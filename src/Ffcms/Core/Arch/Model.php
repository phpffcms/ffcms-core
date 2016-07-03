<?php

namespace Ffcms\Core\Arch;

use Ffcms\Core\App;
use Ffcms\Core\Exception\SyntaxException;
use Dflydev\DotAccessData\Data as DotData;
use Ffcms\Core\Helper\Type\Obj;
use Ffcms\Core\Helper\Type\Str;
use Ffcms\Core\Traits\DynamicGlobal;
use Ffcms\Core\Traits\ModelValidator;

/**
 * Class Model. Classic constructor of models in MVC architecture with algorithm of passing attributes from user input data.
 * @package Ffcms\Core\Arch
 */
class Model
{
    use DynamicGlobal, ModelValidator {
        ModelValidator::initialize as private validatorConstructor;
    }

    public $_csrf_token;

    /**
     * Model constructor. Initialize before() method for extended objects and run validator initialization
     * @param bool $csrf
     */
    public function __construct($csrf = false)
    {
        $this->before();
        $this->validatorConstructor($csrf);
    }

    /**
     * Make any things before model is initialized
     */
    public function before() {}

    /**
     * Get label value by variable name
     * @param string $param
     * @return mixed
     */
    final public function getLabel($param)
    {
        $labels = $this->labels();
        $response = null;
        // maybe array-dotted declaration?
        if (Str::contains('.', $param)) {
            // not defined for all array-dotted nesting?
            if (Str::likeEmpty($labels[$param])) {
                // lets set default array label (before first dot-separation)
                $response = $labels[Str::firstIn($param, '.')];
            } else {
                $response = $labels[$param];
            }
        } else {
            $response = $labels[$param];
        }

        return (Str::likeEmpty($response) ? Str::replace('.', ' ', Str::splitCamelCase($param)) : $response);
    }

    /**
     * Set attribute labels for model variables
     * @return array
     */
    public function labels()
    {
        return [];
    }

    /**
     * Set of model validation rules
     * @return array
     */
    public function rules()
    {
        return [];
    }

    /**
     * Set model data sources for input data
     * @return array
     */
    public function sources()
    {
        return [];
    }

    /**
     * Validate defined rules in app
     * @return bool
     * @throws SyntaxException
     */
    final public function validate()
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
        if (Obj::isArray($badAttributes) && count($badAttributes) > 0) {
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
     * Filter model fields as text, html or secure obscured
     * @param array|null $fields
     * @return $this
     */
    public function filter(array $fields = null)
    {
        // list all model fields
        $allFields = $this->getAllProperties();
        if ($allFields !== null && Obj::isArray($allFields)) {
            foreach ($allFields as $f => $v) {
                // if attr is not passed - set from global as plaintext
                if (!isset($fields[$f])) {
                    $fields[$f] = 'text';
                }
            }
        }

        // if no fields is found - return
        if (!Obj::isArray($fields)) {
            return $this;
        }

        // each all properties
        foreach ($fields as $field => $security) {
            // get property value
            $fieldValue = $this->{$field};
            // switch security levels
            switch ($security) {
                case '!secure': // is full security obscured field, skip it
                    break;
                case 'html':
                    $this->{$field} = App::$Security->secureHtml($fieldValue);
                    break;
                default: // text/plaintext
                    $this->{$field} = App::$Security->strip_tags($fieldValue);
                    break;
            }
        }

        return $this;
    }

    /**
     * Export model values for safe-using in HTML pages.
     * @deprecated
     * @return $this
     */
    final public function export()
    {
        return $this->filter(null);
    }


    /**
     * Get all properties for current model in key=>value array
     * @return array|null
     */
    public function getAllProperties()
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
     */
    public function clearProperties()
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
    final public function getValidationRule($field)
    {
        $rules = $this->rules();
        $response = [];

        foreach ($rules as $rule) {
            if (Obj::isArray($rule[0])) { // 2 or more rules [['field1', 'field2'], 'filter', 'filter_argv']
                foreach ($rule[0] as $tfield) {
                    if ($tfield == $field) {
                        $response[$rule[1]] = $rule[2]; // ['min_length' => 1, 'required' => null]
                    }
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