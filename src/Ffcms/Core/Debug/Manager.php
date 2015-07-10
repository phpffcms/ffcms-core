<?php

namespace Ffcms\Core\Debug;

use \DebugBar\StandardDebugBar;
use \DebugBar\DataCollector\ConfigCollector;
use \Ffcms\Core\App;
use Ffcms\Core\Helper\Object;

/**
 * Class Debug - display information of debug and collected data in debug bar
 * @package Ffcms\Core
 */
class Manager
{

    public $bar;
    public $render;

    public function __construct()
    {
        $this->bar = new StandardDebugBar();
        $this->render = $this->bar->getJavascriptRenderer();

        $this->bar->addCollector(new ConfigCollector());
    }

    /**
     * Render debug bar header
     * @return string
     */
    public function renderHead()
    {
        return $this->render->renderHead();
    }

    /**
     * Render debug bar code
     * @return string
     * @throws \DebugBar\DebugBarException
     */
    public function renderOut()
    {
        if (!$this->bar->hasCollector('queries')) {
            $timeCollector = null;
            $log = App::$Database->connection()->getQueryLog();
            if ($this->bar->hasCollector('time')) {
                $timeCollector = $this->bar->getCollector('time');
            }
            $queryCollector = new LaravelDatabaseCollector($timeCollector, $log);
            $this->bar->addCollector($queryCollector);
        }
        return $this->render->render();
    }

    /**
     * Add exception into debug bar and stop execute
     * @param $e
     * @throws \DebugBar\DebugBarException
     */
    public function addException($e)
    {
        if ($e instanceof \Exception) {
            $this->bar->getCollector('exceptions')->addException($e);
        }
    }

    /**
     * Add message into debug bar
     * @param string $m
     * @param string $type
     * @throws \DebugBar\DebugBarException
     */
    public function addMessage($m, $type = 'info')
    {
        if (!Object::isString($m) || !Object::isString($type)) {
            return;
        }
        $m = App::$Security->secureHtml($m);
        $mCollector = $this->bar->getCollector('messages');

        if (method_exists($mCollector, $type)) {
            $this->bar->getCollector('messages')->{$type}($m);
        }
    }

    /**
     * Add message debug data to bar
     * @param $data
     * @throws \DebugBar\DebugBarException
     */
    public function vardump($data)
    {
        $this->bar->getCollector('messages')->info($data);
    }

    /**
     * Check if debug bar is enabled. Method called before __construct() is initiated!!
     * @return bool
     */
    public static function isEnabled()
    {
        $property = App::$Property->get('debug');
        return (true === $property['all'] ||
            App::$Request->cookies->get($property['cookie']['key']) === $property['cookie']['value']);
    }
}