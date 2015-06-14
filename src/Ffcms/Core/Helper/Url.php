<?php

namespace Ffcms\Core\Helper;

use Ffcms\Core\App;
use Ffcms\Core\Helper\HTML\NativeGenerator;

class Url extends NativeGenerator
{

    /**
     * Build link via controller/action and other params
     * @param string $controller_action
     * @param string|null $id
     * @param string|null $add
     * @param array $params
     * @return null|string
     */
    public static function to($controller_action, $id = null, $add = null, array $params = null)
    {
        $pathway = self::buildPathway([$controller_action, $id, $add, $params]);
        return App::$Alias->baseUrl . '/' . $pathway;
    }

    /**
     * Build pathway from array $to. Example: ['controller/action', 'id', 'add', ['get' => 'value']]
     * @param array $to
     * @return string|null
     */
    public static function buildPathway(array $to)
    {
        $response = trim(String::lowerCase($to[0]), '/'); // controller/action

        list($controller, $action) = explode('/', $response); // check is it correct
        if ($controller == null || $action == null) {
            return null;
        }

        if ($to[1] != null) { // id is not null?
            $response .= '/' . urlencode(self::nohtml(String::lowerCase($to[1])));
        }

        if ($to[2] != null) { // add is not null?
            $response .= '/' . urlencode(self::nohtml(String::lowerCase($to[2])));
        }

        if (Object::isArray($to[3]) && count($to[3]) > 0) { // get params is defined?
            $first = true;
            foreach ($to[3] as $key=>$value) {
                $response .= $first ? '?' : '&';
                $response .= urlencode($key) . '=' . urlencode($value);
                $first = false;
            }
        }

        return $response;
    }

    /**
     * Build current pathway with get data to compare in some methods
     * @return null|string
     */
    public static function buildPathwayFromRequest()
    {
        return self::buildPathway([
            App::$Request->getController() . '/' . App::$Request->getAction(),
            App::$Request->getID(),
            App::$Request->getAdd(),
            App::$Request->query->all()
        ]);
    }

    /**
     * Create <a></a> block link
     * @param string|array $to
     * @param string $name
     * @param array $property
     * @return string
     */
    public static function link($to, $name, $property = [])
    {
        $compile_property = self::applyProperty($property);

        if (!Object::isArray($to)) { // callback magic (:
            $to = [$to];
        }
        // call Url::to(args)
        $callbackTo = call_user_func_array([__NAMESPACE__ . '\Url', 'to'], $to);

        return '<a href="' . $callbackTo . '"' . $compile_property . '>' . $name . '</a>';
    }

    public static function getRemoteContent($url)
    {
        // check is valid url
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        $content = null;
        if(function_exists('curl_version')) {
            $curl = \curl_init();
            $userAgent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)';
            \curl_setopt($curl,CURLOPT_URL, $url);
            \curl_setopt($curl,CURLOPT_RETURNTRANSFER, TRUE);
            \curl_setopt($curl,CURLOPT_CONNECTTIMEOUT, 5);
            \curl_setopt($curl, CURLOPT_HEADER, 0);
            \curl_setopt($curl, CURLOPT_USERAGENT, $userAgent);
            \curl_setopt($curl, CURLOPT_FAILONERROR, TRUE);
            \curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
            \curl_setopt($curl, CURLOPT_AUTOREFERER, TRUE);
            \curl_setopt($curl, CURLOPT_TIMEOUT, 10);
            $content = \curl_exec($curl);
            \curl_close($curl);
        } else {
            $content = @file_get_contents($url);
        }
        return $content;
    }

}