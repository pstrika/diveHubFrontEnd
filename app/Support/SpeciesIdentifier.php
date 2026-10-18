<?php

namespace App\Support;

use App\Models\DivePhoto;
use App\Models\Species;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * "What's this?" (Pablo, 2026-10-18): suggests catalog species for a dive
 * photo using Claude's vision. The model only picks from a numbered list
 * of the species this diver can tag (catalog + their own); numbers it
 * makes up are dropped. Nothing is tagged here - the diver confirms.
 * Answers are kept on the photo (ai_suggestions), so asking twice is free.
 */
class SpeciesIdentifier
{
    public static function enabled(?object $user): bool
    {
        return (bool) config('services.anthropic.key') && EarlyAccess::allows($user, 'species');
    }

    /**
     * @return array{candidates: array<int, array{uuid:string,name:string,scientific:?string,category:string,confidence:string,photo_url:?string}>, not_in_catalog: ?string, cached: bool}
     */
    public static function identify(DivePhoto $photo, bool $fresh = false): array
    {
        $species = Species::visibleTo($photo->user_id)->orderBy('common_name')->get();

        if (!$fresh && is_array($photo->ai_suggestions)) {
            return self::present($photo->ai_suggestions, $species) + ['cached' => true];
        }

        // A cap per diver per day keeps the bill predictable.
        $key = 'species-id:' . $photo->user_id . ':' . now()->toDateString();
        $limit = (int) config('services.anthropic.species_daily_limit', 50);
        if (Cache::get($key, 0) >= $limit) {
            throw ValidationException::withMessages(['photo' => "You've used today's $limit species identifications. Try again tomorrow."]);
        }

        $path = $photo->path('web');
        if (!is_file($path)) {
            throw ValidationException::withMessages(['photo' => 'This photo file is missing.']);
        }

        $list = [];
        foreach ($species->values() as $i => $s) {
            $list[] = ($i + 1) . ' | ' . $s->common_name . ' | ' . ($s->scientific_name ?: '-') . ' | ' . $s->categoryLabel();
        }

        $model = config('services.anthropic.species_model', 'claude-haiku-5-5');
        $response = Http::withHeaders([
            'x-api-key' => config('services.anthropic.key'),
            'anthropic-version' => '2023-06-01',
        ])->timeout(60)->post('https://api.anthropic.com/v1/messages', [
            'model' => $model,
            'max_tokens' => 400,
            'system' => 'You identify marine life in underwater photos taken by scuba divers, mostly in South Florida and the Caribbean. '
                . 'Only name animals or corals that are clearly visible. Pick from the numbered catalog the user gives you. '
                . 'Reply with JSON only, no other text: {"candidates":[{"n":<catalog number>,"confidence":"high"|"medium"|"low"}],"not_in_catalog":<short common name or null>}. '
                . 'At most 3 candidates, most likely first; an empty list if nothing identifiable is in the photo (a diver, open water, a blurry shot). '
                . 'Use not_in_catalog only when the main subject is clearly an animal that is not in the catalog.',
            'messages' => [[
                'role' => 'user',
                'content' => [
                    ['type' => 'image', 'source' => ['type' => 'base64', 'media_type' => 'image/webp', 'data' => base64_encode(file_get_contents($path))]],
                    ['type' => 'text', 'text' => "Catalog (number | common name | scientific name | group):\n" . implode("\n", $list) . "\n\nWhich of these are in the photo?"],
                ],
            ]],
        ]);

        if (!$response->successful()) {
            Log::warning('Species identification failed: ' . $response->status() . ' ' . mb_substr($response->body(), 0, 300));
            throw ValidationException::withMessages(['photo' => "Couldn't identify this photo right now. Try again in a minute."]);
        }
        Cache::put($key, Cache::get($key, 0) + 1, now()->endOfDay());

        $text = collect($response->json('content', []))->where('type', 'text')->pluck('text')->implode('');
        $parsed = json_decode(self::firstJsonObject($text), true) ?: [];

        $byNumber = $species->values();
        $candidates = [];
        foreach (array_slice($parsed['candidates'] ?? [], 0, 3) as $c) {
            $n = (int) ($c['n'] ?? 0);
            $s = $byNumber[$n - 1] ?? null;
            if (!$s || isset($candidates[$s->uuid])) continue; // not in the list we sent: dropped
            $conf = in_array($c['confidence'] ?? '', ['high', 'medium', 'low'], true) ? $c['confidence'] : 'low';
            $candidates[$s->uuid] = ['uuid' => $s->uuid, 'confidence' => $conf];
        }
        $stored = [
            'model' => $model,
            'at' => now()->toIso8601String(),
            'candidates' => array_values($candidates),
            'not_in_catalog' => is_string($parsed['not_in_catalog'] ?? null) ? mb_substr($parsed['not_in_catalog'], 0, 80) : null,
        ];
        $photo->forceFill(['ai_suggestions' => $stored])->saveQuietly();

        return self::present($stored, $species) + ['cached' => false];
    }

    /** Stored suggestions -> what the viewer shows (species that still exist only). */
    private static function present(array $stored, $species): array
    {
        $byUuid = $species->keyBy('uuid');
        $out = [];
        foreach ($stored['candidates'] ?? [] as $c) {
            $s = $byUuid[$c['uuid']] ?? null;
            if (!$s) continue;
            $out[] = [
                'uuid' => $s->uuid, 'name' => $s->common_name, 'scientific' => $s->scientific_name,
                'category' => $s->categoryLabel(), 'confidence' => $c['confidence'],
                'photo_url' => $s->photoUrl(), 'icon_url' => $s->iconUrl(),
            ];
        }
        return ['candidates' => $out, 'not_in_catalog' => $stored['not_in_catalog'] ?? null];
    }

    private static function firstJsonObject(string $text): string
    {
        $start = strpos($text, '{');
        $end = strrpos($text, '}');
        return ($start !== false && $end !== false && $end > $start) ? substr($text, $start, $end - $start + 1) : '{}';
    }
}
