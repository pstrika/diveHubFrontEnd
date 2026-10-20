<?php

namespace App\Models\Concerns;

use App\Support\CloudflareStream;

/**
 * What a row that points at a Cloudflare Stream video shares (DiveVideo,
 * SiteVideo): the status poll, and the playback / thumbnail URLs. Columns:
 * stream_uid, stream_status, stream_error, customer_host, duration_seconds,
 * width, height, size_bytes, checked_at.
 */
trait StreamVideo
{
    public function isReady(): bool
    {
        return $this->stream_status === 'ready';
    }

    /**
     * Ask Cloudflare how the video is doing - at most every 5 s unless
     * forced, and never once it's ready or failed.
     */
    public function refreshStream(bool $force = false): static
    {
        if (!$force && in_array($this->stream_status, ['ready', 'failed'], true)) return $this;
        if (!$force && $this->checked_at && $this->checked_at->gt(now()->subSeconds(5))) return $this;
        try {
            $this->applyStreamDetails(CloudflareStream::details($this->stream_uid));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Cloudflare Stream status check failed: ' . $e->getMessage());
            $this->checked_at = now();
            $this->save();
        }
        return $this;
    }

    public function applyStreamDetails(?array $d): void
    {
        if ($d === null) {
            $this->stream_status = 'failed';
            $this->stream_error = 'The video is no longer on Cloudflare.';
        } else {
            $state = $d['status']['state'] ?? null;
            if (!empty($d['readyToStream']) || $state === 'ready') {
                $this->stream_status = 'ready';
                $this->stream_error = null;
            } elseif ($state === 'error') {
                $this->stream_status = 'failed';
                $this->stream_error = mb_substr((string) ($d['status']['errorReasonText'] ?? 'Cloudflare could not process this video.'), 0, 255);
            } elseif ($state === 'pendingupload') {
                $this->stream_status = $this->stream_status === 'processing' ? 'processing' : 'uploading';
            } else {
                $this->stream_status = 'processing';
            }
            // Cloudflare reports -1 for what it doesn't know yet.
            if (($d['duration'] ?? 0) > 0) $this->duration_seconds = (int) round($d['duration']);
            if (($d['input']['width'] ?? 0) > 0) $this->width = (int) $d['input']['width'];
            if (($d['input']['height'] ?? 0) > 0) $this->height = (int) $d['input']['height'];
            if (($d['size'] ?? 0) > 0) $this->size_bytes = (int) $d['size'];
            $host = parse_url($d['preview'] ?? $d['thumbnail'] ?? '', PHP_URL_HOST);
            if ($host) $this->customer_host = $host;
        }
        $this->checked_at = now();
        $this->save();
    }

    /** Owner-only playback: URLs carry a short-lived token. */
    private function tokenBase(): ?string
    {
        if (!$this->customer_host || !$this->isReady()) return null;
        return 'https://' . $this->customer_host . '/' . CloudflareStream::token($this->stream_uid);
    }

    public function playerUrl(): ?string
    {
        $b = $this->tokenBase();
        return $b ? $b . '/iframe' : null;
    }

    public function hlsUrl(): ?string
    {
        $b = $this->tokenBase();
        return $b ? $b . '/manifest/video.m3u8' : null;
    }

    public function thumbnailUrl(int $height = 360): ?string
    {
        $b = $this->tokenBase();
        return $b ? $b . '/thumbnails/thumbnail.jpg?time=1s&height=' . $height : null;
    }

    /** Public playback (a video approved for a site page: signed URLs turned off). */
    public function publicPlayerUrl(): ?string
    {
        return $this->customer_host && $this->isReady() ? 'https://' . $this->customer_host . '/' . $this->stream_uid . '/iframe' : null;
    }

    public function publicThumbnailUrl(int $height = 360): ?string
    {
        return $this->customer_host && $this->isReady() ? 'https://' . $this->customer_host . '/' . $this->stream_uid . '/thumbnails/thumbnail.jpg?time=1s&height=' . $height : null;
    }

    public function durationLabel(): ?string
    {
        if (!$this->duration_seconds) return null;
        return floor($this->duration_seconds / 60) . ':' . str_pad($this->duration_seconds % 60, 2, '0', STR_PAD_LEFT);
    }
}
