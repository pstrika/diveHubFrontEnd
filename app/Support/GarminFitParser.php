<?php

namespace App\Support;

/**
 * Garmin Descent dive exported as a .FIT file (Garmin Connect > activity >
 * Export Original; Pablo, 2026-10-16: "there's a .FIT in my /Download...
 * check if we can import it"). FIT is Garmin's documented binary format:
 * a 14-byte header, then records that are either a *definition* (global
 * message number + field layout for a local message type) or *data* laid
 * out by the last definition for that local type. Only the messages a
 * dive needs are kept:
 *
 *   0 file_id      - product (watch model), serial
 *   18 session     - start time, GPS entry point, elapsed time, sub_sport (apnea)
 *   20 record      - 1 s samples: depth, temperature, heart rate, NDL, next stop, TTS, CNS, N2, PO2
 *   21 event       - gas switches (event 57, data = dive_gas index)
 *   34 activity    - local_timestamp, giving the diver's UTC offset
 *   258 dive_settings - algorithm, GF low/high, water type
 *   259 dive_gas   - the gas list
 *   268 dive_summary - max/avg depth, start/end CNS, dive number
 *   319 tank_update - per-sample tank pressure from a T1/T2 transmitter
 *
 * Output is the payload shape the other parsers produce. Like the
 * Shearwater XML there is no site name or notes, but there IS GPS, so the
 * dive gets a coordinates-only site the wizard matches to the catalog.
 */
class GarminFitParser
{
    public array $computers = [];
    public array $sites = [];
    public array $buddies = [];
    public ?string $generator = 'Garmin FIT';
    public array $dives = [];

    private const FIT_EPOCH = 631065600; // 1989-12-31T00:00:00Z
    private const SEMI = 180 / 2147483648;

    /** garmin_product codes for Descent watches; anything else is "Descent". */
    private const MODELS = [2859 => 'Descent Mk1', 2991 => 'Descent Mk1', 3258 => 'Descent Mk2', 3542 => 'Descent Mk2S', 3702 => 'Descent Mk2', 4223 => 'Descent G1', 4518 => 'Descent Mk3', 4519 => 'Descent Mk3i', 4520 => 'Descent Mk3i'];

    /** [base type => [unpack code, size, invalid value]] */
    private const TYPES = [
        0x00 => ['C', 1, 0xFF], 0x01 => ['c', 1, 0x7F], 0x02 => ['C', 1, 0xFF],
        0x83 => ['s', 2, 0x7FFF], 0x84 => ['S', 2, 0xFFFF],
        0x85 => ['l', 4, 0x7FFFFFFF], 0x86 => ['L', 4, 0xFFFFFFFF],
        0x07 => ['a', 1, null], 0x88 => ['f', 4, null], 0x89 => ['d', 8, null],
        0x0A => ['C', 1, 0x00], 0x8B => ['S', 2, 0x0000], 0x8C => ['L', 4, 0x00000000],
        0x0D => ['C', 1, null], 0x8E => ['q', 8, null], 0x8F => ['Q', 8, null], 0x90 => ['q', 8, null],
    ];

    public static function sniff(string $path): bool
    {
        $h = (string) @file_get_contents($path, false, null, 0, 12);
        return strlen($h) >= 12 && substr($h, 8, 4) === '.FIT';
    }

    /** @param array<int, array{path:string, name:string}> $files */
    public static function parseMany(array $files): self
    {
        $p = new self();
        foreach ($files as $f) {
            $d = $p->parseOne($f['path']);
            if ($d) $p->dives[] = $d;
        }
        return $p;
    }

