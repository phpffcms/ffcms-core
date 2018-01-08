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
     * Special method on "before" run widget
     */
    public function init();
}
