<?php
namespace Ffcms\Core\Managers;

use Ffcms\Core\App;
use Ffcms\Core\Helper\Type\Any;
use Ffcms\Core\Helper\Type\Obj;

/**
 * Class EventManager. Control and run events.
 * @package \Ffcms\Core\Event
 * @author zenn
 */
class EventManager
{
    /** @var array $events */
    private $events;
    private $runned;

    /**
     * EventManager constructor. Get always initiated data from memory storage.
     */
    public function __construct()
    {
        // get events from memory object saver
        $this->events = App::$Memory->get('events.catched.save');
        $this->runned = App::$Memory->get('events.runned.save');
    }

    /** Catch the event if it occurred after this initiation of interception
     * @param string|array $event
     * @param \Closure $callback
     */
    public function on($event, \Closure $callback): void
    {
        // check if event is a single string and parse it to array single item
        if (!Any::isArray($event)) {
            $event = [$event];
        }
        
        foreach ($event as $item) {
            $this->events[$item][] = $callback;
        }
    }

    /**
     * Catch the event if it occurred before the initiation of interception
     * @param string|array $event
     * @param \Closure $callback
     * @return mixed
     */
    public function listen($event, \Closure $callback)
    {
        // check if $event is a single string and set it as array with one item
        if (!Any::isArray($event)) {
            $event = [$event];
        }
        
        // each every one event in array
        foreach ($event as $item) {
            if (Any::isArray($this->runned) && array_key_exists($item, $this->runned)) {
                return call_user_func_array($callback, $this->runned[$item]);
            }
        }
        
        return false;
    }
    
    /**
     * Initialize event on happend
     * @return mixed
     */
    public function run()
    {
        // dynamicly parse input params
        $args = func_get_args();
        
        if (count($args) < 1) {
            return false;
        }
        
        // get event name
        $eventName = array_shift($args);
        // get event args as array if passed
        $eventArgs = @array_shift($args);
        
        // if event is registered
        if (isset($this->events[$eventName]) && Any::isArray($this->events[$eventName])) {
            foreach ($this->events[$eventName] as $callback) {
                // call anonymous function with args if passed
                return call_user_func_array($callback, $eventArgs);
            }
        }
        
        // set to post runned actions
        $this->runned[$eventName] = $eventArgs;
        return false;
    }

    /**
     * Save events data in memory to prevent any sh@ts ;D
     */
    public function __destruct()
    {
        App::$Memory->set('events.catched.save', $this->events);
        App::$Memory->set('events.runned.save', $this->runned);
    }
}
