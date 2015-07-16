<?php

namespace Ffcms\Core\I18n;

use Ffcms\Core\App;
use Ffcms\Core\Helper\Arr;
use Ffcms\Core\Helper\Directory;
use Ffcms\Core\Helper\File;
use Ffcms\Core\Helper\Normalize;
use Ffcms\Core\Helper\Object;
use Ffcms\Core\Helper\String;
use Ffcms\Core\I18n\Lexer;

class Translate
{

    protected $cached = [];
    protected $indexes = [];

    public function __construct()
    {
        if (App::$Request->getLanguage() !== App::$Property->get('baseLanguage')) {
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
    public function get($index, $text, array $params = null)
    {
        if (App::$Request->getLanguage() !== App::$Property->get('baseLanguage')) {
            if ($index !== null && !Arr::in($index, $this->indexes)) {
                $this->cached = array_merge($this->cached, $this->load($index));
                $this->indexes[] = $index;
            }

            if ($this->cached !== null && Object::isString($text) && $this->cached[$text] !== null) {
                $text = $this->cached[$text];
            }
        }

        if (Object::isArray($params) && count($params) > 0) {
            foreach ($params as $var => $value) {
                $text = String::replace('%' . $var . '%', $value, $text);
            }
        }
        return $text;
    }

    /**
     * Get internalization based on called controller
     * @param string $text
     * @param array $params
     * @return string
     */
    public function translate($text, array $params = [])
    {
        $index = null;
        $namespace = 'Apps\\Controller\\' . env_name . '\\';
        foreach (debug_backtrace() as $caller) {
            if (String::startsWith($namespace, $caller['class'])) {
                $index = String::substr((string)$caller['class'], String::length($namespace));
            }
        }
        return $this->get($index, $text, $params);
    }

    /**
     * Load locale file from local storage
     * @param string $index
     * @return array|null
     */
    protected function load($index)
    {
        $file = root . '/I18n/' . env_name . '/' . App::$Request->getLanguage() . '/' . $index . '.php';
        if (!File::exist($file)) {
            return [];
        }
        return require_once($file);
    }

    /**
     * Append translation data from exist full path
     * @param string $path
     * @return bool
     */
    public function append($path)
    {
        $path = Normalize::diskFullPath($path);
        // check if file exist
        if (!File::exist($path)) {
            return false;
        }

        // load file translations
        $addTranslation = require_once($path);
        if (!Object::isArray($addTranslation)) {
            return false;
        }

        // merge data
        $this->cached = Arr::merge($addTranslation, $this->cached);
        return true;
    }

    /**
     * Get available languages in the filesystem
     * @return array
     */
    public function getAvailableLangs()
    {
        $langs = ['en'];
        $scan = Directory::scan(root . '/I18n/' . env_name . '/', GLOB_ONLYDIR, true);
        foreach ($scan as $row) {
            $langs[] = trim($row, '/');
        }
        return $langs;
    }

    /**
     * Get lexer
     * @return static
     */
    public function lexer()
    {
        return Lexer::instance();
    }
}