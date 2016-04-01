<?php
namespace Ffcms\Core\Helper\HTML\Form;

use Ffcms\Core\Helper\HTML\System\NativeGenerator;

class EmailField extends NativeGenerator implements iField
{
    private $properties;
    private $name;

    /**
     * EmailField constructor. Pass attributes inside model.
     * @param array $properties
     * @param string $name
     * @param string|null $value
     */
    public function __construct($properties, $name, $value = null)
    {
        $this->properties = $properties;
        $this->name = $name;
    }
    

    /**
     * Build <input type="password" {$properties} /> response
     * {@inheritDoc}
     * @see \Ffcms\Core\Helper\HTML\Form\iField::make()
     */
    public function make()
    {
        $this->properties['type'] = 'email';
        
        return self::buildSingleTag('input', $this->properties);
    }
}