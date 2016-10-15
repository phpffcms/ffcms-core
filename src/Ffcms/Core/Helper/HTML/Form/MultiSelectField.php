<?php
namespace Ffcms\Core\Helper\HTML\Form;

use Ffcms\Core\Exception\SyntaxException;
use Ffcms\Core\Helper\HTML\System\NativeGenerator;
use Ffcms\Core\Helper\Type\Arr;
use Ffcms\Core\Helper\Type\Obj;

class MultiSelectField extends NativeGenerator implements iField
{
    private $properties;
    private $name;
    private $value;

    /**
     * MultiSelectField constructor. Pass params inside.
     * @param array $properties
     * @param string $name
     * @param string|null $value
     */
    public function __construct($properties, $name, $value = null)
    {
        $this->properties = $properties;
        $this->name = $name;
        $this->value = $value;
    }

    /**
     * Build <select @properties>@optionsDOM</select> container 
     * {@inheritDoc}
     * @see \Ffcms\Core\Helper\HTML\Form\iField::make()
     */
    public function make()
    {
        // check if options is defined
        $options = $this->properties['options'];
        $optionsKey = (bool)$this->properties['optionsKey'];
        if (!Obj::isIterable($options)) {
            throw new SyntaxException('Options for field ' . self::nohtml($this->name) . ' is not iterable');
        }
        unset($this->properties['options']);
        
        // set global field type
        $this->properties['type'] = 'select';
        // add multiple element
        $this->properties['multiple'] = null;
        $this->properties['name'] .= '[]';
        unset($this->properties['value'], $this->properties['options'], $this->properties['optionsKey']);

        // build options as HTML DOM element: <option @value>@text</option>
        $optionsDOM = null;
        foreach ($options as $val => $text) {
            // check if options key is value
            $optionProperty = null;
            if ($optionsKey === true) {
                $optionProperty['value'] = $val;
                // check if current element is active
                if (Obj::isArray($this->value) && Arr::in((string)$val, $this->value)) {
                    $optionProperty['selected'] = 'selected';
                }
            } else {
                if (Obj::isArray($this->value) && Arr::in((string)$text, $this->value)) {
                    $optionProperty['selected'] = 'selected';
                }
            }
            $optionsDOM .= self::buildContainerTag('option', $optionProperty, $text);
        }
        
        // build <select @properties>@optionsDOM</select>
        return self::buildContainerTag('select', $this->properties, $optionsDOM, true);
    }
}