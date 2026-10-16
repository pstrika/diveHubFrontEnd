<?php

namespace App\Support;

use XMLReader;

/**
 * UDDF 3.x (MacDive, Subsurface, Shearwater Cloud exports...) -> the
 * `dives` row shape, one array per dive, plus the file's site/buddy/
 * computer lookups. Streams the file with XMLReader (a full MacDive
 * export is ~70 MB) and expands one element at a time.
 *
 * Fingerprints (Pablo, 2026-10-16: "is any fingerprint present in that
 * UDDF that allows us to recognize the dive, so next time the user
 * connects the same computer we don't download the same dive again"):
 * libdivecomputer's Shearwater fingerprint is the dive's start time as a
 * big-endian uint32 of local wall-clock seconds (shearwater_petrel.c
 * hands back the 4 bytes at offset 12 of the record, which the parser
 * reads as the datetime), so for a Shearwater computer the UDDF's
 * <datetime> + <serialnumber> rebuild the exact (serial, fingerprint)
 * key the app uploads with - verified byte-for-byte against 52 synced
 * dives. Other brands' fingerprints are header bytes the UDDF doesn't
 * carry, so those get the SYNC-CONTRACT.md synthesised form and rely on
 * the start-time window match in LogbookImportController instead.
 */
class UddfParser
{
    private const NS = 'http://www.streit.cc/uddf/3.2/';

    private string $ns = '';

    public array $computers = [];  // id => ['model' => , 'serial' => , 'manufacturer' => ]
    public array $sites = [];      // id => ['name', 'latitude', 'longitude', 'location', 'country']
    public array $buddies = [];    // id => name
    public array $mixes = [];      // id => ['oxygen' => %, 'helium' => %, 'name' => ]
    public ?string $generator = null;
    public array $dives = [];

    public static function parse(string $path): self
    {
        $p = new self();
        $p->run($path);
        return $p;
    }

    private function run(string $path): void
    {
        $r = new XMLReader();
        if (!$r->open($path)) {
            throw new \RuntimeException('Could not open the file.');
        }
        libxml_use_internal_errors(true);

        while ($r->read()) {
            if ($r->nodeType !== XMLReader::ELEMENT) continue;
            $name = $r->localName;
            if ($this->ns === '' && $r->namespaceURI) $this->ns = $r->namespaceURI;

            switch ($name) {
                case 'generator':
                    $x = $this->expand($r);
                    if ($x) $this->generator = trim($this->txt($x, 'name') . ' ' . $this->txt($x, 'version'));
                    $r->next();
                    break;
                case 'divecomputer':
                    $x = $this->expand($r);
                    if ($x) {
                        $this->computers[(string) $x['id']] = [
                            'model' => $this->txt($x, 'model') ?: $this->txt($x, 'name'),
                            'serial' => $this->txt($x, 'serialnumber'),
                            'manufacturer' => $this->txt($this->el($x, 'manufacturer'), 'name'),
                        ];
                    }
                    $r->next();
                    break;
                case 'site':
                    $x = $this->expand($r);
                    if ($x) {
                        $g = $this->el($x, 'geography');
                        $this->sites[(string) $x['id']] = [
                            'name' => $this->txt($x, 'name'),
                            'latitude' => $this->num($this->txt($g, 'latitude')),
                            'longitude' => $this->num($this->txt($g, 'longitude')),
                            'location' => $this->txt($g, 'location'),
                            'country' => $this->txt($this->el($g, 'address'), 'country'),
                        ];
                    }
                    $r->next();
                    break;
                case 'buddy':
                    $x = $this->expand($r);
                    if ($x) {
                        $p = $this->el($x, 'personal');
                        $this->buddies[(string) $x['id']] = trim($this->txt($p, 'firstname') . ' ' . $this->txt($p, 'lastname'));
                    }
                    $r->next();
                    break;
                case 'mix':
                    $x = $this->expand($r);
                    if ($x) {
                        $o2 = (float) $this->txt($x, 'o2');
                        $he = (float) $this->txt($x, 'he');
                        $this->mixes[(string) $x['id']] = [
                            'oxygen' => (int) round($o2 <= 1 ? $o2 * 100 : $o2),
                            'helium' => (int) round($he <= 1 ? $he * 100 : $he),
                            'name' => $this->txt($x, 'name'),
                        ];
                    }
                    $r->next();
                    break;
                case 'dive':
                    $x = $this->expand($r);
                    if ($x) {
                        $dive = $this->dive($x);
                        if ($dive) $this->dives[] = $dive;
                    }
                    $r->next();
                    break;
            }
        }
        $r->close();
    }

    private function expand(XMLReader $r): ?\SimpleXMLElement
    {
        $xml = $r->readOuterXml();
        if ($xml === '') return null;
        $x = simplexml_load_string($xml);
        return $x === false ? null : $x;
    }

    /** Child elements, honoring the default namespace the file declares. */
    private function el(?\SimpleXMLElement $node, string $name): ?\SimpleXMLElement
    {
        if ($node === null) return null;
        $children = $this->ns !== '' ? $node->children($this->ns) : $node->children();
        if (!isset($children->$name)) {
            // Some writers leave the fragment un-namespaced after expansion.
            $children = $node->children();
            if (!isset($children->$name)) return null;
        }
        return $children->$name;
    }