    /** Decode the file into [globalMsg => [fields...], ...] (messages we keep only). */
    private function decode(string $raw): array
    {
        $hsize = ord($raw[0]);
        $dataSize = unpack('V', substr($raw, 4, 4))[1];
        $end = min(strlen($raw), $hsize + $dataSize);
        $pos = $hsize;
        $defs = [];
        $out = [];
        $lastTs = 0;
        $keep = [0, 18, 20, 21, 34, 258, 259, 268, 319];

        while ($pos < $end) {
            $hdr = ord($raw[$pos++]);
            if ($hdr & 0x80) { // compressed-timestamp data message
                $local = ($hdr >> 5) & 0x03;
                $offset = $hdr & 0x1F;
                $ts = ($lastTs & ~0x1F) + $offset;
                if ($offset < ($lastTs & 0x1F)) $ts += 0x20;
                $lastTs = $ts;
                if (!isset($defs[$local])) break;
                $msg = $this->readData($raw, $pos, $defs[$local]);
                $msg[253] = $ts;
                if (in_array($defs[$local]['global'], $keep, true)) $out[$defs[$local]['global']][] = $msg;
                continue;
            }
            $local = $hdr & 0x0F;
            if ($hdr & 0x40) { // definition
                $pos++; // reserved
                $big = ord($raw[$pos++]) === 1;
                $global = unpack($big ? 'n' : 'v', substr($raw, $pos, 2))[1]; $pos += 2;
                $n = ord($raw[$pos++]);
                $fields = [];
                for ($i = 0; $i < $n; $i++) {
                    $fields[] = [ord($raw[$pos]), ord($raw[$pos + 1]), ord($raw[$pos + 2])];
                    $pos += 3;
                }
                $devBytes = 0;
                if ($hdr & 0x20) { // developer fields: skipped by size
                    $nd = ord($raw[$pos++]);
                    for ($i = 0; $i < $nd; $i++) { $devBytes += ord($raw[$pos + 1]); $pos += 3; }
                }
                $defs[$local] = ['global' => $global, 'big' => $big, 'fields' => $fields, 'dev' => $devBytes];
                continue;
            }
            if (!isset($defs[$local])) break; // corrupt
            $msg = $this->readData($raw, $pos, $defs[$local]);
            if (isset($msg[253])) $lastTs = $msg[253];
            if (in_array($defs[$local]['global'], $keep, true)) $out[$defs[$local]['global']][] = $msg;
        }
        return $out;
    }

    private function readData(string $raw, int &$pos, array $def): array
    {
        $msg = [];
        foreach ($def['fields'] as [$num, $size, $base]) {
            $bytes = substr($raw, $pos, $size);
            $pos += $size;
            $t = self::TYPES[$base] ?? null;
            if (!$t || $t[1] !== $size || $base === 0x07 || $base === 0x0D) continue; // arrays/strings/bytes: not needed
            $code = $t[0];
            if ($def['big'] && $size > 1) {
                $bytes = strrev($bytes);
            }
            // unpack's s/S/l/L are machine-order; FIT here is little-endian
            // after the reversal above, and every host we run on is too.
            $v = unpack($code, $bytes)[1];
            if ($t[2] !== null && $v === $t[2]) continue;
            if (is_float($v) && is_nan($v)) continue;
            $msg[$num] = $v;
        }
        $pos += $def['dev'];
        return $msg;
    }

