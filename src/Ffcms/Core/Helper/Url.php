<?php

namespace Ffcms\Core\Helper;

use Ffcms\Core\App;
use Ffcms\Core\Helper\HTML\NativeGenerator;
use Ffcms\Core\Helper\Type\Arr;
use Ffcms\Core\Helper\Type\Obj;
use Ffcms\Core\Helper\Type\Str;

class Url extends NativeGenerator
{

    /**
     * Build link via controller/action and other params
     * @param string $controller_action
     * @param string|null $id
     * @param string|null $add
     * @param array $params
     * @param bool $encode
     * @return null|string
     */
    public static function to($controller_action, $id = null, $add = null, array $params = null, $encode = true)
    {
        $pathway = self::buildPathway([$controller_action, $id, $add, $params], $encode);
        return App::$Alias->baseUrl . '/' . $pathway;
    }

    /**
     * Build pathway from array $to. Example: ['controller/action', 'id', 'add', ['get' => 'value']]
     * @param array $to
     * @param bool $encode
     * @return string|null
     */
    public static function buildPathway(array $to = null, $encode = true)
    {
        // if empty passed - let show main page
        if ($to === null) {
            return null;
        }

        $response = trim($to[0], '/'); // controller/action
        list($controller, $action) = explode('/', $response);

        $routing = App::$Properties->getAll('Routing');
        // sounds like dynamic callback
        if (Str::startsWith('@', $controller)) {
            $controller = trim($controller, '@');
            // search callback in properties
            if (isset($routing['Callback'][env_name]) && Arr::in($controller, $routing['Callback'][env_name])) {
                $pathInject = array_search($controller, $routing['Callback'][env_name]);
                // if path is founded - lets set source
                if ($pathInject !== false) {
                    $controller = Str::lowerCase($pathInject);
                }
            }

            // if controller still looks like path injection - define last entity like controller name
            if (Str::contains('\\', $controller)) {
                $controller = Str::lastIn($controller, '\\', true);
            }

            $response = $controller . '/' . $action;
        }

        // check if controller and action is defined
        if (Str::likeEmpty($controller) || Str::likeEmpty($action)) {
            return null;
        }

        // id is defined?
        if (isset($to[1]) && !Str::likeEmpty($to[1])) {
            $response .= '/' . self::safeUri($to[1], $encode);
        }

        // add param is defined?
        if (isset($to[2]) && !Str::likeEmpty($to[2])) {
            $response .= '/' . self::safeUri($to[2], $encode);
        }

        // try to find static alias
        if (isset($routing['Alias'][env_name]) && Arr::in('/' . $response, $routing['Alias'][env_name])) {
            $pathAlias = array_search('/' . $response, $routing['Alias'][env_name]);
            if ($pathAlias !== false) {
                $response = Str::lowerCase(trim($pathAlias, '/'));
            }
        }

        if (isset($to[3]) && Obj::isArray($to[3]) && count($to[3]) > 0) { // get params is defined?
            $response .= '?' . http_build_query($to[3]);
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
            Str::lowerCase(App::$Request->getController()) . '/' . Str::lowerCase(App::$Request->getAction()),
            App::$Request->getID(),
            App::$Request->getAdd(),
            App::$Request->query->all()
        ]);
    }

    /**
     * Create <a></a> block link
     * @param string|array $to
     * @param string $name
     * @param array|null $property
     * @return string
     */
    public static function link($to, $name, array $property = null)
    {
        $compile_property = self::applyProperty($property);

        if (!Obj::isArray($to)) { // callback magic (:
            $to = [$to];
        }
        // call Url::to(args)
        $callbackTo = call_user_func_array([__NAMESPACE__ . '\Url', 'to'], $to);

        return '<a href="' . $callbackTo . '"' . $compile_property . '>' . $name . '</a>';
    }

    /**
     * Download remote content in string
     * @param string $url
     * @return null|string
     */
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