    private function all(?\SimpleXMLElement $node, string $name): array
    {
        if ($node === null) return [];
        $children = $this->ns !== '' ? $node->children($this->ns) : $node->children();
        if (!isset($children->$name)) $children = $node->children();
        $out = [];
        foreach ($children->$name as $c) $out[] = $c;
        return $out;
    }

    /**
     * Un-namespaced attribute. An element reached through children($ns)
     * resolves $el['ref'] in that namespace and finds nothing, so this
     * reads the plain attribute list instead.
     */
    private function attr(?\SimpleXMLElement $el, string $name): string
    {
        if ($el === null) return '';
        $attrs = $el->attributes();
        return isset($attrs[$name]) ? trim((string) $attrs[$name]) : '';
    }

    private function txt(?\SimpleXMLElement $node, string $name): string
    {
        $e = $this->el($node, $name);
        return $e === null ? '' : trim((string) $e);
    }

    private function num(string $v): ?float
    {
        return $v === '' || !is_numeric($v) ? null : (float) $v;
    }

    private function dive(\SimpleXMLElement $x): ?array
    {
        $before = $this->el($x, 'informationbeforedive');
        $after = $this->el($x, 'informationafterdive');
        $datetime = $this->txt($before, 'datetime');
        if ($datetime === '') return null;
        try {
            $started = new \DateTimeImmutable($datetime);
        } catch (\Exception $e) {
            return null;
        }

        // Which computer / site / buddies this dive links to.
        $computer = null;
        $siteKey = null;
        $buddyNames = [];
        foreach ($this->all($before, 'link') as $l) {
            $ref = $this->attr($l, 'ref');
            if (isset($this->sites[$ref])) $siteKey = $ref;
            elseif (isset($this->buddies[$ref])) $buddyNames[] = $this->buddies[$ref];
            elseif (isset($this->computers[$ref])) $computer = $ref;
        }
        foreach ($this->all($this->el($after, 'equipmentused'), 'link') as $l) {
            $ref = $this->attr($l, 'ref');
            if (isset($this->computers[$ref])) $computer = $ref;
        }
        $comp = $computer ? $this->computers[$computer] : null;

        // Gas list: tankdata order first, then any switchmix target not yet seen.
        $mixIndex = [];
        $gasMixes = [];
        $addMix = function (string $ref) use (&$mixIndex, &$gasMixes) {
            if (!isset($this->mixes[$ref])) return null;
            if (!isset($mixIndex[$ref])) {
                $m = $this->mixes[$ref];
                $gasMixes[] = ['oxygen' => $m['oxygen'], 'helium' => $m['helium'], 'nitrogen' => max(0, 100 - $m['oxygen'] - $m['helium']), 'usage' => 0];
                $mixIndex[$ref] = count($gasMixes) - 1;
            }
            return $mixIndex[$ref];
        };
        $tankRows = [];
        foreach ($this->all($x, 'tankdata') as $t) {
            $link = $this->el($t, 'link');
            $ref = $link ? $this->attr($link, 'ref') : '';
            $gi = $ref !== '' ? $addMix($ref) : null;
            $vol = $this->num($this->txt($t, 'tankvolume'));
            $tankRows[] = ['gasMix' => $gi, 'volume_m3' => $vol];
        }

        // Profile.
        $profile = [];
        $currentGas = null;
        $firstPressure = null;
        $lastPressure = null;
        foreach ($this->all($this->el($x, 'samples'), 'waypoint') as $w) {
            $sw = $this->el($w, 'switchmix');
            if ($sw !== null) {
                $gi = $addMix($this->attr($sw, 'ref'));
                if ($gi !== null) $currentGas = $gi;
            }
            $time = (int) round((float) $this->txt($w, 'divetime'));
            $depth = (float) $this->txt($w, 'depth');
            $s = ['time' => $time, 'depth' => round($depth, 3)];
            $tempK = $this->num($this->txt($w, 'temperature'));
            if ($tempK !== null) $s['temperature'] = round($tempK > 200 ? $tempK - 273.15 : $tempK, 2);
            $pPa = $this->num($this->txt($w, 'tankpressure'));
            if ($pPa !== null && $pPa > 0) {
                $bar = round($pPa > 2000 ? $pPa / 100000 : $pPa, 2);
                $s['pressure'] = $bar;
                $s['tankPressures'] = [$bar];
                $firstPressure ??= $bar;
                $lastPressure = $bar;
            }
            if ($currentGas !== null) $s['currentGas'] = $currentGas;
            $profile[] = $s;
        }
        if (count($profile) && !isset($profile[0]['currentGas']) && count($gasMixes)) {
            // Untagged start: breathing the first configured gas.
            foreach ($profile as &$s) { if (isset($s['currentGas'])) break; $s['currentGas'] = 0; }
            unset($s);
        }

        // Tanks: MacDive pads the list with zero-volume repeats of a mix
        // already listed; keep each mix once, the first (pressure-bearing)
        // cylinder carrying the single pressure stream the file has.
        $tanks = [];
        $seenMix = [];
        foreach ($tankRows as $i => $t) {
            if ($t['gasMix'] !== null && isset($seenMix[$t['gasMix']]) && !($t['volume_m3'] > 0)) continue;
            if ($t['gasMix'] !== null) $seenMix[$t['gasMix']] = true;
            $tanks[] = [
                'usage' => 'none',
                'volume' => 0,
                'workPressure' => 0,
                'beginPressure' => count($tanks) === 0 && $firstPressure !== null ? $firstPressure : 0,
                'endPressure' => count($tanks) === 0 && $lastPressure !== null ? $lastPressure : 0,
                'gasMix' => $t['gasMix'] ?? 4294967295,
            ];
        }

        $duration = (int) round((float) $this->txt($after, 'diveduration'));
        if ($duration <= 0 && count($profile)) $duration = (int) end($profile)['time'];
        $maxDepth = $this->num($this->txt($after, 'greatestdepth'));
        if ($maxDepth === null && count($profile)) $maxDepth = max(array_column($profile, 'depth'));
        $avgDepth = self::averageDepth($profile);
        $lowK = $this->num($this->txt($after, 'lowesttemperature'));
        $minTemp = $lowK === null ? null : round($lowK > 200 ? $lowK - 273.15 : $lowK, 1);

        $serial = $comp ? strtolower($comp['serial'] ?? '') : '';
        $model = $comp ? ($comp['model'] ?? '') : '';
        $family = self::family($model, $comp['manufacturer'] ?? '');
        // Shearwater: the wall-clock start time read as if it were UTC.
        $wallClockAsUtc = (new \DateTimeImmutable($started->format('Y-m-d H:i:s'), new \DateTimeZone('UTC')))->getTimestamp();
        $fingerprint = ($family === 'shearwaterPetrel' && $serial !== '')
            ? base64_encode(pack('N', $wallClockAsUtc))
            : 'sha256:' . hash('sha256', $started->format('Y-m-d\TH:i:s') . '|' . $duration . '|' . number_format((float) $maxDepth, 2, '.', ''));

        $notes = $this->txt($this->el($after, 'notes'), 'para');
        $notes = self::cleanNotes($notes);
        $rating = $this->txt($this->el($after, 'rating'), 'ratingvalue');

        return [
            'dive_number' => (int) $this->txt($before, 'divenumber') ?: null,
            'computer_family' => $family,
            'computer_model' => $model !== '' ? $model : null,
            'computer_serial' => $serial !== '' ? $serial : null,
            'fingerprint' => $fingerprint,
            'started_at' => $started->format('Y-m-d H:i:s'),
            'duration_seconds' => $duration,
            'max_depth_m' => round((float) $maxDepth, 2),
            'avg_depth_m' => $avgDepth,
            'min_water_temp_c' => $minTemp,
            'dive_mode' => 'open_circuit',
            'gas_mixes' => $gasMixes ?: null,
            'tanks' => $tanks ?: null,
            'profile' => $profile,
            'site_key' => $siteKey,
            'notes' => $notes !== '' ? $notes : null,
            'buddies' => $buddyNames,
            'rating' => $rating !== '' ? (int) $rating : null,
            'source' => 'uddf-import',
        ];
    }

