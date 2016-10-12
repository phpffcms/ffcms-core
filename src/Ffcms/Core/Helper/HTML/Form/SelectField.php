<?php
namespace Ffcms\Core\Helper\HTML\Form;

use Ffcms\Core\Helper\HTML\System\NativeGenerator;
use Ffcms\Core\Exception\SyntaxException;
use Ffcms\Core\Helper\Type\Obj;

class SelectField extends NativeGenerator implements iField
{
    private $properties;
    private $name;
    private $value;
    
    /**
     * SelectField constructor. Pass arguments inside model
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
     * Build <select {$properties} >[<options>]</select> response
     * {@inheritDoc}
     * @see \Ffcms\Core\Helper\HTML\Form\iField::make()
     */
    public function make()
    {
        // get options from properties
        $options = $this->properties['options'];
        unset($this->properties['options']);
        if (!Obj::isIterable($options)) {
            throw new SyntaxException('Select field ' . self::nohtml($this->name) . ' have no iterable options');
        }
        // value is not used there
        unset($this->properties['value']);
        // options is defined as key->value array?
        $optionsKey = $this->properties['optionsKey'] === true;
        unset($this->properties['optionsKey']);
        $buildOpt = null;
        foreach ($options as $optIdx => $opt) {
            $optionProperty = [];
            if ($optionsKey === true) { // options with value => text
                $optionProperty['value'] = $optIdx;
                if ($optIdx == $this->value) {
                    $optionProperty['selected'] = null; // def boolean attribute html5
                }
            } else { // only value option
                if ($opt == $this->value) {
                    $optionProperty['selected'] = null; // def boolean attribute html5
                }
            }
            $buildOpt .= self::buildContainerTag('option', $optionProperty, $opt);
        }
        
        // return compiled DOM
        return self::buildContainerTag('select', $this->properties, $buildOpt, true);
    }
}