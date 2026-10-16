<?php

namespace App\Support;

/**
 * One dive exported from Shearwater Cloud as XML ("Export > XML", one file
 * per dive, named like "Petrel[3699F479]#9_2020-10-02.xml") -> the payload
 * shape UddfParser/MacDiveXmlParser produce, so the import wizard takes it
 * like any other file (Pablo, 2026-10-16: "a XML for a single dive from
 * Shearwater... a button to upload a dive").
 *
 * This is the computer's own log, sample by sample, so it carries what the
 * logbook exports drop: the decompression ceiling (firstStopDepth/Time),
 * TTS and NDL, the voted PPO2, the circuit mode (CC/BO vs OC/BO), the
 * low/high setpoint flag, the gas in use (O2/He fractions) and AI tank
 * pressures. It has no site, notes or buddies - the wizard's duplicate
 * check turns it into an upgrade when that dive was already imported from
 * a logbook (Dive::replaceComputerFacts keeps the diver's annotations).
 *
 * Quirks handled: the header says encoding="utf-16" but the bytes are
 * UTF-8; currentTime is milliseconds; depths/temps follow imperialUnits;
 * the setpoint VALUES aren't in the file (only low/high), so each one is
 * read off the median voted PPO2 while it was active, to 0.1 bar.
 */
class ShearwaterXmlParser
{
    public array $computers = [];
    public array $sites = [];
    public array $buddies = [];
    public ?string $generator = 'Shearwater Cloud XML';
    public array $dives = [];

    /** computerModel/product codes seen in Shearwater exports. */
    private const MODELS = ['4/3' => 'Petrel', '2/11' => 'Perdix 2', '2/6' => 'Perdix AI', '2/7' => 'NERD 2', '2/5' => 'Perdix', '5/12' => 'Teric', '6/13' => 'Peregrine', '7/14' => 'Tern'];

    public static function sniff(string $path): bool
    {
        $head = (string) @file_get_contents($path, false, null, 0, 2048);
        return str_contains($head, '<diveLog>') || (str_contains($head, '<dive ') && str_contains($head, 'diveLog'));
    }

    /** @param array<int, array{path:string, name:string}> $files */
    public static function parseMany(array $files): self
    {
        $p = new self();
        foreach ($files as $f) {
            $d = $p->parseOne($f['path'], $f['name']);
            if ($d) $p->dives[] = $d;
        }
        return $p;
    }

