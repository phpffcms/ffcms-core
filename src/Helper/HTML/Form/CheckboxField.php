<?php
namespace Ffcms\Core\Helper\HTML\Form;

use Ffcms\Core\Helper\HTML\System\NativeGenerator;

class CheckboxField extends NativeGenerator implements iField
{
    private $properties;
    private $name;
    private $value;
    
    /**
     * CheckboxField constructor. Pass params inside model.
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
     * Build <input type="checkbox" checked {$properties} /> response
     * {@inheritDoc}
     * @see \Ffcms\Core\Helper\HTML\Form\iField::make()
     */
    public function make()
    {
        // hook DOM model: build "hidden" field with value="0" for this box
        $response = self::buildSingleTag('input', [
            'type' => 'hidden',
            'value' => '0',
            'name' => $this->properties['name']
        ]);
        
        // set field type
        $this->properties['type'] = 'checkbox';
        if ($this->value === 1 || $this->value === true || $this->value === '1') {
            $this->properties['checked'] = null; // set checked if active
        }
        
        unset($this->properties['required']);
        // this item always have "1" value (0 is hidden and active when this is :not(checked)
        $this->properties['value'] = '1';
        $response .= self::buildSingleTag('input', $this->properties);
        return $response;
    }
}
