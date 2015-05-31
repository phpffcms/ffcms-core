<?php

namespace Ffcms\Core\Helper;

use Ffcms\Core\App;

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
     * @param string $data
     * @return string
     */
    public function secureHtml($data)
    {
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

    public function escapeQuotes($html)
    {
        return String::replace(['"', "'"], '&quot;', $html);
    }

    /**
     * Crypt password secure with Blow fish crypt algo (defined in salt)
     * @param string $password
     * @return string
     */
    public static function password_hash($password)
    {
        return crypt($password, App::$Property->get('passwordSalt'));
    }


}