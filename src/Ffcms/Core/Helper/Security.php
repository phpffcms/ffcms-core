<?php

namespace Ffcms\Core\Helper;

use Ffcms\Core\App;

class Security {

    protected $purifier;


    public function __construct()
    {
        $config = \HTMLPurifier_Config::createDefault();
        //$config->set('HTML.Allowed', 'p,b,a[href],i');
        //$config->set('URI.Base', 'http://www.example.com');
        //$config->set('URI.MakeAbsolute', true);
        $config->set('AutoFormat.AutoParagraph', false);

        $this->purifier = new \HTMLPurifier($config);
    }

    /**
     * @return \HTMLPurifier
     */
    public function purifier()
    {
        return $this->purifier;
    }

    /**
     * String html tags and escape quotes
     * @param string $html
     * @param boolean $escapeQuotes
     * @return string
     */
    public function strip_tags($html, $escapeQuotes = true)
    {
        $text = strip_tags($html);
        if($escapeQuotes) {
            $text = $this->escapeQuotes($text);
        }
        return $text; // x10 faster
        /**$cfg = \HTMLPurifier_Config::createDefault();
        $cfg->set('HTML.Allowed', '');
        return $this->purifier()->purify($html, $cfg); */
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
        return crypt($password, App::$Property->get('password_salt'));
    }


}