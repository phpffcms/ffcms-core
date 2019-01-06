<?php

namespace Ffcms\Core\Helper;

use Ffcms\Core\Helper\Type\Str;

/**
 * Class Crypt. All cryptography features simplification for ffcms
 * @package Ffcms\Core\Helper
 */
class Crypt
{
    const PASSWORD_CRYPT_ALGO = PASSWORD_BCRYPT;
    const PASSWORD_CRYPT_COST = 12;

    /**
     * Generate password hash using php password_hash with predefined algo
     * @param string $text
     * @return null|string
     */
    public static function passwordHash(string $text): ?string
    {
        return password_hash($text, self::PASSWORD_CRYPT_ALGO, [
            'cost' => self::PASSWORD_CRYPT_COST
        ]);
    }

    /**
     * Verify password to hash equality
     * @param string $password
     * @param string $hash
     * @return bool
     */
    public static function passwordVerify(string $password, string $hash): bool
    {
        return (Str::length($hash) > 0 && password_verify($password, $hash));
    }

    /**
     * Check if password encryption is from old ffcms version (3.0 blowfish with cost=7 and predefined salt)
     * @param string $hash
     * @return bool
     */
    public static function isOldPasswordHash(string $hash): bool
    {
        return Str::startsWith('$2a$07$', $hash);
    }

    /**
     * Generate random string with numbers from secure function random_bytes
     * @param int $length
     * @return string
     */
    public static function randomString(int $length): string
    {
        try {
            $rand = bin2hex(random_bytes($length));
            // bytes_length = length * 2
            $rand = substr($rand, 0, $length);
        } catch (\Exception $ce) {
            $rand = Str::randomLatinNumeric($length);
        }

        return $rand;
    }
}
