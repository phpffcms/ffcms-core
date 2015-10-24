<?php

namespace Ffcms\Core\Cache;

use Ffcms\Core\Traits\Singleton;

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
     * @return mixed
     */
    public function get($key)
    {
        return isset($this->data[$key]) ? $this->data[$key] : null;
    }
}