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
    use DynamicGlobal, ModelValidator;

    public function __construct()
    {
        $this->before();
    }

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
        // get all rules as array from method rules()
        $rules = $this->rules();
        // get default values of attributes
        $defaultAttr = $this->getAllProperties();

        // start validation as save boolean value
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
                    $this->{$attr} = App::$Security->strip_tags($defaultAttr[$attr]); // just set ;)
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
     * Export model values for safe-using in HTML pages.
     * @return $this
     */
    final public function export()
    {
        $properties = get_object_vars($this);
        foreach ($properties as $var => $value) {
            if (Str::startsWith('_', $var)) { // ignore $_var
                continue;
            }
            $this->{$var} = App::$Security->secureHtml($value);
        }
        return $this;
    }


    /**
     * Get all properties for current model in key=>value array
     * @return array|null
     */
    public function getAllProperties()
    {
        $properties = null;
        foreach ($this as $property => $value) {
            if (Str::startsWith('_', $property)) {
                continue;
            }
            $properties[$property] = $value;
        }
        return $properties;
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