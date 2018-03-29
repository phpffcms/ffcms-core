<?php

namespace Ffcms\Core\Arch;

use Ffcms\Core\App;
use Ffcms\Core\Helper\FileSystem\Directory;
use Ffcms\Core\Helper\Type\Str;
use Ffcms\Templex\Engine;

/**
 * Class View. Classic realisation of view's management in MVC architecture.
 * This class can be uses as object - (new View())->render() or from entry point App::$View->render() or
 * from controller action like $this->view->render()
 * @package Ffcms\Core\Arch
 * @property string $title
 * @property string $description
 * @property string $keywords
 * @property array $breadcrumbs
 */
class View extends Engine
{
    public $lang = 'en';

    private $path;

    /**
     * View constructor. Initialize template engine
     */
    public function __construct()
    {
        $this->lang = App::$Request->getLanguage();
        // get theme from config based on env_name global
        $theme = App::$Properties->get('theme')[env_name] ?? 'default';
        $env = ucfirst(Str::lowerCase(env_name));

        $this->path = root . DIRECTORY_SEPARATOR . 'Apps' . DIRECTORY_SEPARATOR . 'View' . DIRECTORY_SEPARATOR . $env . DIRECTORY_SEPARATOR . $theme;
        if (!Directory::exist($this->path)) {
            return;
        }

        // initialize template engine with path and load default extensions
        parent::__construct($this->path);
        $this->loadDefaultExtensions();
    }

    /**
     * Get current template absolute path
     * @return string|null
     */
    public function getCurrentPath(): ?string
    {
        return $this->path;
    }
}
