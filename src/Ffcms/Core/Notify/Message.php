<?php

namespace Ffcms\Core\Notify;

use Core\App;

class Message {
    protected $data;

    /**
     * Set message for notify or any other futures
     * @param string $group
     * @param string $type
     * @param string $text
     */
    public function set($group, $type, $text)
    {
        $this->data[$group][] = [
            'type' => $type,
            'text' => App::$Security->strip_tags($text)
        ];
    }

    /**
     * Get messages for group
     * @param string $group
     * @return array|null
     */
    public function getGroup($group)
    {
        return $this->data[$group];
    }


}