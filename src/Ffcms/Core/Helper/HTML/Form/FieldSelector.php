<?php

namespace Ffcms\Core\Helper\HTML\Form;


use Ffcms\Core\App;
use Ffcms\Core\Arch\Model;
use Ffcms\Core\Exception\SyntaxException;
use Ffcms\Core\Helper\HTML\System\NativeGenerator;
use Ffcms\Core\Helper\Type\Arr;
use Ffcms\Core\Helper\Type\Str;

/**
 * Class FieldSelector. Form field builder instance.
 * @package Ffcms\Core\Helper\HTML\Form
 * @method text(string $name, ?array $properties = null, ?string $helper = null, ?string $layerFile = null)
 * @method password(string $name, ?array $properties = null, ?string $helper = null, ?string $layerFile = null)
 * @method hidden(string $name, ?array $properties = null)
 * @method select(string $name, ?array $properties = null, ?string $helper = null, ?string $layerFile = null)
 * @method checkbox(string $name, ?array $properties = null, ?string $helper = null, ?string $layerFile = null)
 * @method email(string $name, ?array $properties = null, ?string $helper = null, ?string $layerFile = null)
 * @method textarea(string $name, ?array $properties = null, ?string $helper = null, ?string $layerFile = null)
 * @method checkboxes(string $name, ?array $properties = null, ?string $helper = null, ?string $layerFile = null)
 * @method captcha(string $name, ?array $properties = null, ?string $helper = null, ?string $layerFile = null)
 * @method file(string $name, ?array $properties = null, ?string $helper = null, ?string $layerFile = null)
 * @method div(string $name, ?array $properties = null, ?string $helper = null, ?string $layerFile = null)
 * @method radio(string $name, ?array $properties = null, ?string $helper = null, ?string $layerFile = null)
 * @method multiselect(string $name, ?array $properties = null, ?string $helper = null, ?string $layerFile = null)
 */
class FieldSelector extends NativeGenerator
{
    /** @var Model */
    private $model;
    /** @var array */
    private $layers;

    /**
     * FieldSelector constructor.
     * @param Model $model
     * @param array $defaultLayers
     */
    public function __construct(Model $model, array $defaultLayers = [])
    {
        $this->model = $model;
        $this->layers = $defaultLayers;
    }

    /**
     * @param string $field
     * @param array|null $arguments
     * @return string|null
     */
    public function __call(string $field, ?array $arguments = null): ?string
    {
        if ($arguments === null || count($arguments) < 1)
            return null;

        // get named arguments from passed array
        $name = (string)$arguments[0];
        $rootName = $name;
        // set root element name if array dot-nested notation found
        if (Str::contains('.', $name))
            $rootName = strstr($rootName, '.', true);
        // define properties, helper and layer
        $properties = null;
        $helper = null;
        $layer = null;
        if (isset($arguments[1]))
            $properties = (array)$arguments[1];

        if (isset($arguments[2]))
            $helper = (string)$arguments[2];

        if (isset($arguments[3]))
            $layer = (string)$arguments[3];

        // check if model has attribute
        if (!property_exists($this->model, $rootName)) {
            if (App::$Debug)
                App::$Debug->addMessage('Field "' . $name . '" (' . $field . ') is not defined in model: [' . get_class($this->model) . ']', 'error');

            return null;
        }

        // prepare default layer if not passed
        $layer = $this->findFieldLayer($field, $layer);
        // prepare html attributes, object value
        $attr = $this->model->getFormName() . '-' . $rootName;
        $label = $this->model->getLabel($name);
        $value = $this->model->{$rootName};
        // check if dot-nested array used and update attr name
        if ($rootName !== $name) {
            $nesting = trim(strstr($name, '.'), '.');
            $attr .= '-' . Str::replace('.', '-', $nesting);
            $value = Arr::getByPath($nesting, $value);
        }

        // initialize form fields constructor and build output dom html value
        $constructor = new Constructor($this->model, $this->model->getFormName(), $field);
        $elementDOM = $constructor->makeTag($name, $value, $properties);

        // if item is hidden - return tag without assign of global template
        if ($field === 'hidden')
            return $elementDOM;

        // prepare output html
        try {
            return App::$View->render($layer, [
                'name' => $attr,
                'label' => $label,
                'item' => $elementDOM,
                'help' => self::nohtml($helper)
            ]);
        } catch (SyntaxException $e) {
            if (App::$Debug)
                App::$Debug->addException($e);
            return null;
        }
    }

    /**
     * Find default layer if not passed direct
     * @param null|string $type
     * @param null|string $layer
     * @return null|string
     */
    private function findFieldLayer(?string $type = null, ?string $layer = null): ?string
    {
        if ($layer !== null)
            return $layer;

        switch ($type) {
            case 'checkbox':
                $layer = $this->layers['checkbox'];
                break;
            case 'radio':
                $layer = $this->layers['radio'];
                break;
            default:
                $layer = $this->layers['base'];
                break;
        }

        return $layer;
    }
}