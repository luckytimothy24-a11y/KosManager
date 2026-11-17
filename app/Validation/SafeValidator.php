<?php

namespace App\Validation;

use Illuminate\Validation\Validator;

/**
 * Backport Laravel 12.60's CR/LF rejection for the "email" rule.
 *
 * egulias/email-validator accepts folding whitespace (CRLF) inside a quoted
 * local-part, which can let injected header text reach Symfony Mailer.
 * Until the framework can be upgraded, reject any CR/LF before delegating
 * to the native validator.
 */
class SafeValidator extends Validator
{
    public function validateEmail($attribute, $value, $parameters)
    {
        if (preg_match('/[\r\n]/', (string) $value) > 0) {
            return false;
        }

        return parent::validateEmail($attribute, $value, $parameters);
    }
}
