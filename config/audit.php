<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Audit Log HMAC Secret
    |--------------------------------------------------------------------------
    |
    | A 64-character hex string used to sign each audit log entry with
    | HMAC-SHA256.  The system is fail-closed: if the key is missing or
    | shorter than 64 hex chars, new entries will have integrity_hash = null
    | and verifyChain() will report failure.
    |
    | Generate with: php artisan tinker --execute="echo bin2hex(random_bytes(32))"
    |
    */

    'hmac_secret' => env('AUDIT_LOG_HMAC_SECRET'),

];
