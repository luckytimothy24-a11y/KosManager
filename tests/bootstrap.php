<?php

require __DIR__.'/../vendor/autoload.php';

/*
 * Set default test environment variables that phpunit.xml's <php><env>
 * section is expected to provide but does NOT reliably apply on Windows
 * (PHP's variables_order=GPCS omits "E"). Without APP_ENV=testing the app
 * boots in the local environment, CSRF verification is enforced (not
 * auto-skipped), and every HTML POST in the test suite fails with a 419.
 *
 * putenv() populates the real process environment, so getenv()/$_ENV
 * reflect these values when the Laravel kernel bootstraps Dotenv
 * (immutable loader will not override already-set variables).
 */
$testEnv = [
    'APP_ENV' => 'testing',
    'BCRYPT_ROUNDS' => '4',
    'CACHE_DRIVER' => 'array',
    'DB_CONNECTION' => 'sqlite',
    'DB_DATABASE' => ':memory:',
    'MAIL_MAILER' => 'array',
    'QUEUE_CONNECTION' => 'sync',
    'SESSION_DRIVER' => 'array',
    'TELESCOPE_ENABLED' => 'false',
    'PAYMENT_GATEWAY_WEBHOOK_KEY' => 'phpunit-test-webhook-secret-for-kosmanager',
    'AUDIT_LOG_HMAC_SECRET' => 'aabbccddeeff00112233445566778899aabbccddeeff00112233445566778899',
];

foreach ($testEnv as $key => $value) {
    if (getenv($key) === false) {
        putenv($key.'='.$value);
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
}
