<?php

namespace App\Support;

use XMLReader;

/**
 * MacDive's native XML export ("File > Export > XML", DTD
 * mac-dive.com/macdive_logbook.dtd) -> the same per-dive payload shape
 * UddfParser produces, so LogbookImportController's wizard handles both.
 *
 * Why a second format (Pablo, 2026-10-16: "I think we do have PPO2 data
 * in there, setpoints, setpoint switches, gas switches... offer this
 * option for a more complete import"): MacDive's UDDF export drops dive
 * mode, PPO2, setpoints, operator, boat, conditions, tags and tank names.
 * The XML keeps all of them, per dive:
 *  - computer + serial on every dive (UDDF links some dives to none)
 *  - samples with depth, tank pressure, temperature, PPO2 and NDL
 *  - gas list in order of use, each with its duration and tank
 *    (name, size, working pressure, doubles, start/end pressure)
 *  - events: gas switches (type 10), setpoint low/high (25/26), alarms
 *  - operator, boat, skipper, divemaster, weather, current, surface,
 *    dive types, tags, buddies, rating, GF settings, CNS
 *  - site with water type, certification level, altitude, GPS
 * Values come in the file's own units (<units>Imperial|Metric</units>);
 * everything is converted to the metric the `dives` table stores.
 *
 * Dive mode, which this format doesn't state outright either, is decided
 * from evidence, strongest first:
 *  1. setpoint switch events (only a rebreather has a setpoint)  -> CC
 *  2. a "CCR"/"rebreather" tag the diver gave the dive            -> CC
 *  3. physics: on open circuit PPO2 = O2 fraction x ambient pressure
 *     for one of the listed gases; a rebreather holds PPO2 near its
 *     setpoint whatever the depth. Over the samples deeper than 20 ft:
 *     <50% match OC -> CC, >90% match -> OC, between -> unknown.
 * Anything left unknown is offered to the diver in the wizard.
 */
class MacDiveXmlParser
{
    public array $computers = [];
    public array $sites = [];
    public array $buddies = [];
    public ?string $generator = 'MacDive XML';
    public array $dives = [];

    private bool $imperial = true;

    public static function parse(string $path): self
    {
        $p = new self();
        $p->run($path);
        return $p;
    }

    /** True when the file looks like a MacDive XML logbook (sniffs the head). */
    public static function sniff(string $path): bool
    {
        $head = (string) @file_get_contents($path, false, null, 0, 4096);
        return str_contains($head, 'macdive_logbook') || (str_contains($head, '<dives>') && !str_contains($head, '<uddf'));
    }

