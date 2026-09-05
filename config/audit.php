<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Audit Log HMAC Secret
    |--------------------------------------------------------------------------
    |
    | A 64-character hex string used to sign each audit log entry with
    | HMAC-SHA256. The secret is validated strictly: it must be exactly 64
    | hexadecimal characters. If it is missing, too short, or not valid hex,
    | the system FAILS CLOSED — new entries are stored with integrity_hash
    | = NULL and are counted as unverified, instead of silently falling back
    | to an insecure key.
    |
    | Generate with: php artisan tinker --execute="echo bin2hex(random_bytes(32))"
    |
    */

    'hmac_secret' => env('AUDIT_LOG_HMAC_SECRET'),

];
