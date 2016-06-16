<?php

namespace Ffcms\Core\Helper\HTML\Form;

use Ffcms\Core\App;
use Ffcms\Core\Helper\Type\Obj;
use Ffcms\Core\Exception\SyntaxException;
use Ffcms\Core\Helper\Type\Str;

class Constructor
{
    const TYPE_TEXT = 'text';
    const TYPE_PASSWORD = 'password';
    const TYPE_EMAIL = 'email';
    const TYPE_CHECKBOX = 'checkbox';
    const TYPE_SELECT = 'select';
    const TYPE_MULTISELECT = 'multiselect';
    const TYPE_TEXTAREA = 'textarea';
    const TYPE_MULTI_CHECKBOXES = 'checkboxes';
    const TYPE_CAPTCHA = 'captcha';
    const TYPE_FILE = 'file';
    const TYPE_HIDDEN = 'hidden';
    const TYPE_DIV_FAKE = 'div';
    const TYPE_RADIO = 'radio';
    
    /** @var \Ffcms\Core\Arch\Model $model */
    private $model;
    private $formName;
    private $type;
    
    /**
     * Initialize Constructor. Pass model and type inside of current field inside.
     * @param \Ffcms\Core\Arch\Model $model
     * @param string $type
     */
    public function __construct($model, $formName = false, $type = 'text')
    {
        $this->model = $model;
        $this->formName = $formName;
        $this->type = $type;
    }
    
    
    public function makeTag($name, $value = null, $properties = null)
    {
        // check if properties is passed well
        if ($properties !== null && !Obj::isArray($properties)) {
            throw new SyntaxException('Property must be passed as array or null! Field: ' . $name);
        }
        
        // add properties to autovalidation by js (properties passed by ref)
        $this->validatorProperties($name, $properties);
        // prepare properties name and id to autobuild
        $this->globalProperties($name, $value, $properties);
        
        // initialize build model depend of current type
        switch ($this->type) {
            case static::TYPE_TEXT: // for <input type="text">
                $builder = new TextField($properties, $name);
                return $builder->make();
            case static::TYPE_CHECKBOX:
                $builder = new CheckboxField($properties, $name, $value);
                return $builder->make();
            case static::TYPE_PASSWORD:
                $builder = new PasswordField($properties, $name);
                return $builder->make();
            case static::TYPE_EMAIL:
                $builder = new EmailField($properties, $name);
                return $builder->make();
            case static::TYPE_SELECT:
                $builder = new SelectField($properties, $name, $value);
                return $builder->make();
            case static::TYPE_TEXTAREA:
                $builder = new TextareaField($properties, $name, $value);
                return $builder->make();
            case static::TYPE_MULTI_CHECKBOXES:
                $builder = new MultiCheckboxField($properties, $name, $value);
                return $builder->make();
            case static::TYPE_CAPTCHA:
                $builder = new CaptchaField($properties, $name);
                return $builder->make();
            case static::TYPE_FILE:
                $builder = new FileField($properties, $name);
                return $builder->make();
            case static::TYPE_HIDDEN:
                $builder = new HiddenField($properties, $name, $value);
                return $builder->make();
            case static::TYPE_DIV_FAKE:
                $builder = new DivFakeField($properties, $name, $value);
                return $builder->make();
            case static::TYPE_MULTISELECT:
                $builder = new MultiSelectField($properties, $name, $value);
                return $builder->make();
            case static::TYPE_RADIO:
                $builder = new RadioField($properties, $name, $value);
                return $builder->make();
        }
        
        // if field is unknown type add notification in debugbar
        if (App::$Debug !== null) {
            App::$Debug->addMessage('Field with name [' . App::$Security->strip_tags($name) . '] have unknown type [' . $this->type . ']', 'error');
        }
        return 'No data: ' . App::$Security->strip_tags($name);
    }
    
    /**
     * Set validator options to current properties
     * @param string $name
     * @param array $properties
     */
    private function validatorProperties($name, &$properties)
    {
        // jquery validation quick-build some rules
        $rules = $this->model->getValidationRule($name);
        if (count($rules) > 0) {
            foreach ($rules as $rule_name => $rule_value) {
                switch ($rule_name) {
                    case 'required':
                        $properties['required'] = null;
                        break;
                    case 'length_min':
                        $properties['minlength'] = $rule_value;
                        break;
                    case 'length_max':
                        $properties['maxlength'] = $rule_value;
                        break;
                }
            }
        }
    }
    
    /**
     * Prepare field global properties - name, id and value
     * @param string $name
     * @param string|null $value
     * @param array $properties
     */
    private function globalProperties($name, $value = null, &$properties)
    {
        // standard property data definition
        $properties['name'] = $properties['id'] = $this->formName; // form global name
        if ($value !== null && !Str::likeEmpty($value)) {
            $properties['value'] = $value;
        }
        
        // sounds like a array-path based obj name
        if (Str::contains('.', $name)) {
            $splitedName = explode('.', $name);
            foreach ($splitedName as $nameKey) {
                $properties['name'] .= '[' . $nameKey . ']';
                $properties['id'] .= '-' . $nameKey;
            }
        } else { // standard property definition - add field name
            $properties['name'] .= '[' . $name . ']';
            $properties['id'] .= '-' . $name;
        }
    }
}