    public static function family(string $model, string $manufacturer = ''): string
    {
        $m = strtolower($model . ' ' . $manufacturer);
        if (preg_match('/perdix|petrel|teric|peregrine|tern|predator|nerd|shearwater/', $m)) return 'shearwaterPetrel';
        if (preg_match('/descent|garmin/', $m)) return 'garmin';
        if (preg_match('/i\d{3}|atom|geo|pro plus|veo|oceanic|aeris|aqualung|hollis/', $m)) return 'oceanicAtom2';
        if (preg_match('/suunto|eon|d\d|zoop|vyper/', $m)) return 'suunto';
        if (preg_match('/mares|puck|quad|genius/', $m)) return 'mares';
        return 'uddf';
    }

    /** Time-weighted mean depth of a profile, or null without one. */
    public static function averageDepth(array $profile): ?float
    {
        if (count($profile) < 2) return null;
        $sum = 0; $span = 0;
        for ($i = 1; $i < count($profile); $i++) {
            $dt = ($profile[$i]['time'] ?? 0) - ($profile[$i - 1]['time'] ?? 0);
            if ($dt <= 0) continue;
            $sum += $dt * ((($profile[$i]['depth'] ?? 0) + ($profile[$i - 1]['depth'] ?? 0)) / 2);
            $span += $dt;
        }
        return $span > 0 ? round($sum / $span, 2) : null;
    }

    /** Strip MacDive's empty "Summary: / Environment: / Gas: / Gear: / Issues:" template. */
    public static function cleanNotes(string $notes): string
    {
        $lines = preg_split('/\R/', $notes);
        $kept = [];
        foreach ($lines as $line) {
            $t = trim($line);
            if ($t === '' || preg_match('/^(Summary|Environment|Gas|Gear|Issues):\s*$/i', $t)) continue;
            $kept[] = $t;
        }
        return trim(implode("\n", $kept));
    }
}
