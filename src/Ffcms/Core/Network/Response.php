<?php

namespace Ffcms\Core\Network;


class Response {


    protected $globalVars;

    /**
     * Set application response header. Default - html
     * @param string $type ['html', 'js', 'json']
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
}