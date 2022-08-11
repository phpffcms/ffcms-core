<?php

namespace Ffcms\Core\Network;

use Ffcms\Core\App;
use Ffcms\Core\Helper\Type\Str;
use Symfony\Component\HttpFoundation\RedirectResponse as FoundationRedirect;
use Symfony\Component\HttpFoundation\Response as FoundationResponse;

/**
 * Class Response. Basic implementation of httpfoundation.response class.
 * @package Ffcms\Core\Network
 */
class Response extends FoundationResponse
{
    /**
     * Fast redirect in web environment
     * @param string $to
     * @param int $code
     * @param bool $full
     */
    public function redirect($to, $full = false, $code = 302)
    {
        $to = trim($to, '/');
        if (!$full && !Str::startsWith(App::$Alias->baseUrl, $to)) {
            $to = App::$Alias->baseUrl . '/' . $to;
        }

        $redirect = new FoundationRedirect($to, $code);
        $redirect->send();
        exit('Redirecting to ' . $to . ' ...');
    }
}
