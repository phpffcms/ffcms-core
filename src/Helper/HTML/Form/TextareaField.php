<?php
namespace Ffcms\Core\Helper\HTML\Form;

use Ffcms\Core\Helper\HTML\System\NativeGenerator;

class TextareaField extends NativeGenerator implements iField
{
    private $properties;
    private $name;
    private $value;
    
    /**
     * TextField constructor. Pass attributes inside model.
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
     * Build <input type="text" value="$value" {$properties} /> response
     * {@inheritDoc}
     * @see \Ffcms\Core\Helper\HTML\Form\iField::make()
     */
    public function make()
    {
        // check is html enabled there
        $html = false;
        if (isset($this->properties['html']) && $this->properties['html'] === true) {
            $html = true;
            unset($this->properties['html']);
        }
        // unset value
        if (isset($this->properties['value'])) {
            unset($this->properties['value']);
        }
        // return compiled DOM html
        return self::buildContainerTag('textarea', $this->properties, $this->value, $html);
    }
}
