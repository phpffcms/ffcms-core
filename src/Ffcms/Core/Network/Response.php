<?php

namespace Ffcms\Core\Network;

use Ffcms\Core\App;
use Ffcms\Core\Helper\Type\Str;
use Symfony\Component\HttpFoundation\Response as FoundationResponse;
use Symfony\Component\HttpFoundation\RedirectResponse as FoundationRedirect;

class Response extends FoundationResponse
{
    /**
     * Fast redirect in web environment
     * @param string $to
     * @param bool $full
     */
    public function redirect($to, $full = false)
    {
        $to = trim($to, '/');
        if (false === $full && !Str::startsWith(App::$Alias->baseUrl, $to)) {
            $to = App::$Alias->baseUrl . '/' . $to;
        }
        $redirect = new FoundationRedirect($to);
        $redirect->send();
        exit('Redirecting to ' . $to . ' ...');
    }
}