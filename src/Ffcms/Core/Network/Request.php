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

    /**
     * Get current controller name
     * @return string
     */
    public function getController()
    {
        return ucfirst(strtolower(self::$controller));
    }

    /**
     * Get current controller action() name
     * @return string
     */
    public function getAction()
    {
        return ucfirst(strtolower(self::$action));
    }

    /**
     * Get current $id argument for controller action
     * @return string
     */
    public function getID()
    {
        return strtolower(self::$id);
    }

    /**
     * Get current $add argument for controller action
     * @return string
     */
    public function getAdd()
    {
        return strtolower(self::$add);
    }

    /**
     * Get data from global $_POST with $key. Like $_POST[$key]
     * @param string|null $key
     * @return string|null
     */
    public function post($key = null)
    {
        return $key === null ? $_POST : $_POST[$key];
    }
    /**
     * Get data from global $_GET with $key according urldecode(). Like urldecode($_GET[$key])
     * @param string $key
     * @return string|null
     */
    public function get($key = null)
    {
        return $key === null ? $_GET : urldecode($_GET[$key]);
    }
}