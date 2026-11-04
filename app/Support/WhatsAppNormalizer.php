<?php

namespace App\Support;

/**
 * WhatsApp number normalization for Indonesian phone numbers.
 *
 * Supported input formats:
 * - 08123456789 (local format, already starts with 0)
 * - 628123456789 (country code without plus)
 * - +628123456789 (country code with plus)
 * - 00628123456789 (double zero format)
 *
 * Normalized output: always 08... format
 *
 * Rules (in order):
 * 1. If starts with '0062', remove '00', prepend '0' -> results in 08...
 * 2. If starts with '062' (variant of above), remove '062', prepend '0' -> results in 08...
 * 3. If starts with '+62', remove '+', prepend '0'
 * 4. If starts with '62' (and not already starting with '0'), prepend '0'
 * 5. If already starts with '0', keep as-is (prevents double 0; catches already-correct 08... format)
 * 6. Short/ invalid numbers are left as-is (validation happens separately)
 */
class WhatsAppNormalizer
{
    /**
     * Normalize a WhatsApp phone number to standard Indonesian format.
     *
     * @param  string  $phone  Raw phone number from user input
     * @return string Normalized phone number
     */
    public static function normalize(string $phone): string
    {
        $phone = trim((string) $phone);
        $phone = preg_replace('/[\s\-\(\)]+/', '', $phone);

        // Rule 1: If starts with '0062', remove '00', prepend '0' -> results in 08...
        if (str_starts_with($phone, '0062')) {
            return '0'.substr($phone, 4);
        }

        // Rule 2: If starts with '062' (variant of above), remove '062', prepend '0' -> results in 08...
        if (str_starts_with($phone, '062')) {
            return '0'.substr($phone, 3);
        }

        // Rule 3: If starts with '+62', remove '+', prepend '0'
        if (str_starts_with($phone, '+62')) {
            return '0'.substr($phone, 3);
        }

        // Rule 4: If starts with '62' (lenient, minimum 10 chars total = 62 + 8 digits)
        if (str_starts_with($phone, '62') && strlen($phone) >= 10) {
            return '0'.substr($phone, 2);
        }

        // Rule 5: If already starts with '0', keep as-is (prevents double 0;
        // catches already-correct 08... format)
        if (str_starts_with($phone, '0')) {
            return $phone;
        }

        // Default: return as-is (will be validated separately for min length)
        return $phone;
    }
}
