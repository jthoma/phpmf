<?php

/**
 * @package MariaFramework
 * @subpackage formatvalidatorPlugin
 * @author Jiju Thomas Mathew
 */

class formatvalidator
{
    public static function validate(string $format, string $value): bool
    {
        switch ($format) {
            case 'email':
                return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;

            case 'password':
                // Adjust rules as needed
                return strlen($value) >= 8;

            case 'ipv4':
                return filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false;

            case 'ipv6':
                return filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false;

            case 'uri':
                return filter_var($value, FILTER_VALIDATE_URL) !== false;

            case 'uri-reference':
                return self::isValidUriReference($value);

            case 'date':
                return self::isValidDate($value);

            case 'date-time':
                return self::isValidDateTime($value);

            case 'byte':
                return self::isValidBase64($value);

            case 'binary':
                return !mb_check_encoding($value, 'UTF-8');

            default:
                // Unknown format; return false or true depending on policy
                return false;
        }
    }

    protected static function isValidUriReference(string $value): bool
    {
        $parts = parse_url($value);
        return $parts !== false;
    }

    protected static function isValidDate(string $value): bool
    {
        $date = DateTime::createFromFormat('Y-m-d', $value);
        return $date && $date->format('Y-m-d') === $value;
    }

    protected static function isValidDateTime(string $value): bool
    {
        $date = DateTime::createFromFormat(DateTime::ATOM, $value);
        if ($date && $date->format(DateTime::ATOM) === $value) {
            return true;
        }
        try {
            new DateTime($value);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    protected static function isValidBase64(string $value): bool
    {
        return base64_encode(base64_decode($value, true)) === $value;
    }
}

