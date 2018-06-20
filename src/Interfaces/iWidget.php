<?php

namespace Ffcms\Core\Interfaces;

interface iWidget
{
    /**
     * Initialize widget
     * @param array|null $params
     * @return string|null
     */
    public static function widget(array $params = null): ?string;

    /**
     * Render widget
     * @return string|null
     */
    public function display(): ?string;

    /**
     * Special method on "before" run widget
     * @return void
     */
    public function init(): void;
}
