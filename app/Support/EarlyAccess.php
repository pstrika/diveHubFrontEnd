<?php

namespace App\Support;

/**
 * Features still limited to a few accounts (config/divehub.php
 * early_access: feature => lower-case emails). Opening one up is a config
 * edit. Pablo, 2026-10-17: equipment and marine life, Pablo only for now.
 */
class EarlyAccess
{
    public static function allows(?object $user, string $feature): bool
    {
        if (!$user) return false;
        $emails = array_map('strtolower', config("divehub.early_access.$feature", []));
        return in_array('*', $emails, true) || in_array(strtolower((string) $user->email), $emails, true);
    }
}
