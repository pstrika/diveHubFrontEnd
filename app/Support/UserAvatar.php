<?php

namespace App\Support;

/**
 * A member's profile picture, or the default avatar - and specifically,
 * the default when users.picture has a value but that file isn't actually
 * on disk (2026-09-11): the dev/test environment is a clone of the repo,
 * not the production upload volume, so any member who uploaded a photo
 * there shows a broken image here until the real server data is in place.
 * Treating "value but no file" the same as "no value" avoids that without
 * needing to know which environment is running.
 */
final class UserAvatar
{
    public static function url(?string $picture): string
    {
        return self::exists($picture) ? self::resolve($picture) : asset('assets') . '/img/default-avatar.png';
    }

    /** True when $picture is set and actually resolvable - a remote URL, or a local file that's really on disk. */
    public static function exists(?string $picture): bool
    {
        if (!$picture) {
            return false;
        }
        if (str_starts_with($picture, 'http://') || str_starts_with($picture, 'https://')) {
            return true;
        }
        return is_file(public_path('assets/img/users/' . ltrim($picture, '/')));
    }

    private static function resolve(string $picture): string
    {
        if (str_starts_with($picture, 'http://') || str_starts_with($picture, 'https://')) {
            return $picture;
        }
        return asset('assets') . '/img/users/' . ltrim($picture, '/');
    }
}
