<?php

namespace Ffcms\Core\Notify;

use Ffcms\Core\App;
use Ffcms\Core\Helper\Object;

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
     * @param string|array $group
     * @return array|null
     */
    public function getGroup($group)
    {
        $output = null;
        if (Object::isArray($group)) {
            foreach($group as $row) {
                if (Object::isArray($this->data[$row])) {
                    if ($output === null) {
                        $output = $this->data[$row];
                    } else {
                        $output = array_merge($output, $this->data[$row]);
                    }
                }
            }
            return $output;
        }
        return $this->data[$group];
    }


}