<?php

namespace Ffcms\Core\Managers;


use Ffcms\Core\App;
use Ffcms\Core\Helper\Type\Any;
use Ffcms\Core\Helper\Type\Obj;

/**
 * Class CronManager. Class to control cron manager on website. Register and run tasks over time.
 * @package Ffcms\Core\Managers
 */
class CronManager
{
    /** @var array $configs */
    private $configs = [];

    /**
     * CronManager constructor. Initialize configurations.
     */
    public function __construct()
    {
        /** @var array $configs */
        $configs = App::$Properties->getAll('Cron');
        if (Any::isArray($configs))
            $this->configs = $configs;
    }

    /**
     * Run cron task. Attention - this method is too 'fat' to run from any app's
     * @return array|null
     */
    public function run()
    {
        // check if cron instances is defined
        if (!isset($this->configs['instances']) || !Any::isArray($this->configs['instances']))
            return null;

        // get timestamp
        $time = time();
        $log = [];

        // each every one instance
        foreach ($this->configs['instances'] as $callback => $delay) {
            if (((int)$this->configs['log'][$callback] + $delay) <= $time) {
                // prepare cron initializing
                list($class, $method) = explode('::', $callback);
                if (class_exists($class) && method_exists($class, $method)) {
                    // make static callback
                    forward_static_call([$class, $method]);
                    $log[] = $callback;
                }
                // update log information
                $this->configs['log'][$callback] = $time + $delay;
            }
        }

        // write updated configs
        App::$Properties->writeConfig('Cron', $this->configs);
        return $log;
    }

    /**
     * Register cron action callback to $class::$method() every $delay seconds
     * @param string $class
     * @param string $method
     * @param int $delay
     * @return bool
     */
    public function register($class, $method, $delay = 60)
    {
        // check if declared callback is exist over autoload
        if (!class_exists($class) || !method_exists($class, $method))
            return false;

        $callback = (string)$class . '::' . (string)$method;

        // add instance to cron task manager
        if (!isset($this->configs['instances'][$callback])) {
            $this->configs['instances'][$callback] = $delay;
            App::$Properties->writeConfig('Cron', $this->configs);
        }

        return true;
    }

    /**
     * Remove registered cron task from configs
     * @param string $class
     * @param string $method
     */
    public function remove($class, $method)
    {
        $callback = $class . '::' . $method;
        if (isset($this->configs['instances'][$callback])) {
            unset($this->configs['instances'][$callback], $this->configs['log'][$callback]);
            App::$Properties->writeConfig('Cron', $this->configs);
        }
    }
}