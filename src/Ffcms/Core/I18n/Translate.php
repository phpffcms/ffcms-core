<?php

namespace Ffcms\Core\I18n;

use Core\App;
use Core\Helper\String;

class Translate {

    protected $cached = [];
    protected $indexes = [];

    protected $makeTranslate = false;

    public function __construct()
    {
        if(App::$Request->getLanguage() != App::$Property->get('baseLanguage')) {
            $this->cached = $this->load('Default');
            $this->indexes[] = 'Default';
        }
    }


    /**
     * Get internalization of current text from i18n
     * @param string $index
     * @param string $text
     * @param array|null $params
     * @return string
     */
    public function get($index, $text, $params = [])
    {
        if(App::$Request->getLanguage() != App::$Property->get('baseLanguage')) {
            if($index !== null && !in_array($index, $this->indexes)) {
                $this->cached = array_merge($this->cached, $this->load($index));
                $this->indexes[] = $index;
            }
            if(!empty($this->cached[$text]))
                $text = $this->cached[$text];
        }

        if(sizeof($params) > 0) {
            foreach($params as $var => $value) {
                $text = String::replace('%' . $var . '%', $value, $text);
            }
        }
        return $text;
    }

    /**
     * Get internalization based on called controller
     * @param string $text
     * @param array|null $params
     * @return string
     */
    public function translate($text, $params = [])
    {
        $index = null;
        $namespace = 'Controller\\' . workground . '\\';
        foreach(debug_backtrace() as $caller)
        {
            if(String::startsWith($namespace, $caller['class']))
                $index = String::substr((string)$caller['class'], String::length($namespace));
        }
        return $this->get($index, $text, $params);
    }

    protected function load($index)
    {
        $file = root . '/I18n/' . workground . '/' . App::$Request->getLanguage() . '/' . $index . '.php';
        if(!file_exists($file) || !is_readable($file))
            return [];
        return require_once($file);
    }
}