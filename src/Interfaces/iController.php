<?php

namespace Ffcms\Core\Interfaces;

/**
 * Interface iController. Default controller hint interface.
 * @package Ffcms\Core\Interfaces
 */
interface iController
{
    public function before();
    public function after();
    public static function boot(): void;
}
