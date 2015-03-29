<?php


namespace Ffcms\Core\Helper;

class String {

    /**
     * Check is $where starts with prefix $string
     * @param string $string
     * @param string $where
     * @return bool
     */
    public static function startsWith($string, $where)
    {
        // check is not empty string
        if(self::length($string) < 1 || self::length($where) < 1)
        {
            return false;
        }
        $pharse_prefix = self::substr($where, 0, self::length($string));
        return $pharse_prefix === $string ? true : false;
    }

    /**
     * Check is $where ends with suffix $string
     * @param string $string
     * @param string $where
     * @return bool
     */
    public static function endsWith($string, $where)
    {
        // check is not empty string
        if(self::length($string) < 1 || self::length($where))
        {
            return false;
        }
        $pharse_suffix = self::substr($where, -self::length($string));
        return $pharse_suffix === $string ? true : false;
    }


    /**
     * Calculate $string length according UTF-8 encoding
     * @param string $string
     * @return int
     */
    public static function length($string)
    {
        return mb_strlen($string, "UTF-8");
    }

    /**
     * Split camel case words with glue. camelCaseWords => camel Case Words
     * @param string $string
     * @param string $glue
     * @return string
     */
    public static function splitCamelCase($string, $glue = ' ')
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
        for($i=0;$i<sizeof($split);++$i) {
            $word = strtolower($split[$i]);
            if($i === 0)
                $word = ucfirst($word);
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
    public static function substr($string, $start, $length = null, $encode = 'UTF-8')
    {
        return mb_substr($string, $start, $length, $encode);
    }

    /**
     * Search entery's in string $where by string $what
     * @param $what
     * @param $where
     * @return bool
     */
    public function contains($what, $where)
    {
        return mb_strpos($where, $what, 0, "UTF-8") !== false ? true : false;
    }

    /**
     * Pseudo random [A-Za-z0-9] string with length $length
     * @param int $length
     * @return string
     */
    public function random($length)
    {
        $ret = 97;
        $out = null;
        for ($i = 0; $i < $length; $i++) {
            $offset = rand(0, 15);
            $char = chr($ret + $offset);
            $posibility = rand(0, 2);
            if ($posibility == 0) {
                // 33% - add random numeric
                $out .= rand(0, 9);
            } elseif ($posibility == 1) {
                // 33% - make upper offset+ret
                $out .= strtoupper($char);
            } else {
                $out .= $char;
            }
        }
        return $out;
    }






}