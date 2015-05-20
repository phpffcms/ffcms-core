<?php

namespace Ffcms\Core\Debug;

use \DebugBar\StandardDebugBar;
use \DebugBar\DataCollector\ConfigCollector;
use \Ffcms\Core\App;

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
            $this->addMessage('Database profiling now is started!');
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
     * Add exception into debug bar
     * @param $e
     * @throws \DebugBar\DebugBarException
     */
    public function addException($e)
    {
        $this->bar->getCollector('exceptions')->addException($e);
    }

    /**
     * Add message into debug bar
     * @param string $m
     * @param string $type
     * @throws \DebugBar\DebugBarException
     */
    public function addMessage($m, $type = 'info')
    {
        $mCollector = $this->bar->getCollector('messages');

        if (method_exists($mCollector, $type)) {
            $this->bar->getCollector('messages')->{$type}($m);
        }
    }

    /**
     * Check if debug bar is enabled
     * @return bool
     */
    public static function isEnabled()
    {
        $debugProperty = App::$Property->get('debug');
        return ($debugProperty['all'] === true || ($debugProperty['owner'] === true && App::$User->isAuth()));
    }
}