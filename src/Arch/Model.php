<?php

namespace Ffcms\Core\Arch;

use Dflydev\DotAccessData\Data as DotData;
use Ffcms\Core\App;
use Ffcms\Core\Exception\SyntaxException;
use Ffcms\Core\Helper\Type\Any;
use Ffcms\Core\Helper\Type\Str;
use Ffcms\Core\Interfaces\iModel;
use Ffcms\Core\Traits\ModelValidator;
use Ffcms\Templex\Helper\Html\Form\ModelInterface;
use Ffcms\Templex\Helper\Html\Form\Model as TemplexModel;

/**
 * Class Model. Classic constructor of models in MVC architecture with algorithm of passing attributes from user input data.
 * @package Ffcms\Core\Arch
 */
abstract class Model extends TemplexModel implements iModel, ModelInterface
{
    use ModelValidator {
        ModelValidator::initialize as private validatorConstructor;
    }

    public $_csrf_token;

    /**
     * Model constructor. Initialize before() method for extended objects and run validator initialization
     * @param bool $csrf
     */
    public function __construct($csrf = false)
    {
        parent::__construct();
        $this->before();
        $this->validatorConstructor($csrf);
    }

    /**
     * Make any things before model is initialized
     */
    public function before() {}

    /**
     * Set attribute labels for model variables
     * @return array
     */
    public function labels(): array
    {
        return [];
    }

    /**
     * Set of model validation rules
     * @return array
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * Set model data sources for input data
     * Allowed sources: get, post, file
     * @return array
     */
    public function sources(): array
    {
        return [];
    }

    /**
     * Set model property types to advanced filtering
     * Allowed types: text, html, !secure
     * Ex: ['property1' => 'text', 'property2' => 'html']
     * @return array
     */
    public function types(): array
    {
        return [];
    }

    /**
     * Validate defined rules in app
     * @return bool
     * @throws SyntaxException
     */
    final public function validate(): bool
    {
        // validate csrf token if required
        if ($this->_tokenRequired && !$this->_tokenOk) {
            App::$Session->getFlashBag()->add('warning', __('Hack attention: security token is wrong!'));
            return false;
        }
        // get all rules as array from method rules()
        $rules = $this->rules();
        // get default values of attributes
        $defaultAttr = $this->getAllProperties();

        // start validation: on this step class attribute values will be changed to input data if it valid
        $success = $this->runValidate($rules);

        // get not-passed validation fields as array
        $badAttributes = $this->getBadAttribute();
        // prevent warnings
        if (Any::isArray($badAttributes) && count($badAttributes) > 0) {
            foreach ($badAttributes as $attr) {
                if (Str::contains('.', $attr)) { // sounds like dot-separated array attr
                    $attrName = strstr($attr, '.', true); // get attr name
                    $attrArray = trim(strstr($attr, '.'), '.'); // get dot-based array path

                    $defaultValue = new DotData($defaultAttr); // load default attr

                    $dotData = new DotData($this->{$attrName}); // load local attr variable
                    $dotData->set($attrArray, $defaultValue->get($attr)); // set to local prop. variable default value

                    $this->{$attrName} = $dotData->export(); // export to model
                } else {
                    $this->{$attr} = $defaultAttr[$attr]; // just set ;)
                }
                // add message about wrong attribute to session holder, later display it
                $attrLabel = $attr;
                if ($this->getLabel($attr) !== null) {
                    $attrLabel = $this->getLabel($attr);
                }

                App::$Session->getFlashBag()->add('warning', __('Field "%field%" is incorrect', ['field' => $attrLabel]));
            }
        }

        return $success;
    }

    /**
     * Get all properties for current model in key=>value array
     * @return array|null
     */
    public function getAllProperties(): ?array
    {
        $properties = null;
        // list all properties here, array_walk sucks on performance!
        foreach ($this as $property => $value) {
            if (Str::startsWith('_', $property)) {
                continue;
            }

            $properties[$property] = $value;
        }
        return $properties;
    }

    /**
     * Cleanup all public model properties
     * @return void
     */
    public function clearProperties(): void
    {
        foreach ($this as $property => $value) {
            if (!Str::startsWith('_', $property)) {
                $this->{$property} = null;
            }
        }
    }

    /**
     * Get validation rules for field
     * @param string $field
     * @return array
     */
    final public function getValidationRule($field): array
    {
        $rules = $this->rules();
        $response = [];

        foreach ($rules as $rule) {
            if (Any::isArray($rule[0])) { // 2 or more rules [['field1', 'field2'], 'filter', 'filter_argv']
                foreach ($rule[0] as $tfield) {
                    if ($tfield == $field) {
                        $response[$rule[1]] = $rule[2];
                    } // ['min_length' => 1, 'required' => null]
                }
            } else { // 1 rule ['field1', 'filter', 'filter_argv']
                if ($rule[0] === $field) {
                    $response[$rule[1]] = $rule[2];
                }
            }
        }

        return $response;
    }
}
