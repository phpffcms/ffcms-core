<?php

namespace Ffcms\Core\Helper\HTML;

use Ffcms\Core\App;
use Ffcms\Core\Helper\String;
use Ffcms\Core\Helper\HTML\NativeGenerator;

class Form extends NativeGenerator
{
    protected $structure = '<div class="form-group"><label for="%name%" class="col-md-3 control-label">%label%</label><div class="col-md-9">%item%</div></div>';
    protected $structureCheckbox = '<div class="form-group"><div class="col-md-9 col-md-offset-3"><div class="checkbox"><label>%item% %label%</label></div></div></div>';
    protected $name;
    /** @var  \Ffcms\Core\Arch\Model */
    protected $model;


    public function __construct($elements)
    {
        if (String::length($elements['structure']) > 0) {
            $this->structure = $elements['structure'];
        }
        if (String::length($elements['name']) > 0) {
            $this->name = App::$Security->strip_tags($elements['name']);
        } else {
            $this->name = String::randomLatin(rand(6, 12));
        }

        if (is_object($elements['model'])) {
            $this->model = $elements['model'];
        }

        $elements['property']['id'] = $this->name; // define form id

        echo '<form' . self::applyProperty($elements['property']) . '>';
    }

    /**
     * Display form field. Allowed type: inputText, inputPassword, textarea, checkbox, select
     * @param $object
     * @param $type
     * @param null|array $property
     * @param null|string $helper
     * @param null|string $structure
     * @return mixed
     */
    public function field($object, $type, $property = null, $helper = null, $structure = null)
    {
        if ($this->model === null) {
            return null;
        }

        if (null === $structure) {
            if ($type === 'checkbox') {
                $structure = $this->structureCheckbox;
            } else {
                $structure = $this->structure;
            }
        }

        $labelFor = $this->name . '-' . $object;
        $labelText = $this->model->getLabel($object);
        $itemValue = $this->model->{$object};
        $itemBody = $this->dataTypeTag($type, $object, $itemValue, $property);
        return str_replace(
            ['%name%', '%label%', '%item%', '%help%'],
            [$labelFor, $labelText, $itemBody, self::nohtml($helper)],
            $structure
        );
    }

    protected function dataTypeTag($type, $name, $value = null, $property = null)
    {
        $propertyString = null;
        $selectOptions = [];
        if (is_array($property['options'])) {
            $selectOptions = $property['options'];
        }

        // jquery validation quick-build some rules
        $rules = $this->model->getValidationRule($name);
        if (count($rules) > 0) {
            foreach ($rules as $rule_name=>$rule_value) {
                switch($rule_name) {
                    case 'required':
                        $property['required'] = null;
                        break;
                    case 'length_min':
                        $property['minlength'] = $rule_value;
                        break;
                    case 'length_max':
                        $property['maxlength'] = $rule_value;
                        break;
                }
            }
        }

        unset($property['options']);
        $propertyString = self::applyProperty($property);
        $response = null;
        switch ($type) {
            case 'inputPassword':
                $response = '<input type="password" name="' . self::nohtml($name) . '" id="' . self::nohtml($this->name) . '-' . self::nohtml($name) . '"' . $propertyString . ' />';
                break;
            case 'textarea':
                $response = '<textarea name="' . self::nohtml($name) . '" id="' . self::nohtml($this->name) . '-' . self::nohtml($name) . '"' . $propertyString . '>'
                    . self::nohtml($value) . '</textarea>';
                break;
            case 'checkbox':
                // hook DOM model
                $response = '<input type="hidden" value="0" name="' . self::nohtml($name) . '" />';
                $response .= '<input type="checkbox" name="' . self::nohtml($name) . '" id="' . self::nohtml($this->name) . '-' . self::nohtml($name) . '"' . $propertyString
                    . ' value="1"'. ($value == 1 ? ' checked' : null) .' />';
                //$response = '<input type="checkbox" name="' . self::nohtml($name) . '" id="' . self::nohtml($this->name) . '-' . self::nohtml($name) . '"' . $propertyString
                //    . ($value != 0 ? ' checked' : null) . ' />';
                break;
            case 'select':
                if (count($selectOptions) < 1) {
                    $response = 'no options';
                } else {
                    $response = '<select name="' . self::nohtml($name) . '" id="' . self::nohtml($this->name) . '-' . self::nohtml($name) . '"' . $propertyString . '>';
                    foreach ($selectOptions as $option) {
                        $response .= '<option' . ($option == $value ? ' selected' : null) . '>' . self::nohtml($option) . '</option>';
                    }
                    $response .= '</select>';
                }
                break;
            case 'inputEmail':
                $response = '<input type="email" name="' . self::nohtml($name) . '" value="' . self::nohtml($value)
                    . '" id="' . self::nohtml($this->name) . '-' . self::nohtml($name) . '"' . $propertyString . ' />';
                break;
            default:
                $response = '<input type="text" name="' . self::nohtml($name) . '" value="' . self::nohtml($value)
                    . '" id="' . self::nohtml($this->name) . '-' . self::nohtml($name) . '"' . $propertyString . ' />';
                break;
        }
        return $response;
    }

    /**
     * Display submit button for current form
     * @param string $title
     * @param array $property
     * @return string
     */
    public function submitButton($title, array $property = [])
    {
        return '<input type="submit" name="submit" value="' . self::nohtml($title) . '"' . self::applyProperty($property) . ' />';
    }

    /**
     * Finish current form.
     */
    public function finish($validate = true)
    {
        echo '</form>';
        // validation ;)
        if ($validate) {
            App::$Alias->afterBody[] = '<script>$().ready(function() { $("#' . $this->name . '").validate(); });</script>';
            App::$Alias->customJS[] = '/vendor/bower/jquery-validation/dist/jquery.validate.min.js';
            if(App::$Request->getLanguage() !== 'en') {
                App::$Alias->customJS[] = '/vendor/bower/jquery-validation/src/localization/messages_' . App::$Request->getLanguage() . '.js';
            }
        }
    }
}