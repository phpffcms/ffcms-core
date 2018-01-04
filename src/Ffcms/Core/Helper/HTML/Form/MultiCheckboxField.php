<?php
namespace Ffcms\Core\Helper\HTML\Form;

use Ffcms\Core\App;
use Ffcms\Core\Exception\SyntaxException;
use Ffcms\Core\Helper\HTML\System\NativeGenerator;
use Ffcms\Core\Helper\Type\Any;
use Ffcms\Core\Helper\Type\Arr;
use Ffcms\Core\Helper\Type\Obj;

/**
 * Class MultiCheckboxField. Make multiple checkboxes form field
 * @package Ffcms\Core\Helper\HTML\Form
 */
class MultiCheckboxField extends NativeGenerator implements iField
{
    private $properties;
    private $name;
    private $value;

    /**
     * MultiCheckboxField constructor.
     * Pass params inside.
     * 
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
        // check if options is defined
        $options = $this->properties['options'];
        if (!Any::isIterable($options))
            throw new SyntaxException('Options for field ' . self::nohtml($this->name) . ' is not iterable');

        unset($this->properties['options']);
        
        // set field type
        $this->properties['type'] = 'checkbox';
        // set this field as array html dom object
        $this->properties['name'] .= '[]';
        unset($this->properties['value'], $this->properties['id']);
        
        $build = null;
        foreach ($options as $opt) {
            // check if this is active element
            if (Any::isArray($this->value) && Arr::in($opt, $this->value)) {
                $this->properties['checked'] = null;
            } else {
                unset($this->properties['checked']); // remove checked if it setted before
            }
            $this->properties['value'] = $opt;
            // apply structured checkboxes style for each item
            $build .= App::$View->render('native/form/multi_checkboxes_list', [
                'item' => self::buildSingleTag('input', $this->properties) . self::nohtml($opt)
            ]);
        }
        
        return $build;
    }
}