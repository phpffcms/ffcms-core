<?php

namespace Ffcms\Core\Helper\HTML\Form;


use Ffcms\Core\App;
use Ffcms\Core\Exception\SyntaxException;
use Ffcms\Core\Helper\HTML\System\NativeGenerator;
use Ffcms\Core\Helper\Type\Any;
use Ffcms\Core\Helper\Type\Obj;

class RadioField extends NativeGenerator implements iField
{
    private $properties;
    private $name;
    private $value;

    /**
     * RadioField constructor. Pass inside values.
     * @param array $properties
     * @param string $name
     * @param string|null $value
     */
    public function __construct($properties, $name, $value = null)
    {
        $this->properties = $properties;
        $this->name = $name;
        $this->value = $value;

        // set default input type
        $this->properties['type'] = 'radio';
    }

    /**
     * Make function of current field type. Return compiled html response
     * @return string
     * @throws \Ffcms\Core\Exception\SyntaxException
     */
    public function make()
    {
        // get options from properties
        $options = $this->properties['options'];
        if (!Any::isIterable($options))
            throw new SyntaxException('Radio field ' . self::nohtml($this->name) . ' have no iterable options');

        unset($this->properties['options'], $this->properties['value']);

        // options is defined as key->value array?
        $optionsKey = $this->properties['optionsKey'] === true;
        unset($this->properties['optionsKey']);
        $build = null;
        // build output dom html
        foreach ($options as $idx => $value) {
            $property = $this->properties;
            if ($optionsKey === true) { // radio button as [value => text_description] - values is a key
                $property['value'] = $idx;
                if ($idx == $this->value) {
                    $property['checked'] = null; // def boolean attribute html5
                }
            } else { // radio button only with [value] data
                $property['value'] = $value;
                if ($value == $this->value) {
                    $property['checked'] = null; // def boolean attribute html5
                }
            }

            // get template and concat avg response
            $build .= App::$View->render('native/form/radio_list', [
                'tag' => self::buildSingleTag('input', $property),
                'text' => $value,
            ]);
        }

        return $build;
    }
}