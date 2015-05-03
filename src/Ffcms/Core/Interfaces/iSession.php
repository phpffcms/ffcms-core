<?php

namespace Ffcms\Core\Interfaces;

interface iSession extends \SessionHandlerInterface
{
    /**
     * Initialize session data
     */
    public function start();

    /**
     * Hook session handler function
     */
    public function registerHandler();

    /**
     * Check is current session always open'd
     * @return bool
     */
    public function isOpen();
}