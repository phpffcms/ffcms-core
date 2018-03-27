<?php

namespace Ffcms\Core\Interfaces;

/**
 * Interface iModel. Default model interface
 * @package Ffcms\Core\Interfaces
 */
interface iModel
{
    public function before();

    public function rules(): array;
    public function sources(): array;
    public function types(): array;
}
