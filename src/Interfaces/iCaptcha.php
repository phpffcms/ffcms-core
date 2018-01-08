<?php

namespace Ffcms\Core\Interfaces;

interface iCaptcha
{

    /**
     * Check is captcha provide 'full-based' output realisation
     * @return bool
     */
    public function isFull();

    /**
     * Get captcha image link(isFull():=false) or builded JS code(isFull():=true)
     * @return string
     */
    public function get();

    /**
     * Validate input data from captcha
     * @param string|null $data
     * @return bool
     */
    public static function validate($data = null);
}
