<?php

namespace Ffcms\Core\Helper\HTML;

use Ffcms\Core\App;
use Ffcms\Core\Helper\Arr;
use Ffcms\Core\Helper\File;
use Ffcms\Core\Helper\Object;
use Ffcms\Core\Helper\String;
use Ffcms\Core\Helper\HTML\NativeGenerator;
use Ffcms\Core\Arch\Model;

class Form extends NativeGenerator
{
    protected $structure = '<div class="form-group"><label for="%name%" class="col-md-3 control-label">%label%</label><div class="col-md-9">%item% <p class="help-block">%help%</p></div></div>';
    protected $structureCheckbox = '<div class="form-group"><div class="col-md-9 col-md-offset-3"><div class="checkbox"><label>%item% %label%</label></div><p class="help-block">%help%</p></div></div>';
    protected $structureCheckboxes = '<div class="checkbox"><label>%item%</label></div>';
    protected $name;
    /** @var Model */
    protected $model;


    public function __construct(Model $model, array $property = null, array $structure = null)
    {
        $this->model = $model;
        $this->name = $model->getFormName();

        // set custom html build structure form fields
        if (Object::isArray($structure)) {
            if (String::length($structure['base']) > 0) {
                $this->structure = $structure['base'];
            }
            if (String::length($structure['checkbox']) > 0) {
                $this->structureCheckbox = $structure['checkbox'];
            }
            if (String::length($structure['checkboxes']) > 0) {
                $this->structureCheckboxes = $structure['checkboxes'];
            }
        }

        if ($property['method'] === null) {
            $property['method'] = 'GET';
        }

        $property['id'] = $this->name; // define form id
        echo '<form' . self::applyProperty($property) . '>';
    }

    /**
     * Display form field. Allowed type: inputText, inputPassword, textarea, checkbox, select
     * @param $object
     * @param $type
     * @param null|array $property
     * @param null|string $helper
     * @param null|string $structure
     * @return null|string
     */
    public function field($object, $type, $property = null, $helper = null, $structure = null)
    {
        if ($this->model === null) {
            if (App::$Debug !== null) {
                App::$Debug->addMessage('Form model is not defined for field name: ' . strip_tags($object));
            }
            return null;
        }

        // can be dots separated object
        $propertyName = $object;
        if (String::contains('.', $propertyName)) {
            $propertyName = strstr($propertyName, '.', true);
        }

        if (!property_exists($this->model, $propertyName)) {
            if (App::$Debug !== null) {
                App::$Debug->addMessage('Form field "' . $object . '" is not defined in model: ' . get_class($this->model), 'error');
            }
            return null;
        }

        if (null === $structure) {
            if ($type === 'checkbox') {
                $structure = $this->structureCheckbox;
            } else {
                $structure = $this->structure;
            }
            // structureCheckboxes is apply'd to each items in builder later
        }

        $labelFor = $this->name . '-' . $propertyName;
        $labelText = $this->model->getLabel($object);
        $itemValue = $this->model->{$propertyName};
        // sounds like a dot-separated $object
        if ($propertyName !== $object) {
            $nesting = trim(strstr($object, '.'), '.');
            $labelFor .= '-' . String::replace('.', '-', $nesting);
            $itemValue = Arr::getByPath($nesting, $itemValue);
        }
        $itemBody = $this->dataTypeTag($type, $object, $itemValue, $property);
        return String::replace(
            ['%name%', '%label%', '%item%', '%help%'],
            [$labelFor, $labelText, $itemBody, self::nohtml($helper)],
            $structure
        );
    }

