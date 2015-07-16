<?php

namespace Ffcms\Core\Interfaces;


interface iWidget
{
    /**
     * Initialize widget
     * @param array|null $params
     * @return string|null
     */
    public static function widget(array $params = null);

    /**
     * Render widget
     * @return string|null
     */
    public function display();

    /**
     * Init widget
     */
    public function init();
}