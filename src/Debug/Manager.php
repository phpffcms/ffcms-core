<?php

namespace Ffcms\Core\Debug;

use DebugBar\DataCollector\ConfigCollector;
use Ffcms\Core\App;
use Ffcms\Core\Helper\Type\Any;

/**
 * Class Debug. Provide methods of display information about debug and collected data in debug bar
 * @package Ffcms\Core
 */
class Manager
{
    public $bar;
    public $render;

    /**
     * Manager constructor. Construct debug manager - build debug bar, javascripts and initialize config
     */
    public function __construct()
    {
        $this->bar = new FfcmsDebugBar();
        $this->render = $this->bar->getJavascriptRenderer();
        try {
            $this->bar->addCollector(new ConfigCollector());
        } catch (\Exception $oe) {
        }
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

            // @todo: fix me! query collector is broken!
            $queryCollector = new LaravelDatabaseCollector($timeCollector, $log);
            $this->bar->addCollector($queryCollector);
        }
        return $this->render->render();
    }

    /**
     * Add exception into debug bar and stop execute
     * @param \Exception $e
     */
    public function addException($e)
    {
        if ($e instanceof \Exception) {
            try {
                $this->bar->getCollector('exceptions')->addException($e);
            } catch (\Exception $ie) {
            } // mute exceptions there
        }
    }

    /**
     * Add message into debug bar
     * @param string $m
     * @param string $type
     */
    public function addMessage($m, $type = 'info')
    {
        if (!Any::isStr($m) || !Any::isStr($type)) {
            return;
        }

        $m = App::$Security->secureHtml($m);
        try {
            $mCollector = $this->bar->getCollector('messages');

            if (method_exists($mCollector, $type)) {
                $this->bar->getCollector('messages')->{$type}($m);
            }
        } catch (\Exception $e) {
        } // mute exceptions there
    }

    /**
     * Add message debug data to bar
     * @param mixed $data
     */
    public function vardump($data)
    {
        try {
            $this->bar->getCollector('messages')->info($data);
        } catch (\Exception $e) {
        }
    }

    /**
     * Start timeline measure
     * @param string $key
     */
    public function startMeasure(string $key): void
    {
        $this->bar['time']->startMeasure($key);
    }

    /**
     * Stop timeline measure
     * @param string $key
     */
    public function stopMeasure(string $key): void
    {
        $this->bar['time']->stopMeasure($key);
    }

    /**
     * Check if debug bar is enabled. Method called before __construct() is initiated!!
     * @return bool
     */
    public static function isEnabled()
    {
        $property = App::$Properties->get('debug');
        // $_COOKIE used insted of symfony request, cuz debug initialize early
        return ($property['all'] === true || $_COOKIE[$property['cookie']['key']] === $property['cookie']['value']);
    }
}
