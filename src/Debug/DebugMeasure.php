<?php

namespace Ffcms\Core\Debug;

use Ffcms\Core\App;

/**
 * Trait DebugMeasure. Debug tools for ease use
 * @package Ffcms\Core\Debug
 */
trait DebugMeasure
{
    /**
     * Start timeline measure
     * @param string $name
     */
    public function startMeasure(string $name): void
    {
        if (App::$Debug)
            App::$Debug->startMeasure($name);
    }

    /**
     * Stop timeline measure
     * @param string $name
     */
    public function stopMeasure(string $name): void
    {
        if (App::$Debug)
            App::$Debug->stopMeasure($name);
    }
}