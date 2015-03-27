<?php

namespace Core\Exception;

class SystemException {

    function __construct($message = null) {
        exit("Critical error founded: " . $message);
    }
}