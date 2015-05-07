<?php

namespace Ffcms\Core\Network;

use Ffcms\Core\App;
use Ffcms\Core\Helper\Url;


class Response {


    protected $globalVars = [];

    public $errorString;

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
                header('Content-Type: text/javascript');
                break;
            case 404:
                header('HTTP/1.1 404 Not Found');
                break;
            default:
                header('Content-Type: text/html');
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
        if (!is_array($array)) {
            return;
        }
        foreach ($array as $var => $value)
        {
            $this->globalVars[$var] = $value;
        }
    }

    /**
     * Get all global variables
     * @return array|null
     */
    public function getGlobals()
    {
        return $this->globalVars;
    }

    /**
     * Build global variables like stdClass object $obj->var = value
     * @return \stdClass
     */
    public function buildGlobal()
    {
        // does it empty global variables?
        if(count($this->globalVars) < 1) {
            return new \stdClass();
        }

        $global = new \stdClass();
        foreach($this->globalVars as $var => $value) {
            $global->$var = App::$Security->strip_tags($value);
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
    public function setCookie($data, $value, $time = null, $httponly = false)
    {
        setcookie($data, $value, $time, '/', null, null, $httponly);
    }

    /**
     * @param string $controller_action
     * @param null|mixed $id
     * @param null|mixed $add
     * @param array $params
     */
    public function redirect($controller_action, $id = null, $add = null, $params = [])
    {
        $link = Url::to($controller_action, $id, $add, $params);
        header('Location: ' . $link);
        exit();
    }

    /**
     * Set header with redirect for user. Only standalone usage.
     * @param $toUri
     * @param boolean $full
     */
    public static function aloneRedirect($toUri, $full = false)
    {
        $toUri = ltrim($toUri, '/');
        header('Location: ' . ($full === false ? '/' : '') . $toUri);
        exit();
    }
}