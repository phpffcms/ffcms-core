<?php


namespace Ffcms\Core\Helper\Type;

/**
 * Class Str. Helper to work with string variables
 * @package Ffcms\Core\Helper\Type
 */
class Str
{
    /**
     * Check if string is empty (check null, false and '' values)
     * @param string|null $string
     * @return bool
     */
    public static function likeEmpty(?string $string = null): bool
    {
        return $string === null || $string === '' || $string === false;
    }

    /**
     * Check is $where starts with prefix $string
     * @param string $what
     * @param string $where
     * @return bool
     */
    public static function startsWith(?string $what, ?string $where): bool
    {
        // check is not empty string
        if (self::likeEmpty($what) || self::likeEmpty($where)) {
            return false;
        }

        $prefix = self::sub($where, 0, self::length($what));
        return $prefix === $what;
    }

    /**
     * Check is $where ends with suffix $string
     * @param string $what
     * @param string $where
     * @return bool
     */
    public static function endsWith(?string $what, ?string $where): bool
    {
        // check is not empty string
        if (self::likeEmpty($what) || self::likeEmpty($where)) {
            return false;
        }

        $suffix = self::sub($where, -self::length($what));
        return $suffix === $what;
    }

    /**
     * Find first entry in $string before $delimiter
     * @param string $string
     * @param string $delimiter
     * @return string|null
     */
    public static function firstIn(?string $string, ?string $delimiter): ?string
    {
        if (self::likeEmpty($string) || self::likeEmpty($delimiter)) {
            return null;
        }

        return strstr($string, $delimiter, true);
    }

    /**
     * Find latest entry in $string after $delimiter
     * @param string $string
     * @param string $delimiter
     * @param bool $withoutDelimiter
     * @return string|null
     */
    public static function lastIn(?string $string, ?string $delimiter, bool $withoutDelimiter = false): ?string
    {
        if (self::likeEmpty($string) || self::likeEmpty($delimiter)) {
            return null;
        }

        $pos = mb_strrpos($string, $delimiter);
        // if entry is not founded return false
        if (!Any::isInt($pos)) {
            return null;
        }

        // remove delimiter pointer
        if ($withoutDelimiter) {
            $pos++;
        }

        return self::sub($string, $pos);
    }

    /**
     * Remove extension from string
     * @param string $string
     * @return string|null
     */
    public static function cleanExtension(?string $string): ?string
    {
        // no extension in string is founded
        if (!self::contains('.', $string)) {
            return $string;
        }

        $splited = explode('.', $string);
        array_pop($splited);
        return implode('.', $splited);
    }


    /**
     * Calculate $string length according UTF-8 encoding
     * @param string $string
     * @return int
     */
    public static function length(?string $string): int
    {
        return mb_strlen($string, 'UTF-8');
    }

    /**
     * Change content to lower case. Analog of strtolower with UTF-8
     * @param string|null $string
     * @return string|null
     */
    public static function lowerCase(?string $string): ?string
    {
        if ($string === null) {
            return null;
        }

        return mb_strtolower($string, 'UTF-8');
    }

    /**
     * Change content to upper case. Analog of strtoupper with UTF-8
     * @param string|null $string
     * @return string|null
     */
    public static function upperCase(?string $string = null): ?string
    {
        if ($string === null) {
            return null;
        }

        return mb_strtoupper($string, 'UTF-8');
    }

    /**
     * Count string entries of search
     * @param string $where
     * @param string $what
     * @return int
     */
    public static function entryCount(string $where, string $what): int
    {
        return mb_substr_count($where, $what, 'UTF-8');
    }

    /**
     * Split camel case words with glue. camelCaseWords => Camel case words
     * @param string $string
     * @param string $glue
     * @return string
     */
    public static function splitCamelCase(string $string, string $glue = ' '): string
    {
        $expression = '/(?#! splitCamelCase Rev:20140412)
                        # Split camelCase "words". Two global alternatives. Either g1of2:
                          (?<=[a-z])      # Position is after a lowercase,
                          (?=[A-Z])       # and before an uppercase letter.
                        | (?<=[A-Z])      # Or g2of2; Position is after uppercase,
                          (?=[A-Z][a-z])  # and before upper-then-lower case.
                        /x';
        $split = preg_split($expression, $string);
        $output = [];
        $count = count($split);
        for ($i = 0; $i < $count; ++$i) { // @todo: rework me with foreach() cycle
            $word = strtolower($split[$i]);
            if ($i === 0) {
                $word = ucfirst($word);
            }
            $output[] = $word;
        }

        return implode($glue, $output);
    }