    private function parseOne(string $path, string $name): ?array
    {
        $raw = (string) file_get_contents($path);
        // Header claims UTF-16, bytes are UTF-8 (and a real UTF-16 file has a BOM).
        if (str_starts_with($raw, "\xFF\xFE") || str_starts_with($raw, "\xFE\xFF")) {
            $raw = mb_convert_encoding($raw, 'UTF-8', 'UTF-16');
        }
        $raw = preg_replace('/encoding="utf-16"/i', 'encoding="utf-8"', $raw, 1);
        libxml_use_internal_errors(true);
        $x = simplexml_load_string($raw, 'SimpleXMLElement', LIBXML_PARSEHUGE);
        if ($x === false || !isset($x->diveLog)) return null;
        $l = $x->diveLog;

        $imperial = strtolower(trim((string) $l->imperialUnits)) === 'true';
        $m = fn ($v) => $imperial ? (float) $v * 0.3048 : (float) $v;
        $c = fn ($v) => $imperial ? ((float) $v - 32) * 5 / 9 : (float) $v;
        $bar = fn ($v) => $imperial ? (float) $v / 14.5038 : (float) $v;

        try {
            $started = \DateTimeImmutable::createFromFormat('n/j/Y g:i:s A', trim((string) $l->startDate)) ?: new \DateTimeImmutable(trim((string) $l->startDate));
        } catch (\Exception $e) {
            return null;
        }

        $serial = strtolower(trim((string) $l->computerSerial));
        $model = null;
        if (preg_match('/^(.+?)\[([0-9A-Fa-f]+)\]/', $name, $mm)) {
            $model = trim($mm[1]);
            $serial = $serial ?: strtolower($mm[2]);
        }
        $model ??= self::MODELS[trim((string) $l->computerModel) . '/' . trim((string) $l->product)] ?? 'Shearwater';
        $this->computers[$model . '|' . $serial] = ['model' => $model, 'serial' => $serial, 'manufacturer' => 'Shearwater'];

        // ----- samples -----
        $gasMixes = [];
        $mixIndex = [];
        $profile = [];
        $isCc = false;
        $spPpo2 = [0 => [], 1 => []];
        $pressureFirst = null; $pressureLast = null;
        foreach ($l->diveLogRecords->diveLogRecord ?? [] as $r) {
            $o2 = (int) round((float) $r->fractionO2 * 100);
            $he = (int) round((float) $r->fractionHe * 100);
            $k = $o2 . '/' . $he;
            if ($o2 > 0 && !isset($mixIndex[$k])) {
                $gasMixes[] = ['oxygen' => $o2, 'helium' => $he, 'nitrogen' => max(0, 100 - $o2 - $he), 'usage' => 0];
                $mixIndex[$k] = count($gasMixes) - 1;
            }
            $cc = str_starts_with(strtoupper(trim((string) $r->currentCircuitSetting)), 'CC');
            if ($cc) $isCc = true;
            $ppo2 = (float) $r->averagePPO2;
            $spType = (int) $r->setPointType;

            $s = ['time' => (int) round((float) $r->currentTime / 1000), 'depth' => round($m($r->currentDepth), 3)];
            $t = trim((string) $r->waterTemp);
            if ($t !== '' && is_numeric($t) && (float) $t != 0) $s['temperature'] = round($c($t), 2);
            if (isset($mixIndex[$k])) $s['currentGas'] = $mixIndex[$k];
            $stopDepth = (float) $r->firstStopDepth;
            if ($stopDepth > 0) {
                $s['decoKind'] = 'deco_stop';
                $s['decoStop'] = round($m($stopDepth), 2);
                $s['decoTime'] = (int) ((float) $r->firstStopTime * 60);
            } else {
                $s['decoKind'] = 'ndl';
                $s['ndl'] = (int) ((float) $r->currentNdl * 60);
            }
            $s['tts'] = (int) ((float) $r->ttsMins * 60);
            if ($cc && $ppo2 > 0) {
                $s['po2'] = round($ppo2, 2);
                $s['_spType'] = $spType; // resolved to a value below
                $spPpo2[$spType][] = $ppo2;
                $mvs = array_values(array_filter([(int) $r->sensor1Millivolts, (int) $r->sensor2Millivolts, (int) $r->sensor3Millivolts]));
                if ($mvs) $s['sensorMillivolts'] = $mvs;
            }
            $tp = trim((string) $r->tank0pressurePSI);
            if (is_numeric($tp) && (float) $tp > 0) {
                $b = round($bar($tp), 2);
                $s['pressure'] = $b; $s['tankPressures'] = [$b];
                $pressureFirst ??= $b; $pressureLast = $b;
            }
            $profile[] = $s;
        }
        if (!$profile) return null;

        // Setpoint values from the PPO2 the loop actually held.
        $spValue = [];
        foreach ($spPpo2 as $type => $vals) {
            if (count($vals) < 3) continue;
            sort($vals);
            $spValue[$type] = round($vals[intdiv(count($vals), 2)], 1);
        }
        foreach ($profile as &$s) {
            if (isset($s['_spType'])) {
                if (isset($spValue[$s['_spType']])) $s['setpoint'] = $spValue[$s['_spType']];
                // Kept so an upgrade can swap in the real low/high values
                // the logbook knew (MacDive setpoint events).
                $s['setpointLevel'] = $s['_spType'] ? 'high' : 'low';
                unset($s['_spType']);
            }
        }
        unset($s);

        if ($isCc && $gasMixes) {
            $dil = $profile[0]['currentGas'] ?? 0;
            foreach ($gasMixes as $gi => $gm) $gasMixes[$gi]['usage'] = $gi === $dil ? 2 : 0;
        }
        $tanks = $pressureFirst !== null ? [[
            'usage' => $isCc ? 'diluent' : 'none', 'volume' => 0, 'workPressure' => 0,
            'beginPressure' => $pressureFirst, 'endPressure' => $pressureLast, 'gasMix' => 0,
        ]] : null;

        $maxDepth = $m($l->maxDepth);
        $duration = (int) (float) $l->maxTime;
        if ($duration <= 0) $duration = (int) end($profile)['time'];
        $gfLow = (int) $l->gfMin; $gfHigh = (int) $l->gfMax;
        $wallClockAsUtc = (new \DateTimeImmutable($started->format('Y-m-d H:i:s'), new \DateTimeZone('UTC')))->getTimestamp();
        $temps = array_filter(array_column($profile, 'temperature'), fn ($v) => $v !== null);

        return [
            'dive_number' => (int) $l->number ?: null,
            'computer_family' => 'shearwaterPetrel',
            'computer_model' => $model,
            'computer_serial' => $serial ?: null,
            'fingerprint' => base64_encode(pack('N', $wallClockAsUtc)),
            'started_at' => $started->format('Y-m-d H:i:s'),
            'duration_seconds' => $duration,
            'max_depth_m' => round($maxDepth, 2),
            'avg_depth_m' => UddfParser::averageDepth($profile),
            'min_water_temp_c' => $temps ? round(min($temps), 1) : null,
            'max_water_temp_c' => $temps ? round(max($temps), 1) : null,
            'dive_mode' => $isCc ? 'closed_circuit' : 'open_circuit',
            'mode_source' => 'file',
            'gas_mixes' => $gasMixes ?: null,
            'tanks' => $tanks,
            'deco_model' => ((int) $l->decoModel === 0 && $gfHigh > 0)
                ? ['type' => 'buhlmann', 'gfLow' => $gfLow, 'gfHigh' => $gfHigh, 'conservatism' => 0]
                : ['type' => 'vpm', 'conservatism' => (int) $l->vpmbConservatism],
            'sensor_data' => ['cns' => (int) $l->endCns],
            'profile' => $profile,
            'site_key' => null,
            'notes' => null,
            'buddies' => [],
            'rating' => null,
            'tags' => [],
            'conditions' => [],
            // Straight off the computer: on a match with an imported dive
            // the wizard offers to upgrade it rather than skip.
            'computer_native' => true,
            // Computer data, not a logbook's copy: mode isn't hand-editable
            // and an app sync of the same dive matches it exactly.
            'source' => 'computer-file',
        ];
    }
}
