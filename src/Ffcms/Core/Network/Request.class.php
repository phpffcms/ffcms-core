<?php

namespace Ffcms\Core\Network;


/**
 * Class Request
 * Class to work with input website data - request params and headers.
 * @package Core\Network
 */
class Request {

    protected static $pathway;
    protected static $controller;
    protected static $action;
    protected static $id;
    protected static $add;

    function __construct()
    {
        // preparing url
        $raw_uri = urldecode($_SERVER['REQUEST_URI']);
        if($get_pos = strpos($raw_uri, '?'))
            $raw_uri = substr($raw_uri, 0, $get_pos);
        self::$pathway = ltrim($raw_uri, '/');
        $uri_split = explode('/', self::$pathway);

        // write mvc data
        self::$controller = $uri_split[0];
        self::$action = $uri_split[1];
        self::$id = $uri_split[2];
        self::$add = $uri_split[3];

        // set defaults
        if(self::$controller == null)
            self::$controller = 'main';

        if(self::$action == null)
            self::$action = 'index';
    }

    public function getController()
    {
        return ucfirst(strtolower(self::$controller));
    }

    public function getAction()
    {
        return ucfirst(strtolower(self::$action));
    }

    public function getID()
    {
        return strtolower(self::$id);
    }

    public function getAdd()
    {
        return strtolower(self::$add);
    }
}