    private function parseOne(string $path): ?array
    {
        $raw = (string) file_get_contents($path);
        if (strlen($raw) < 14 || substr($raw, 8, 4) !== '.FIT') return null;
        $m = $this->decode($raw);
        $records = $m[20] ?? [];
        $session = ($m[18] ?? [[]])[0];
        if (!$records || !isset($session[2])) return null;

        $fileId = ($m[0] ?? [[]])[0];
        $model = self::MODELS[$fileId[2] ?? 0] ?? 'Descent';
        $serial = isset($fileId[3]) ? (string) $fileId[3] : null;
        $this->computers[$model . '|' . $serial] = ['model' => $model, 'serial' => $serial, 'manufacturer' => 'Garmin'];

        // The watch logs UTC; the logbook keeps local wall-clock time.
        $act = ($m[34] ?? [[]])[0];
        $offset = (isset($act[5], $act[253])) ? ((int) $act[5] - (int) $act[253]) : 0;
        if (abs($offset) > 14 * 3600) $offset = 0;
        $startUtc = (int) $session[2] + self::FIT_EPOCH;
        $startLocal = gmdate('Y-m-d H:i:s', $startUtc + $offset);

        // ----- gases -----
        $gasMixes = []; $gasByIndex = [];
        foreach ($m[259] ?? [] as $g) {
            if (($g[2] ?? 1) === 0) continue; // disabled
            $o2 = (int) ($g[1] ?? 21); $he = (int) ($g[0] ?? 0);
            $gasByIndex[(int) ($g[254] ?? count($gasByIndex))] = count($gasMixes);
            $gasMixes[] = ['oxygen' => $o2, 'helium' => $he, 'nitrogen' => max(0, 100 - $o2 - $he), 'usage' => ($g[3] ?? 0) === 1 ? 2 : 0];
        }
        $isCc = (bool) array_filter($gasMixes, fn ($g) => $g['usage'] === 2);
        // Session sub_sport (field 6) 56 apnea_diving / 57 apnea_hunting: a
        // breath-hold dive, which the logbook classes as snorkeling or freediving.
        $isApnea = in_array((int) ($session[6] ?? -1), [56, 57], true);
        // Gas switches: event 57 (dive_gas_switched), data = dive_gas index.
        $switches = [];
        foreach ($m[21] ?? [] as $e) {
            if (($e[0] ?? null) === 57 && isset($e[253], $e[3]) && isset($gasByIndex[(int) $e[3]])) $switches[(int) $e[253]] = $gasByIndex[(int) $e[3]];
        }
        ksort($switches);
        // Tank pressure (transmitter): timestamp => bar, per sensor.
        $tank = [];
        foreach ($m[319] ?? [] as $t) {
            if (isset($t[253], $t[1])) $tank[(int) $t[253]] = round($t[1] / 100, 1);
        }
        ksort($tank);

        // ----- samples -----
        $t0 = (int) $records[0][253];
        $profile = [];
        $gas = $gasMixes ? 0 : null; $tankVal = null;
        foreach ($records as $r) {
            if (!isset($r[253], $r[92])) continue;
            $ts = (int) $r[253];
            foreach ($switches as $at => $gi) { if ($at <= $ts) $gas = $gi; else break; }
            foreach ($tank as $at => $bar) { if ($at <= $ts) $tankVal = $bar; else break; }
            $s = ['time' => $ts - $t0, 'depth' => round($r[92] / 1000, 3)];
            if (isset($r[13])) $s['temperature'] = (float) $r[13];
            // Wrist heart rate (record field 3, bpm; Pablo, 2026-10-23: "those
            // include data for heartbeat... include this"). The watch writes
            // it on every sample; 0 would mean no reading.
            if (isset($r[3]) && (int) $r[3] > 0) $s['heartRate'] = (int) $r[3];
            if ($gas !== null) $s['currentGas'] = $gas;
            $stop = isset($r[93]) ? $r[93] / 1000 : 0;
            if ($stop > 0) {
                $s['decoKind'] = 'deco_stop';
                $s['decoStop'] = round($stop, 2);
                $s['decoTime'] = (int) ($r[94] ?? 0);
            } elseif (isset($r[96])) {
                $s['decoKind'] = 'ndl';
                $s['ndl'] = (int) $r[96];
            }
            if (isset($r[95])) $s['tts'] = (int) $r[95];
            if (isset($r[97])) $s['cns'] = (int) $r[97];
            if (isset($r[104]) && $isCc) $s['po2'] = round($r[104] / 100, 2);
            if ($tankVal !== null) { $s['pressure'] = $tankVal; $s['tankPressures'] = [$tankVal]; }
            $profile[] = $s;
        }
        if (count($profile) < 2) return null;

        // Trim the surface tail the watch keeps logging after the dive.
        while (count($profile) > 2 && end($profile)['depth'] < 0.3 && $profile[count($profile) - 2]['depth'] < 0.3) array_pop($profile);

        $summary = null;
        foreach ($m[268] ?? [] as $ds) if (($ds[0] ?? null) === 18 || $summary === null) $summary = $ds;
        $depths = array_column($profile, 'depth');
        $maxDepth = isset($summary[3]) ? $summary[3] / 1000 : max($depths);
        $temps = array_column($profile, 'temperature');
        // Session avg/max heart rate (fields 16/17); from the samples when
        // the summary lacks them.
        $hrs = array_column($profile, 'heartRate');
        $hrAvg = isset($session[16]) ? (int) $session[16] : ($hrs ? (int) round(array_sum($hrs) / count($hrs)) : null);
        $hrMax = isset($session[17]) ? (int) $session[17] : ($hrs ? max($hrs) : null);

        $settings = ($m[258] ?? [[]])[0];
        $decoModel = ['type' => 'buhlmann', 'gfLow' => (int) ($settings[2] ?? 0), 'gfHigh' => (int) ($settings[3] ?? 0), 'conservatism' => 0];
        if (!$decoModel['gfHigh']) $decoModel = null;

        // GPS entry point -> a coordinates-only site for the wizard. The
        // exit point (session end_position, fields 38/39) is there when the
        // watch got a fix back at the surface (Pablo, 2026-10-24: the dive
        // page's Entry & Exit card, drift distance and current).
        $siteKey = null; $location = null;
        if (isset($session[3], $session[4])) {
            $lat = round($session[3] * self::SEMI, 6); $lon = round($session[4] * self::SEMI, 6);
            if (abs($lat) > 0.0001 || abs($lon) > 0.0001) {
                $location = ['latitude' => $lat, 'longitude' => $lon, 'altitude' => null];
                if (isset($session[38], $session[39])) {
                    $xlat = round($session[38] * self::SEMI, 6); $xlon = round($session[39] * self::SEMI, 6);
                    if (abs($xlat) > 0.0001 || abs($xlon) > 0.0001) { $location['exit_latitude'] = $xlat; $location['exit_longitude'] = $xlon; }
                }
                // Dives within ~100 m of each other share one site.
                $siteKey = 'gps:' . round($lat, 3) . ',' . round($lon, 3);
                $this->sites[$siteKey] ??= [
                    'name' => sprintf('GPS %.4f, %.4f', $lat, $lon),
                    'gps_only' => true,
                    'latitude' => $lat, 'longitude' => $lon,
                    'location' => '', 'country' => '',
                    'water_type' => isset($settings[4]) ? ((int) $settings[4] === 0 ? 'fresh' : 'salt') : null,
                    'min_cert' => null, 'altitude_m' => null, 'body' => '', 'type_votes' => [],
                ];
            }
        }

        $tanks = null;
        if ($tank) {
            $vals = array_values($tank);
            $tanks = [['usage' => $isCc ? 'diluent' : 'none', 'volume' => 0, 'workPressure' => 0, 'beginPressure' => $vals[0], 'endPressure' => end($vals), 'gasMix' => 0]];
        }

        return [
            'dive_number' => isset($summary[10]) ? (int) $summary[10] : null,
            'computer_family' => 'garmin',
            'computer_model' => $model,
            'computer_serial' => $serial,
            'fingerprint' => null,
            'started_at' => $startLocal,
            'duration_seconds' => (int) end($profile)['time'],
            'max_depth_m' => round($maxDepth, 2),
            'avg_depth_m' => isset($summary[2]) ? round($summary[2] / 1000, 2) : UddfParser::averageDepth($profile),
            'min_water_temp_c' => $temps ? round(min($temps), 1) : null,
            'max_water_temp_c' => $temps ? round(max($temps), 1) : null,
            'dive_mode' => $isApnea ? 'freedive' : ($isCc ? 'closed_circuit' : 'open_circuit'),
            'mode_source' => 'file',
            'gas_mixes' => $gasMixes ?: null,
            'tanks' => $tanks,
            'deco_model' => $decoModel,
            'location' => $location,
            'sensor_data' => array_filter(['cns' => isset($summary[6]) ? (int) $summary[6] : null, 'heart_rate_avg' => $hrAvg ?: null, 'heart_rate_max' => $hrMax ?: null], fn ($v) => $v !== null) ?: null,
            'profile' => $profile,
            'site_key' => $siteKey,
            'notes' => null,
            'buddies' => [],
            'rating' => null,
            'tags' => [],
            'conditions' => [],
            'computer_native' => true,
            'source' => 'computer-file',
        ];
    }
}
