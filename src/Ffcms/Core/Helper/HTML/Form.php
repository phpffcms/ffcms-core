<?php

namespace Ffcms\Core\Helper\HTML;

use Core\Helper\String;

class Form extends \Core\Helper\HTML\NativeGenerator
{
    protected $structure = '<div class="form-group"><label for="%name%" class="col-md-3 control-label">%label%</label><div class="col-md-9">%item%</div></div>';
    protected $structureCheckbox = '<div class="form-group"><div class="col-md-9 col-md-offset-3"><div class="checkbox"><label>%item% %label%</label></div></div></div>';
    protected $name;


    public function __construct($elements)
    {
        if(String::length($elements['structure']) > 0)
            $this->structure = $elements['structure'];
        if(String::length($elements['name']) > 0)
            $this->name = $elements['name'];
        else
            $this->name = String::randomLatin(rand(6,12));

        echo '<form' . self::applyProperty($elements['property']) . '>';
    }

    /**
     * @param \Core\Arch\Model $model
     * @param $object
     * @param $type
     * @param null|array $property
     * @param null|string $structure
     * @return mixed
     */
    public function field($model, $object, $type, $property = null, $structure = null)
    {
        if(is_null($structure)) {
            if($type == 'checkbox')
                $structure = $this->structureCheckbox;
            else
                $structure = $this->structure;
        }

        $labelFor = $this->name . '-' . $object;
        $labelText = $model->getLabel($object);
        $itemValue = $model->{$object};
        $itemBody = $this->dataTypeTag($type, $object, $itemValue, $property);
        return str_replace(['%name%', '%label%', '%item%'], [$labelFor, $labelText, $itemBody], $structure);
    }

    public function submitButton($title, $property = [])
    {
        return '<input type="submit" name="submit" value="' . self::nohtml($title) . '"' . self::applyProperty($property) . ' />';
    }

    protected function dataTypeTag($type, $name, $value = null, $property = null)
    {
        $propertyString = null;
        $selectOptions = [];
        if(is_array($property['options']))
            $selectOptions = $property['options'];
        unset($property['options']);
        if(is_array($property))
            $propertyString = self::applyProperty($property);
        $response = null;
        switch($type) {
            case 'inputPassword':
                $response = '<input type="password" name="' . self::nohtml($name) . '" id="' . self::nohtml($this->name) . '-' . self::nohtml($name) . '"'. $propertyString . ' />';
                break;
            case 'textarea':
                $response = '<textarea name="' . self::nohtml($name) . '" id="' . self::nohtml($this->name) . '-' . self::nohtml($name) . '"'. $propertyString . '>'
                    . self::nohtml($value) . '</textarea>';
                break;
            case 'checkbox':
                $response = '<input type="checkbox" name="' . self::nohtml($name) . '" id="' . self::nohtml($this->name) . '-' . self::nohtml($name) . '"'. $propertyString
                    . ($value != 0 ? ' checked' : null) . ' />';
                break;
            case 'select':
                if(sizeof($selectOptions) < 1) {
                    $response = 'no options';
                } else {
                    $response = '<select name="' . self::nohtml($name) . '" id="' . self::nohtml($this->name) . '-' . self::nohtml($name) . '"'. $propertyString . '>';
                    foreach($selectOptions as $option) {
                        $response .= '<option' . ($option == $value ? ' selected' : null) . '>' . self::nohtml($option) . '</option>';
                    }
                    $response .= '</select>';
                }
                break;
            default:
                $response = '<input type="text" name="' . self::nohtml($name) . '" value="' . self::nohtml($value)
                    . '" id="' . self::nohtml($this->name) . '-' . self::nohtml($name) . '"'. $propertyString . ' />';
                break;
        }
        return $response;
    }

    public function finish()
    {
        echo '</form>';
    }
}