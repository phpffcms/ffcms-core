<?php

namespace Ffcms\Core\I18n;

use Ffcms\Core\App;
use Ffcms\Core\Helper\FileSystem\Directory;
use Ffcms\Core\Helper\FileSystem\File;
use Ffcms\Core\Helper\FileSystem\Normalize;
use Ffcms\Core\Helper\Serialize;
use Ffcms\Core\Helper\Type\Any;
use Ffcms\Core\Helper\Type\Arr;
use Ffcms\Core\Helper\Type\Obj;
use Ffcms\Core\Helper\Type\Str;

/**
 * Class Translate. Provide methods to work with internalization data in ffcms.
 * @package Ffcms\Core\I18n
 */
class Translate
{
    protected $cached = [];
    protected $indexes = [];

    /**
     * Translate constructor. Load default translations.
     */
    public function __construct()
    {
        if (App::$Request->getLanguage() !== App::$Properties->get('baseLanguage')) {
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
    public function get(?string $index, string $text, ?array $params = null)
    {
        if (App::$Request->getLanguage() !== App::$Properties->get('baseLanguage')) {
            if ($index && !Arr::in($index, $this->indexes)) {
                $this->cached = Arr::merge($this->cached, $this->load($index));
                $this->indexes[] = $index;
            }

            if ($this->cached && Any::isStr($text) && isset($this->cached[$text])) {
                $text = $this->cached[$text];
            }
        }

        if (Any::isArray($params) && count($params) > 0) {
            foreach ($params as $var => $value) {
                $text = Str::replace('%' . $var . '%', $value, $text);
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
    public function translate(string $text, array $params = null)
    {
        $index = null;
        $namespace = 'Apps\Controller\\' . env_name . '\\';
        foreach (@debug_backtrace() as $caller) {
            if (isset($caller['class']) && Str::startsWith($namespace, $caller['class'])) {
                $index = Str::sub((string)$caller['class'], Str::length($namespace));
            }
        }
        return $this->get($index, $text, $params);
    }

    /**
     * Load locale file from local storage
     * @param string $index
     * @return array|null
     */
    protected function load(string $index): ?array
    {
        $file = root . '/I18n/' . env_name . '/' . App::$Request->getLanguage() . '/' . $index . '.php';
        if (!File::exist($file)) {
            return [];
        }

        return require($file);
    }

    /**
     * Append translation data from exist full path
     * @param string $path
     * @return bool
     */
    public function append($path): bool
    {
        $path = Normalize::diskFullPath($path);
        // check if file exist
        if (!File::exist($path)) {
            return false;
        }

        // load file translations
        $addTranslation = require($path);
        if (!Any::isArray($addTranslation)) {
            return false;
        }

        // merge data
        $this->cached = Arr::merge($this->cached, $addTranslation);
        return true;
    }

    /**
     * Get available languages in the filesystem
     * @return array
     */
    public function getAvailableLangs(): array
    {
        $langs = ['en'];
        $scan = Directory::scan(root . '/I18n/' . env_name . '/', GLOB_ONLYDIR, true);
        foreach ($scan as $row) {
            $langs[] = trim($row, '/');
        }
        return $langs;
    }

    /**
     * Get locale data from input array or serialized string
     * @param array|string $input
     * @param string|null $lang
     * @param string|null $default
     * @return string|null
     */
    public function getLocaleText($input, ?string $lang = null, ?string $default = null): ?string
    {
        // define language if empty
        if ($lang === null) {
            $lang = App::$Request->getLanguage();
        }
        // unserialize from string to array
        if (Any::isStr($input)) {
            $input = Serialize::decode($input);
        }

        if (Any::isArray($input) && array_key_exists($lang, $input)) {
            return $input[$lang];
        }

        return $default;
    }
}
