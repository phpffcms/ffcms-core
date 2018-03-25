<?php

namespace Helper\Url;


use Ffcms\Core\Helper\Type\Str;


/**
 * Class Url. Build and parse urls
 * @package Helper\Url
 */
class Url
{
    private $controller;
    private $action;
    private $id;
    private $add;
    private $query;
    private $encode = false;

    /**
     * Url constructor. Create instance before process params
     * @param string $controllerAction
     * @param null|string $id
     * @param null|string $add
     * @param array|null $query
     * @param bool $encode
     */
    public function __construct(string $controllerAction, ?string $id, ?string $add, ?array $query = null, bool $encode = false)
    {
        $controllerAction = Str::lowerCase(trim($controllerAction, '/'));
        [$controller, $action] = explode('/', $controllerAction);

        $this->controller = $controller;
        $this->action = $action;
        $this->id = $id;
        $this->add = $add;
        $this->query = $query;
        $this->encode = $encode;
    }

    /**
     * Build link from passed params. Old method Url::to() -> Url::href
     * @param array|null $to
     * @param bool $encode
     * @return null|string
     */
    public static function href(?array $to = null, bool $encode = false): ?string
    {
        if (!$to || !isset($to[0])) {
            return null;
        }

        $instance = new self($to[0], $to[1], $to[2], $to[3], $encode);
        return $instance->buildLinkString();
    }

    private function buildLinkString()
    {
        return 'test';
    }
}