    /**
     * Alias for mb_substr() function
     * @param string $string
     * @param int $start
     * @param int|null $length
     * @param string $encode
     * @return string
     */
    public static function sub(string $string, int $start, ?int $length = null, string $encode = 'UTF-8'): ?string
    {
        return mb_substr($string, $start, $length, $encode);
    }

    /**
     * Case-sensive replace $needle to $replacement in all string $haystack entry. Alias for function str_replace()
     * @param string|array $needle
     * @param string|array $replacement
     * @param string $haystack
     * @return string
     */
    public static function replace($needle, $replacement, ?string $haystack): ?string
    {
        if ($haystack === null) {
            return null;
        }

        return str_replace($needle, $replacement, $haystack);
    }

    /**
     * Case-ignore replace $needle to $replacement in string $haystack. Alias for function str_ireplace()
     * @param string|array $needle
     * @param string|array $replacement
     * @param string $haystack
     * @return string
     */
    public static function ireplace($needle, $replacement, ?string $haystack): ?string
    {
        if ($haystack === null) {
            return null;
        }

        return str_ireplace($needle, $replacement, $haystack);
    }

    /**
     * Search substring $what is string $where
     * @param string $what
     * @param string $where
     * @return bool
     */
    public static function contains(string $what, string $where): bool
    {
        return mb_strpos($where, $what, 0, 'UTF-8') !== false;
    }

    /**
     * Check if string is valid url address
     * @param string $string
     * @return bool
     */
    public static function isUrl(string $string): bool
    {
        return (!filter_var($string, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED) === false);
    }

    /**
     * Pseudo random [A-Za-z0-9] string with defined $length
     * @param int $length
     * @return string
     */
    public static function randomLatinNumeric(int $length): string
    {
        $ret = 97;
        $out = null;
        for ($i = 0; $i < $length; $i++) {
            $offset = mt_rand(0, 15);
            $char = chr($ret + $offset);
            $posibility = mt_rand(0, 2);
            if ($posibility == 0) {
                // 33% - add random numeric
                $out .= mt_rand(0, 9);
            } elseif ($posibility == 1) {
                // 33% - make upper offset+ret
                $out .= strtoupper($char);
            } else {
                $out .= $char;
            }
        }
        return $out;
    }

    /**
     * Pseudo random [A-Za-z] string with defined $length
     * @param int $length
     * @return string
     */
    public static function randomLatin(int $length): string
    {
        $ret = 97;
        $out = null;
        for ($i = 0; $i < $length; $i++) {
            $offset = mt_rand(0, 15);
            $char = chr($ret + $offset);
            $posibility = mt_rand(0, 1);
            if ($posibility == 1) {
                // 50% - make upper offset+ret
                $out .= strtoupper($char);
            } else {
                $out .= $char;
            }
        }
        return $out;
    }

    /**
     * Check is $string look's like email
     * @param string $string
     * @return bool
     */
    public static function isEmail(string $string): bool
    {
        return filter_var($string, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Check is $string sounds like a phone number
     * @param string $string
     * @return bool
     */
    public static function isPhone(?string $string = null): bool
    {
        return (bool)preg_match('/^[+]?([\d]{0,3})?[\(\.\-\s]?([\d]{3})[\)\.\-\s]*([\d]{3})[\.\-\s]?([\d]{4})$/', $string);
    }

    /**
     * Concat string with add string by separator
     * @param string $separator
     * @param string $string1
     * @param string $string2
     * @return string
     */
    public static function concat()
    {
        $args = func_get_args();
        $separator = array_shift($args);

        $output = null;
        foreach ($args as $string) {
            $output .= $string . $separator;
        }

        $output = rtrim($output, $separator);
        return $output;
    }

    /**
     * Check if var1 is equal to var2 in binary-safe mode with ignore case
     * @param string $var1
     * @param string $var2
     * @return bool
     */
    public static function equalIgnoreCase(string $var1, string $var2): bool
    {
        return strcasecmp($var1, $var2) === 0;
    }
}