    protected function dataTypeTag($type, $name, $value = null, $property = null)
    {
        $propertyString = null;
        $selectOptions = [];
        if (Object::isArray($property['options'])) {
            $selectOptions = $property['options'];
        }

        // jquery validation quick-build some rules
        $rules = $this->model->getValidationRule($name);
        if (count($rules) > 0) {
            foreach ($rules as $rule_name => $rule_value) {
                switch ($rule_name) {
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
        $response = null;

        // standard property data definition
        $property['name'] = $property['id'] = $this->name; // form global name
        if ($value !== null) {
            $property['value'] = $value;
        }

        // sounds like a array-path based
        if (String::contains('.', $name)) {
            $splitedName = explode('.', $name);
            foreach($splitedName as $nameKey) {
                $property['name'] .= '[' . $nameKey . ']';
                $property['id'] .= '-' . $nameKey;
            }
        } else { // standard property's definition - add field name
            $property['name'] .= '[' . $name . ']';
            $property['id'] .= '-' . $name;
        }

        switch ($type) {
            case 'inputPassword':
                $property['type'] = 'password';
                unset($property['value']);
                $response = self::buildSingleTag('input', $property);
                break;
            case 'textarea':
                unset($property['value']);
                $response = self::buildContainerTag('textarea', $property, $value);
                break;
            case 'checkbox': // single checkbox for ON or OFF" value
                // hook DOM model
                $response = self::buildSingleTag('input', ['type' => 'hidden', 'value' => '0', 'name' => $property['name']]); // hidden 0 elem
                $property['type'] = 'checkbox';
                if ($value === 1 || $value === true || $value === '1') {
                    $property['checked'] = null; // set boolean attribute, maybe = "checked" is better
                }
                unset($property['required']);
                $property['value'] = '1';
                $response .= self::buildSingleTag('input', $property);
                break;
            case 'checkboxes':
                if (!Object::isArray($selectOptions)) {
                    if (App::$Debug !== null) {
                        App::$Debug->addMessage('Checkboxes field ' . $name . ' field have no options', 'warning');
                    }
                    break;
                }

                $property['type'] = 'checkbox';
                $property['name'] .= '[]';
                unset($property['value'], $property['id']);

                $buildCheckboxes = null;

                foreach ($selectOptions as $opt) {
                    if (Object::isArray($value) && Arr::in($opt, $value)) {
                        $property['checked'] = null;
                    } else {
                        unset($property['checked']); // remove checked if it setted before
                    }
                    $property['value'] = $opt;
                    // apply structured checkboxes style for each item
                    $buildCheckboxes .= String::replace('%item%' , self::buildSingleTag('input', $property) . self::nohtml($opt), $this->structureCheckboxes);
                }

                $response = $buildCheckboxes;
                break;
            case 'select':
                if (count($selectOptions) < 1) {
                    $response = 'Form select ' . self::nohtml($name) . ' have no options';
                } else {
                    unset($property['value']);
                    $optionsKey = $property['optionsKey'] === true;
                    unset($property['optionsKey']);
                    $buildOpt = null;
                    foreach ($selectOptions as $optIdx => $opt) {
                        $optionProperty = [];
                        if (true === $optionsKey) { // options with value => text
                            $optionProperty['value'] = $optIdx;
                            if ($optIdx == $value) {
                                $optionProperty['selected'] = null; // def boolean attribute html5
                            }
                        } else { // only value option
                            if ($opt == $value) {
                                $optionProperty['selected'] = null; // def boolean attribute html5
                            }
                        }
                        $buildOpt .= self::buildContainerTag('option', $optionProperty, $opt);
                    }

                    $response = self::buildContainerTag('select', $property, $buildOpt, true);
                }
                break;
            case 'captcha':
                if (App::$Captcha->isFull()) {
                    $response = App::$Captcha->get();
                } else {
                    $image = App::$Captcha->get();
                    $response = self::buildSingleTag('img', ['id' => 'src-secure-image', 'src' => $image, 'alt' => 'secure image', 'onClick' => 'this.src=\'' . $image . '&rnd=\'+Math.random()']);
                    $property['type'] = 'text';
                    $response .= self::buildSingleTag('input', $property);
                }
                break;
            case 'inputEmail':
                $property['type'] = 'email';
                $response = self::buildSingleTag('input', $property);
                break;
            case 'inputFile':
                $property['type'] = 'file';
                unset($property['value']);
                $response = self::buildSingleTag('input', $property);
                break;
            case 'hidden':
                $property['type'] = 'hidden';
                $response = self::buildSingleTag('input', $property);
                break;
            default:
                $property['type'] = 'text';
                $response = self::buildSingleTag('input', $property);
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
        $property['type'] = 'submit';
        $property['name'] = $this->name . '[submit]';
        $property['value'] = $title;
        return self::buildSingleTag('input', $property);
    }

    /**
     * Finish current form.
     * @param bool $validate
     * @return string
     */
    public function finish($validate = true)
    {
        // pre-validate form fields based on model rules and jquery.validation
        if ($validate) {
            App::$Alias->addPlainCode('js', '$().ready(function() { $("#' . $this->name . '").validate(); });');
            App::$Alias->setCustomLibrary('js', '/vendor/bower/jquery-validation/dist/jquery.validate.min.js');
            if (App::$Request->getLanguage() !== 'en') {
                $localeFile = '/vendor/bower/jquery-validation/src/localization/messages_' . App::$Request->getLanguage() . '.js';
                if (File::exist($localeFile)) {
                    App::$Alias->setCustomLibrary('js', $localeFile);
                }
            }
        }
        return '</form>';
    }
}