<?php

namespace Ffcms\Core\Helper\HTML\Form;

use Ffcms\Core\App;
use Ffcms\Core\Arch\Model;
use Ffcms\Core\Exception\SyntaxException;
use Ffcms\Core\Helper\Type\Any;
use Ffcms\Core\Helper\Type\Obj;
use Ffcms\Core\Helper\Type\Str;

/**
 * Class Constructor. Form field construction manager.
 * @package Ffcms\Core\Helper\HTML\Form
 */
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
     * @param string|null $formName
     * @param string $type
     */
    public function __construct(Model $model, ?string $formName = null, ?string $type = 'text')
    {
        $this->model = $model;
        $this->formName = $formName;
        $this->type = $type;
    }

    /**
     * @param string|array $name
     * @param string|array|null $value
     * @param array|null $properties
     * @return null|string
     */
    public function makeTag($name, $value = null, ?array $properties = null): ?string
    {
        // check if properties is passed well
        if ($properties !== null && !Any::isArray($properties)) {
            return null;
        }
        
        // add properties to autovalidation by js (properties passed by ref)
        $this->addValidationProperties($name, $properties);
        // set default form data as properties: name="", value="", id="" based tag info
        $this->setDefaultProperties($name, $value, $properties);
        
        // initialize build model depend of current type
        switch ($this->type) {
            case static::TYPE_TEXT: // for <input type="text">
                $builder = new TextField($properties, $name);
                break;
            case static::TYPE_CHECKBOX:
                $builder = new CheckboxField($properties, $name, $value);
                break;
            case static::TYPE_PASSWORD:
                $builder = new PasswordField($properties, $name);
                break;
            case static::TYPE_EMAIL:
                $builder = new EmailField($properties, $name);
                break;
            case static::TYPE_SELECT:
                $builder = new SelectField($properties, $name, $value);
                break;
            case static::TYPE_TEXTAREA:
                $builder = new TextareaField($properties, $name, $value);
                break;
            case static::TYPE_MULTI_CHECKBOXES:
                $builder = new MultiCheckboxField($properties, $name, $value);
                break;
            case static::TYPE_CAPTCHA:
                $builder = new CaptchaField($properties, $name);
                break;
            case static::TYPE_FILE:
                $builder = new FileField($properties, $name);
                break;
            case static::TYPE_HIDDEN:
                $builder = new HiddenField($properties, $name, $value);
                break;
            case static::TYPE_DIV_FAKE:
                $builder = new DivFakeField($properties, $name, $value);
                break;
            case static::TYPE_MULTISELECT:
                $builder = new MultiSelectField($properties, $name, $value);
                break;
            case static::TYPE_RADIO:
                $builder = new RadioField($properties, $name, $value);
                break;
            default:
                if (App::$Debug) {
                    App::$Debug->addMessage('Field has unknown type: ' . App::$Security->strip_tags($name));
                }
        }

        return $builder->make();
    }
    
    /**
     * Set validator options to current properties
     * @param string $name
     * @param array|null $properties
     * @return void
     */
    private function addValidationProperties(string $name, ?array &$properties = null): void
    {
        // jquery validation quick-build some rules
        $rules = $this->model->getValidationRule($name);
        if (count($rules) > 0) {
            foreach ($rules as $type => $value) {
                switch ($type) {
                    case 'required':
                        $properties['required'] = null;
                        break;
                    case 'length_min':
                        $properties['minlength'] = $value;
                        break;
                    case 'length_max':
                        $properties['maxlength'] = $value;
                        break;
                }
            }
        }
    }
    
    /**
     * Prepare field global properties - name, id and value
     * @param string $name
     * @param string|null $value
     * @param array|null $properties
     */
    private function setDefaultProperties(string $name, $value = null, array &$properties = null): void
    {
        // standard property data definition
        $properties['name'] = $properties['id'] = $this->formName; // form global name
        if ($value !== null && !Any::isEmpty($value)) {
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
