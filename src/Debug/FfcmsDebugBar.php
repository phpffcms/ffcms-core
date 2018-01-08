<?php
namespace Ffcms\Core\Debug;

use DebugBar\DataCollector\ExceptionsCollector;
use DebugBar\DataCollector\MemoryCollector;
use DebugBar\DataCollector\MessagesCollector;
use DebugBar\DataCollector\PhpInfoCollector;
use DebugBar\DataCollector\RequestDataCollector;
use DebugBar\DataCollector\TimeDataCollector;
use DebugBar\DebugBar;
use Ffcms\Core\Debug\Collectors\FfcmsInfoCollector;

/**
 * Class FfcmsDebugBar. Extend default class and configure some ffcms features ;)
 * @package Ffcms\Core\Debug
 */
class FfcmsDebugBar extends DebugBar
{
    public function __construct()
    {
        try {
            $this->addCollector(new PhpInfoCollector());
            $this->addCollector(new FfcmsInfoCollector());
            $this->addCollector(new MessagesCollector());
            $this->addCollector(new RequestDataCollector());
            $this->addCollector(new TimeDataCollector());
            $this->addCollector(new MemoryCollector());
            $this->addCollector(new ExceptionsCollector());
        } catch (\Exception $e) {
        } // mute
    }
}
