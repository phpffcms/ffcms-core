<?php
namespace Ffcms\Core\Helper\HTML\Form;

use Ffcms\Core\Helper\HTML\System\NativeGenerator;

class FileField extends NativeGenerator implements iField
{
    private $properties;
    private $name;
    
    /**
     * FileField constructor. Pass data inside the model.
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
     * Build <input type="file" {$properties} /> response
     * {@inheritDoc}
     * @see \Ffcms\Core\Helper\HTML\Form\iField::make()
     */
    public function make()
    {
        $this->properties['type'] = 'file';
        if (isset($this->properties['value'])) {
            unset($this->properties['value']);
        }
        return self::buildSingleTag('input', $this->properties);
    }
}