<?php

namespace Ffcms\Core\Helper\Type;

class Arr
{

    /**
     * Check is $needle in $haystack. Alias to function in_array().
     * @param string $needle
     * @param array $haystack
     * @param bool $strict
     * @return bool
     */
    public static function in($needle, $haystack, $strict = true)
    {
        // prevent errors
        if (!Obj::isArray($haystack)) {
            return false;
        }
        return in_array($needle, $haystack, $strict);
    }

    /**
     * Alternative function for array_merge - safe for use with any-type params.
     * @return array
     */
    public static function merge()
    {
        $arguments = [];
        foreach (func_get_args() as $key => $val) {
            if (!Obj::isArray($val)) {
                $val = [];
            }
            $arguments[$key] = $val;
        }
        return call_user_func_array('array_merge', $arguments);
    }

    public static function mergeRecursive()
    {
        $arguments = [];
        foreach (func_get_args() as $key => $val) {
            if (!Obj::isArray($val)) {
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
    public static function getByPath($path, $array = null, $delimiter = '.')
    {
        // path of nothing? interest
        if (!Obj::isArray($array) || count($array) < 1) {
            return null;
        }

        // c'mon man, what the f*ck are you doing? ))
        if (!Str::contains($delimiter, $path)) {
            return $array[$path];
        }

        $output = $array;
        $pathArray = explode($delimiter, $path);
        foreach ($pathArray as $key) {
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
    public static function ploke($key, $array)
    {
        if (!Obj::isArray($array)) {
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
                return var_export($var, TRUE);
        }
    }
    
    /**
     * Check if array contains only numeric values
     * @param array $array
     * @return boolean
     */
    public static function onlyNumericValues($array)
    {
        return is_numeric(implode('', $array));
    }
}