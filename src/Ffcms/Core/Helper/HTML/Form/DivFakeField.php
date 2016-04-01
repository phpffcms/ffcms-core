<?php
namespace Ffcms\Core\Helper\HTML\Form;

use Ffcms\Core\Helper\HTML\System\NativeGenerator;

class DivFakeField extends NativeGenerator implements iField
{
    private $properties;
    private $name;
    private $value;
    
    /**
     * DivFakeField constructor. Pass attributes inside model.
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
     * Build <div $properties>$value</div> response
     * {@inheritDoc}
     * @see \Ffcms\Core\Helper\HTML\Form\iField::make()
     */
    public function make()
    {
        // value is not required in properties now
        unset($this->properties['value']);
        $html = false;
        if (isset($this->properties['html']) && $this->properties['html'] === true) {
            $html = true;
            unset($this->properties['html']);
        }
        
        return self::buildContainerTag('div', $this->properties, $this->value, $html);
    }
}