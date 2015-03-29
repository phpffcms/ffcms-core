<?php

namespace Ffcms\Core\Helper;

use \Core\App;

class Security {

    protected $purifier;


    function __construct()
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
     * Crypt password secure with Blow fish crypt algo (defined in salt)
     * @param string $password
     * @return string
     */
    public function password_hash($password)
    {
        return crypt($password, App::$Property->get('password_salt'));
    }


}