    private function run(string $path): void
    {
        $r = new XMLReader();
        if (!$r->open($path, null, LIBXML_PARSEHUGE | LIBXML_NOCDATA)) {
            throw new \RuntimeException('Could not open the file.');
        }
        libxml_use_internal_errors(true);
        while ($r->read()) {
            if ($r->nodeType !== XMLReader::ELEMENT) continue;
            if ($r->name === 'units') {
                $this->imperial = stripos($r->readString(), 'metric') === false;
            } elseif ($r->name === 'schema') {
                $this->generator = 'MacDive XML ' . trim($r->readString());
            } elseif ($r->name === 'dive' && $r->depth === 1) {
                $x = simplexml_load_string($r->readOuterXml(), 'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_PARSEHUGE);
                if ($x !== false) {
                    $d = $this->dive($x);
                    if ($d) $this->dives[] = $d;
                }
                $r->next();
            }
        }
        $r->close();
    }

    // ----- units -----
    private function m(?float $v): ?float { return $v === null ? null : ($this->imperial ? $v * 0.3048 : $v); }
    private function c(?float $v): ?float { return $v === null ? null : ($this->imperial ? ($v - 32) * 5 / 9 : $v); }
    private function bar(?float $v): ?float { return $v === null ? null : ($this->imperial ? $v / 14.5038 : $v); }
    private function num($v): ?float { $s = trim((string) $v); return ($s === '' || !is_numeric($s)) ? null : (float) $s; }

    private function dive(\SimpleXMLElement $x): ?array
    {
        $date = trim((string) $x->date);
        if ($date === '') return null;
        try { $started = new \DateTimeImmutable($date); } catch (\Exception $e) { return null; }

        // Computer: "Shearwater Petrel 2" -> model "Petrel 2", like the app reports it.
        $computerFull = trim((string) $x->computer);
        $model = trim(preg_replace('/^(Shearwater Research, Inc|Shearwater|Garmin|Aqualung|Aqua Lung|Suunto|Mares|Oceanic|Scubapro)\s+/i', '', $computerFull));
        $serial = strtolower(trim((string) $x->serial));
        $family = UddfParser::family($model, $computerFull);
        if ($computerFull !== '') {
            $this->computers[$computerFull . '|' . $serial] = ['model' => $model, 'serial' => $serial, 'manufacturer' => trim(substr($computerFull, 0, max(0, strlen($computerFull) - strlen($model))))];
        }

        // ----- gases: usage order with durations -> unique mixes + a timeline -----
        $gasMixes = [];
        $mixIndex = [];
        $timeline = []; // [startSecond, gasIndex]
        $tanks = [];
        $tankConfig = [];
        $tankByMix = [];
        $t = 0;
        foreach ($x->gases->gas ?? [] as $g) {
            $o2 = (int) round((float) $g->oxygen);
            $he = (int) round((float) $g->helium);
            if ($o2 <= 0) $o2 = 21;
            $k = $o2 . '/' . $he;
            if (!isset($mixIndex[$k])) {
                $gasMixes[] = ['oxygen' => $o2, 'helium' => $he, 'nitrogen' => max(0, 100 - $o2 - $he), 'usage' => 0];
                $mixIndex[$k] = count($gasMixes) - 1;
            }
            $gi = $mixIndex[$k];
            $timeline[] = [$t, $gi];
            $t += (int) round((float) $g->duration);

            // One cylinder per distinct mix + tank; repeats are the same bottle.
            $tankKey = $k . '|' . trim((string) $g->tankName) . '|' . (string) $g->double;
            if (!isset($tankByMix[$tankKey])) {
                $ps = $this->num($g->pressureStart); $pe = $this->num($g->pressureEnd);
                $tanks[] = [
                    'usage' => 'none', 'volume' => 0, 'workPressure' => 0,
                    'beginPressure' => $ps > 0 ? round($this->bar($ps), 2) : 0,
                    'endPressure' => $pe > 0 ? round($this->bar($pe), 2) : 0,
                    'gasMix' => $gi,
                ];
                $ti = count($tanks) - 1;
                $tankByMix[$tankKey] = $ti;
                $type = self::tankType(trim((string) $g->tankName), $this->num($g->tankSize), $this->num($g->workingPressure));
                if ($type) $tankConfig[$ti] = ['type' => $type, 'twins' => ((string) $g->double) === '1'];
            }
        }
        $gasAt = function (float $sec) use ($timeline) {
            $gi = $timeline[0][1] ?? null;
            foreach ($timeline as [$start, $g]) { if ($sec >= $start) $gi = $g; else break; }
            return $gi;
        };

        // ----- events: setpoints -----
        $setpoints = []; // [second, value]
        $hasSetpointEvents = false;
        foreach ($x->events->event ?? [] as $e) {
            $type = (string) $e->type;
            if ($type === '25' || $type === '26') {
                $hasSetpointEvents = true;
                if (preg_match('/\(([\d.]+)\)/', (string) $e->detail, $mm)) $setpoints[] = [(float) $e->time, (float) $mm[1]];
            }
        }
        usort($setpoints, fn ($a, $b) => $a[0] <=> $b[0]);

        // ----- samples -----
        $raw = [];
        foreach ($x->samples->sample ?? [] as $s) {
            $raw[] = [
                'time' => (float) $s->time,
                'depth' => $this->num($s->depth) ?? 0.0,
                'pressure' => $this->num($s->pressure),
                'temperature' => $this->num($s->temperature),
                'ppo2' => $this->num($s->ppo2),
                'ndt' => $this->num($s->ndt),
            ];
        }

        // ----- dive mode from evidence -----
        $tags = [];
        foreach ($x->tags->tag ?? [] as $tag) { $tv = trim((string) $tag); if ($tv !== '') $tags[] = $tv; }
        $ccTag = (bool) preg_grep('/^(ccr|scr|rebreather|closed circuit)$/i', $tags);
        $mode = null; $modeSource = null;
        if ($hasSetpointEvents) { $mode = 'closed_circuit'; $modeSource = 'file'; }
        elseif ($ccTag) { $mode = 'closed_circuit'; $modeSource = 'file'; }
        else {
            $fractions = array_unique(array_map(fn ($g) => $g['oxygen'] / 100, $gasMixes ?: [['oxygen' => 21]]));
            $n = 0; $fit = 0;
            foreach ($raw as $s) {
                $depthFt = $this->imperial ? $s['depth'] : $s['depth'] / 0.3048;
                if ($depthFt < 20 || !($s['ppo2'] > 0)) continue;
                $amb = $depthFt / 33 + 1;
                $n++;
                foreach ($fractions as $f) { if (abs($f * $amb - $s['ppo2']) <= 0.06) { $fit++; break; } }
            }
            if ($n >= 30) {
                $share = $fit / $n;
                if ($share < 0.5) { $mode = 'closed_circuit'; $modeSource = 'inferred'; }
                elseif ($share > 0.9) { $mode = 'open_circuit'; $modeSource = 'inferred'; }
            }
        }
        $isCc = $mode === 'closed_circuit';

        $profile = [];
        $first = null; $last = null; $spIdx = -1; $currentSp = null;
        foreach ($raw as $s) {
            while ($spIdx + 1 < count($setpoints) && $setpoints[$spIdx + 1][0] <= $s['time']) { $spIdx++; $currentSp = $setpoints[$spIdx][1]; }
            $p = ['time' => (int) round($s['time']), 'depth' => round($this->m($s['depth']), 3)];
            if ($s['temperature'] !== null && $s['temperature'] != 0) $p['temperature'] = round($this->c($s['temperature']), 2);
            if ($s['pressure'] !== null && $s['pressure'] > 0) {
                $b = round($this->bar($s['pressure']), 2);
                $p['pressure'] = $b; $p['tankPressures'] = [$b];
                $first ??= $b; $last = $b;
            }
            $g = $gasAt($s['time']);
            if ($g !== null) $p['currentGas'] = $g;
            // PPO2: kept for rebreather dives (the computer's measured value);
            // on open circuit it's MacDive's own calculation, which the web
            // derives itself from the gas, so it isn't stored.
            if ($isCc && $s['ppo2'] > 0) $p['po2'] = round($s['ppo2'], 2);
            if ($isCc && $currentSp !== null) $p['setpoint'] = $currentSp;
            if ($s['ndt'] !== null && $s['ndt'] > 0 && $s['ndt'] < 99) $p['ndl'] = (int) ($s['ndt'] * 60);
            $profile[] = $p;
        }
        // MacDive fills PPO2 with the setpoint when the computer measured
        // nothing (a backup computer on the loop): that's not data.
        if ($isCc && self::po2IsSetpointFiller($profile)) {
            foreach ($profile as &$pp) unset($pp['po2']);
            unset($pp);
        }
        if ($first !== null && $tanks) {
            // The single pressure stream belongs to the first cylinder.
            if (!$tanks[0]['beginPressure']) $tanks[0]['beginPressure'] = $first;
            if (!$tanks[0]['endPressure']) $tanks[0]['endPressure'] = $last;
        }
        if ($isCc && $gasMixes) {
            $dil = $profile[0]['currentGas'] ?? 0;
            foreach ($gasMixes as $gi => $gm) $gasMixes[$gi]['usage'] = $gi === $dil ? 2 : 0;
            foreach ($tanks as $ti => $tk) if ($tk['gasMix'] === $dil) { $tanks[$ti]['usage'] = 'diluent'; break; }
        }

        // ----- facts -----
        $duration = (int) round((float) $x->duration);
        $maxDepth = $this->m($this->num($x->maxDepth));
        if (!$maxDepth && $profile) $maxDepth = max(array_column($profile, 'depth'));
        $avg = $this->num($x->averageDepth);
        $avgDepth = $avg > 0 ? round($this->m($avg), 2) : UddfParser::averageDepth($profile);
        $low = $this->num($x->tempLow); $high = $this->num($x->tempHigh); $air = $this->num($x->tempAir);
        $deco = null;
        if (preg_match('/GF\s*(\d+)\s*\/\s*(\d+)/i', (string) $x->decoModel, $gm)) $deco = ['type' => 'buhlmann', 'gfLow' => (int) $gm[1], 'gfHigh' => (int) $gm[2], 'conservatism' => 0];
        $cns = $this->num($x->cns);

        $wallClockAsUtc = (new \DateTimeImmutable($started->format('Y-m-d H:i:s'), new \DateTimeZone('UTC')))->getTimestamp();
        $fingerprint = ($family === 'shearwaterPetrel' && $serial !== '')
            ? base64_encode(pack('N', $wallClockAsUtc))
            : 'sha256:' . hash('sha256', $started->format('Y-m-d\TH:i:s') . '|' . $duration . '|' . number_format((float) $maxDepth, 2, '.', ''));

        // ----- site -----
        $siteKey = null;
        if (isset($x->site) && trim((string) $x->site->name) !== '') {
            $st = $x->site;
            $lat = $this->num($st->lat); $lon = $this->num($st->lon);
            $siteKey = 'md:' . md5(mb_strtolower(trim((string) $st->name)) . '|' . $lat . '|' . $lon);
            if (!isset($this->sites[$siteKey])) {
                $alt = $this->num($st->altitude);
                $this->sites[$siteKey] = [
                    'name' => trim((string) $st->name),
                    'latitude' => ($lat || $lon) ? $lat : null,
                    'longitude' => ($lat || $lon) ? $lon : null,
                    'location' => trim((string) $st->location),
                    'country' => trim((string) $st->country),
                    'water_type' => self::waterType((string) $st->waterType),
                    'min_cert' => self::cert((string) $st->difficulty),
                    'altitude_m' => $alt ? (int) round($this->m($alt)) : null,
                    'body' => trim((string) $st->bodyOfWater),
                    'type_votes' => [],
                ];
            }
        }

        // ----- conditions -----
        $types = [];
        foreach ($x->types->type ?? [] as $tp) $types[] = trim((string) $tp);
        $diveTypes = array_values(array_unique(array_filter(array_map([self::class, 'diveType'], $types))));
        if ($siteKey) foreach ($diveTypes as $dt) if (in_array($dt, ['wreck', 'reef', 'cave', 'cavern'], true)) $this->sites[$siteKey]['type_votes'][$dt] = ($this->sites[$siteKey]['type_votes'][$dt] ?? 0) + 1;
        $buddyNames = [];
        foreach ($x->buddies->buddy ?? [] as $b) { $bn = trim((string) $b); if ($bn !== '') $buddyNames[] = $bn; }
        [$current, $currentDir] = self::current((string) $x->current);
        $conditions = array_filter([
            'operator_name' => trim((string) $x->diveOperator) ?: null,
            'boat_name' => trim((string) $x->boat) ?: null,
            'boat_captain' => trim((string) $x->skipper) ?: null,
            'divemaster' => trim((string) $x->diveMaster) ?: null,
            'weather' => self::weather((string) $x->weather),
            'current' => $current,
            'current_direction' => $currentDir,
            'surface_conditions' => self::surface((string) $x->surfaceConditions),
            'dive_type' => $diveTypes ?: null,
            'rating' => ($rt = (int) $x->rating) > 0 ? $rt : null,
            'tank_config' => $tankConfig ?: null,
        ], fn ($v) => $v !== null && $v !== '' && $v !== []);

        $notes = UddfParser::cleanNotes(trim((string) $x->notes));

        return [
            'dive_number' => (int) $x->diveNumber ?: null,
            'computer_family' => $computerFull !== '' ? $family : 'uddf',
            'computer_model' => $model !== '' ? $model : null,
            'computer_serial' => $serial !== '' ? $serial : null,
            'fingerprint' => $fingerprint,
            'started_at' => $started->format('Y-m-d H:i:s'),
            'duration_seconds' => $duration,
            'max_depth_m' => round((float) $maxDepth, 2),
            'avg_depth_m' => $avgDepth,
            'min_water_temp_c' => $low ? round($this->c($low), 1) : null,
            'max_water_temp_c' => $high ? round($this->c($high), 1) : null,
            'surface_temp_c' => $air ? round($this->c($air), 1) : null,
            'dive_mode' => $mode ?? 'open_circuit',
            'mode_source' => $modeSource,
            'gas_mixes' => $gasMixes ?: null,
            'tanks' => $tanks ?: null,
            'deco_model' => $deco,
            'sensor_data' => $cns !== null ? ['cns' => (int) round($cns)] : null,
            'profile' => $profile,
            'site_key' => $siteKey,
            'notes' => $notes !== '' ? $notes : null,
            'buddies' => $buddyNames,
            'rating' => ($rt = (int) $x->rating) > 0 ? $rt : null,
            'tags' => array_slice(array_map(fn ($tg) => mb_substr($tg, 0, 40), $tags), 0, 5),
            'conditions' => $conditions,
            'source' => 'uddf-import',
        ];
    }

    /** Site type for a created own site: body of water first, then what its dives were. */
    public static function siteType(array $site): string
    {
        $body = strtolower($site['body'] ?? '');
        if (str_contains($body, 'cavern')) return 'cavern';
        if (str_contains($body, 'cave')) return 'cave';
        if (str_contains($body, 'quarry')) return 'quarry';
        if (str_contains($body, 'lake')) return 'lake';
        if (str_contains($body, 'river')) return 'river';
        $votes = $site['type_votes'] ?? [];
        arsort($votes);
        return array_key_first($votes) ?? 'other';
    }

    public static function tankType(string $name, ?float $size, ?float $wp): ?string
    {
        $n = strtolower(str_replace([' ', '-', '_'], '', $name));
        if (preg_match('/al80|aluminum80/', $n)) return 'AL-80';
        if (preg_match('/al72/', $n)) return 'AL-72';
        if (preg_match('/al40|pony40|stage40/', $n)) return 'AL-40';
        if (preg_match('/al30|pony30/', $n)) return 'AL-30';
        if (preg_match('/al19/', $n)) return 'AL-19';
        if (preg_match('/al13/', $n)) return 'AL-13';
        if (preg_match('/hp100/', $n)) return 'ST-HP-100';
        if (preg_match('/lp85/', $n)) return 'ST-LP-85';
        if (preg_match('/lp50/', $n)) return 'ST-LP-50';
        if (preg_match('/3l/', $n)) return 'ST-HP-3L';
        if (preg_match('/2l/', $n)) return 'ST-HP-2L';
        // By rated size when the name doesn't say.
        $s = (int) round((float) $size);
        return [80 => 'AL-80', 72 => 'AL-72', 40 => 'AL-40', 30 => 'AL-30', 100 => 'ST-HP-100', 85 => 'ST-LP-85'][$s] ?? null;
    }

    public static function waterType(string $v): ?string
    {
        $v = strtolower(trim($v));
        if ($v === '') return null;
        if (str_starts_with($v, 'f')) return 'fresh';
        if (str_starts_with($v, 's')) return 'salt';
        return null;
    }

    /** MacDive's free-text difficulty -> OW/AOW/Ta/Tn/Th (closest TecRec level). */
    public static function cert(string $v): ?string
    {
        $v = strtolower(trim($v));
        return match (true) {
            $v === '' => null,
            in_array($v, ['ow', 'open water'], true) => 'OW',
            in_array($v, ['aow', 'advanced open water'], true) => 'AOW',
            in_array($v, ['andp', 'tech', 'ta', 'deco'], true) => 'Ta',
            in_array($v, ['atr', 'atrimix', 'atrx', 'th', 'hypoxic'], true) => 'Th',
            in_array($v, ['tr', 'trx', 'trimix', 'tn', 'normoxic'], true) => 'Tn',
            default => null,
        };
    }

    public static function diveType(string $v): ?string
    {
        $v = strtolower($v);
        return match (true) {
            str_contains($v, 'wreck') => 'wreck',
            str_contains($v, 'reef') => 'reef',
            str_contains($v, 'drift') => 'drift',
            str_contains($v, 'cavern') => 'cavern',
            str_contains($v, 'cave') => 'cave',
            str_contains($v, 'wall') => 'wall',
            str_contains($v, 'photo') || str_contains($v, 'video') => 'photo_video',
            str_contains($v, 'train') || str_contains($v, 'traning') || str_contains($v, 'class') => 'training',
            default => null,
        };
    }

    public static function weather(string $v): ?string
    {
        $v = strtolower(trim($v));
        return match (true) {
            $v === '' => null,
            str_contains($v, 'storm') => 'stormy',
            str_contains($v, 'rain') => 'rainy',
            str_contains($v, 'wind') => 'windy',
            (bool) preg_match('/partial|partly|some cloud|mostly sun|partially sun/', $v) => 'partly_cloudy',
            (bool) preg_match('/cloud|overcast/', $v) => 'cloudy',
            str_starts_with($v, 'sun') => 'sunny',
            default => null,
        };
    }

    /** "Mild South" -> ['light', 'S']; "1 Knot N" -> ['light', 'N']. */
    public static function current(string $v): array
    {
        $v = strtolower(trim($v));
        if ($v === '') return [null, null];
        $strength = match (true) {
            (bool) preg_match('/^none|^no |^0/', $v) => 'none',
            (bool) preg_match('/strong|[23]\s*knot/', $v) => 'strong',
            (bool) preg_match('/medium|midium|moderate/', $v) => 'moderate',
            (bool) preg_match('/mild|light|half knot|1\s*knot|push/', $v) => 'light',
            default => null,
        };
        $dir = null;
        if (preg_match('/\b(north\s*east|north\s*west|south\s*east|south\s*west|ne|nw|se|sw)\b/', $v, $m)) {
            $dir = strtoupper(str_replace(['north', 'south', 'east', 'west', ' '], ['n', 's', 'e', 'w', ''], $m[1]));
        } elseif (preg_match('/\b(north|south|east|west|n|s|e|w)\b/', $v, $m)) {
            $dir = strtoupper($m[1][0]);
        }
        return [$strength, $dir];
    }

    public static function surface(string $v): ?string
    {
        $v = strtolower(trim($v));
        return match (true) {
            $v === '' => null,
            (bool) preg_match('/flat|calm/', $v) => 'calm',
            (bool) preg_match('/big|large|swell|surf|\d\s*ft|rough/', $v) => 'rough',
            (bool) preg_match('/small|smal/', $v) => 'slight_chop',
            (bool) preg_match('/medium|wave|surge|chop/', $v) => 'choppy',
            default => null,
        };
    }

    /** PPO2 that just repeats the setpoint (>= 90% of samples) or has <= 3 values. */
    public static function po2IsSetpointFiller(array $profile): bool
    {
        $with = array_values(array_filter($profile, fn ($p) => isset($p['po2'])));
        if (count($with) < 10) return false;
        $on = count(array_filter($with, fn ($p) => isset($p['setpoint']) && abs($p['po2'] - $p['setpoint']) < 0.011));
        $distinct = count(array_unique(array_map(fn ($p) => (string) $p['po2'], $with)));
        return $on / count($with) >= 0.9 || $distinct <= 3;
    }
}
