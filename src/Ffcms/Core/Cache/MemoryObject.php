<?php

namespace Ffcms\Core\Cache;

use Ffcms\Core\Traits\Singleton;

/**
 * Class MemoryObject. Simple singleton-type of magic __set and __get class to store any objects in memory (get and set)
 * @package Ffcms\Core\Cache
 */
class MemoryObject
{
    use Singleton;

    protected $data;

    /**
     * Set data to store
     * @param string $key
     * @param mixed $value
     */
    public function set($key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * Get stored data
     * @param string $key
     * @return object|array|string|null|boolean
     */
    public function get($key)
    {
        return isset($this->data[$key]) ? $this->data[$key] : null;
    }
}