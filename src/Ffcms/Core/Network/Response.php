<?php

namespace Ffcms\Core\Network;

use \Core\App;


class Response {


    protected $globalVars;

    /**
     * Set application response header. Default - html
     * @param string $type ['html', 'js', 'json', 404]
     */
    public function setHeader($type = 'html')
    {
        switch($type) {
            case 'json':
                header('Content-Type: application/json');
                break;
            case 'js':
                header("Content-Type: text/javascript");
                break;
            case 404:
                header("HTTP/1.0 404 Not Found");
                header("HTTP/1.1 404 Not Found");
                header("Status: 404 Not Found");
                break;
            default:
                header("Content-Type: text/html");
                break;
        }
    }

    /**
     * Set global variable for Views
     * @param string $var
     * @param string $value
     */
    public function setGlobal($var, $value)
    {
        $this->globalVars[$var] = $value;
    }

    /**
     * Set global variable from key=>value array (key = varname)
     * @param array $array
     */
    public function setGlobalArray($array)
    {
        if(!is_array($array))
            return;
        foreach($array as $var => $value)
        {
            $this->globalVars[$var] = $value;
        }
    }

    /**
     * Get all global variables
     * @return array|null
     */
    public function getGlobal()
    {
        return $this->globalVars;
    }

    /**
     * Build global variables like stdClass object $obj->var = value
     * @return \stdClass
     */
    public function buildGlobal()
    {
        $global = new \stdClass();
        foreach($this->getGlobal() as $var => $value) {
            $global->{$var} = $value;
        }
        return $global;
    }

    /**
     * Simple function to set user cookie. If time is nil - only for this session. If httponly is false - allowed using by javascript.
     * @param string $data
     * @param string $value
     * @param int $time
     * @param boolean $httponly
     */
    public function setCookie($data, $value, $time = null, $httponly = false) {
        setcookie($data, $value, $time, '/', null, null, $httponly);
    }
}