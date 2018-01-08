<?php

namespace Ffcms\Core\Helper\Type;

/**
 * Class Arr. Helper for work with arrays and it data.
 * @package Ffcms\Core\Helper\Type
 */
class Arr
{

    /**
     * Check is $needle in $haystack. Alias to function in_array().
     * @param string|int|float $needle
     * @param array $haystack
     * @param bool $strict
     * @return bool
     */
    public static function in($needle, ?array $haystack = null, bool $strict = true): bool
    {
        // prevent errors
        if (!Any::isArray($haystack) || $needle === null) {
            return false;
        }

        return in_array($needle, $haystack, $strict);
    }

    /**
     * Alternative function for array_merge - safe for use with any-type params.
     * @return array
     */
    public static function merge(): array
    {
        $arguments = [];
        foreach (func_get_args() as $key => $val) {
            if (!Any::isArray($val)) {
                $val = [];
            }

            $arguments[$key] = $val;
        }
        return call_user_func_array('array_merge', $arguments);
    }

    /**
     * Alternative function for array_merge_recursive - safe for use with any params
     * @return array
     */
    public static function mergeRecursive(): array
    {
        $arguments = [];
        foreach (func_get_args() as $key => $val) {
            if (!Any::isArray($val)) {
                $val = [];
            }

            $arguments[$key] = $val;
        }
        return call_user_func_array('array_merge_recursive', $arguments);
    }

    /**
     * Get array item by path separated by dots. Example: getByPath('dir.file', ['dir' => ['file' => 'text.txt']]) return "text.txt"
     * @param string $path
     * @param array|null $array
     * @param string $delimiter
     * @return array|string|null
     */
    public static function getByPath(string $path, ?array $array = null, $delimiter = '.')
    {
        // path of nothing? interest
        if (!Any::isArray($array) || count($array) < 1 || !Any::isStr($path) || Str::likeEmpty($path)) {
            return null;
        }

        // c'mon man, what the f*ck are you doing? ))
        if (!Str::contains($delimiter, $path)) {
            return $array[$path];
        }

        $output = $array;
        $pathArray = explode($delimiter, $path);
        foreach ($pathArray as $key) {
            if (!Any::isArray($output) || !array_key_exists($key, $output)) {
                return null;
            }

            $output = $output[$key];
        }
        return $output;
    }

    /**
     * Extract from multi-array elements by key to single-level array
     * @param string $key
     * @param array $array
     * @return array
     */
    public static function pluck(?string $key = null, ?array $array = null): array
    {
        if (!Any::isArray($array) || !Any::isStr($key)) {
            return [];
        }

        $output = [];
        foreach ($array as $item) {
            $object = $item[$key];
            if (!self::in($object, $output)) {
                $output[] = $object;
            }
        }
        return $output;
    }

    /**
     * Alternative var_export function for php >= 5.4 syntax
     * @param array|string $var
     * @param string|null $indent
     * @param boolean $guessTypes
     * @return string|null
     */
    public static function exportVar($var, $indent = null, $guessTypes = false)
    {
        switch (gettype($var)) {
            case 'string':
                // guess boolean type for "1" and "0"
                if (true === $guessTypes) {
                    if ($var === '0' || $var === '') {
                        return 'false';
                    } elseif ($var === '1') {
                        return 'true';
                    }
                }
                return '\'' . $var . '\'';
            case 'array':
                $indexed = array_keys($var) === range(0, count($var) - 1);
                $row = [];
                foreach ($var as $key => $value) {
                    $row[] = $indent . "\t"
                        . ($indexed ? null : self::exportVar($key, null, $guessTypes) . ' => ')
                        . self::exportVar($value, $indent . "\t", $guessTypes);
                }
                return "[\n" . implode(",\n", $row) . "\n" . $indent . ']';
            case 'boolean':
                return $var ? 'true' : 'false';
            default:
                return var_export($var, true);
        }
    }
    
    /**
     * Check if array contains only numeric values
     * @param array $array
     * @return boolean
     */
    public static function onlyNumericValues(?array $array = null)
    {
        if (!Any::isArray($array)) {
            return false;
        }

        return is_numeric(implode('', $array));
    }
}
