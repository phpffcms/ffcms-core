<?php

namespace Ffcms\Core\Exception;

class RequestException {

    function __construct($message = null) {
        exit($message);
    }
}