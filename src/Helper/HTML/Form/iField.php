<?php
namespace Ffcms\Core\Helper\HTML\Form;

interface iField
{
    /**
     * Construct of current field element. Pass inside values.
     * @param array $properties
     * @param string $name
     * @param string|null $value
     */
    public function __construct($properties, $name, $value = null);
    
    /**
     * Make function of current field type. Return compiled html response
     * @return string
     */
    public function make();
}
