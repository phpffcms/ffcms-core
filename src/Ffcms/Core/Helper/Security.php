<?php

namespace Ffcms\Core\Helper;

use Ffcms\Core\App;
use Ffcms\Core\Helper\Type\Object;
use Ffcms\Core\Helper\Type\Str;

class Security
{

    protected $purifier;


    public function __construct()
    {
        $config = \HTMLPurifier_Config::createDefault();
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
        if (Object::isArray($data)) {
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
        if (Object::isArray($html)) {
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
     * @param $var
     * @param null $indent
     * @return mixed|string
     */
    public function var_export54($var, $indent = null, $guessTypes = false) {
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
                $r = [];
                foreach ($var as $key => $value) {
                    $r[] = $indent . "\t"
                        . ($indexed ? null : $this->var_export54($key, null, $guessTypes) . ' => ')
                        . $this->var_export54($value, $indent . "\t", $guessTypes);
                }
                return "[\n" . implode(",\n", $r) . "\n" . $indent . ']';
            case 'boolean':
                return $var ? 'true' : 'false';
            default:
                return var_export($var, TRUE);
        }
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
     * @param string $password
     * @return string
     */
    public static function password_hash($password)
    {
        return crypt($password, App::$Properties->get('passwordSalt'));
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