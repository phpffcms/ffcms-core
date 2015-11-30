<?php

namespace Ffcms\Core\Helper;

use Ffcms\Core\App;
use Ffcms\Core\Helper\Type\Arr;
use Ffcms\Core\Helper\Type\Obj;
use Ffcms\Core\Helper\Type\Str;

class Security
{

    protected $purifier;


    public function __construct()
    {
        $config = \HTMLPurifier_Config::createDefault();
        $config->set('Cache.SerializerPath', root . '/Private/Cache/HTMLPurifier/');
        //$config->set('HTML.Allowed', 'p,b,a[href],i');
        //$config->set('URI.Base', 'http://www.example.com');
        //$config->set('URI.MakeAbsolute', true);
        $config->set('AutoFormat.AutoParagraph', false);

        // allow use target=_blank for links
        $def = $config->getHTMLDefinition(true);
        $def->addAttribute('a', 'target', 'Enum#_blank,_self,_target,_top');

        $this->purifier = new \HTMLPurifier($config);
    }

    /**
     * Get purifier instance
     * @return \HTMLPurifier
     */
    public function getPurifier()
    {
        return $this->purifier;
    }

    /**
     * Secure html code
     * @param string|array $data
     * @return string
     */
    public function secureHtml($data)
    {
        if (Obj::isArray($data)) {
            return $this->purifier->purifyArray($data);
        }

        return $this->purifier->purify($data);
    }

    /**
     * String html tags and escape quotes
     * @param string|array $html
     * @param boolean $escapeQuotes
     * @return string
     */
    public function strip_tags($html, $escapeQuotes = true)
    {
        // recursive usage
        if (Obj::isArray($html)) {
            foreach ($html as $key=>$value) {
                $html[$key] = $this->strip_tags($value, $escapeQuotes);
            }
            return $html;
        }

        $text = strip_tags($html);
        if ($escapeQuotes) {
            $text = $this->escapeQuotes($text);
        }
        return $text;
    }

    /**
     * Strip php tags and notations in string.
     * @param array|string $data
     * @return array|mixed|string
     */
    public function strip_php_tags($data)
    {
        if (is_array($data)) {
            foreach ($data as $key=>$value) {
                $data[$key] = $this->strip_php_tags($value);
            }
            return $data;
        }
        return addslashes(htmlspecialchars(strip_tags($data)));
    }

    /**
     * Alternative var_export function for php >= 5.4 syntax
     * @deprecated
     * @param $var
     * @param null $indent
     * @return mixed|string
     */
    public function var_export54($var, $indent = null, $guessTypes = false) {
        return Arr::var_export54($var, $indent, $guessTypes);
    }

    /**
     * Escape quotes
     * @param string $html
     * @return string
     */
    public function escapeQuotes($html)
    {
        return Str::replace(['"', "'"], '&quot;', $html);
    }

    /**
     * Crypt password secure with Blow fish crypt algo (defined in salt)
     * Blow fish crypt example: crypt('somedata', '$2a$07$usesomesillystringfor$'), where $2a$07$ - definition of algo,
     * usesomesillystringfor - is salt (must be 21 or more chars), $ - end caret. Output: $2a$07$usesomesillystringfor.sUeCOxyFvckc3xgq1Kzqq90gLrrIVjq
     * @param string $password
     * @param string|null $salt
     * @return string
     */
    public static function password_hash($password, $salt = null)
    {
        if ($salt === null || !Obj::isString($salt) || Str::length($salt) < 1) {
            $salt = App::$Properties->get('passwordSalt');
        }
        return crypt($password, $salt);
    }

    /**
     * Generate simple hash of 8 chars (32bit) for string. This method is NOT SECURE for crypt reason!
     * @param string $string
     * @return string
     */
    public static function simpleHash($string)
    {
        return dechex(crc32($string));
    }


}