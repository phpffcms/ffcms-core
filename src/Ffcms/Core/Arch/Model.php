<?php

namespace Ffcms\Core\Arch;

use Core\App;
use Core\Helper\Object;
use Core\Helper\String;
use Core\Filter\Native;

abstract class Model extends \Core\Arch\Constructors\Magic
{
    public $wrongFields = [];

    public final function __construct()
    {
        $this->before();
    }

    public function before() {}

    /**
     * Get label value by variable name
     * @param string $param
     * @return mixed
     */
    public final function getLabel($param)
    {
        $labels = $this->setLabels();

        return ($labels[$param] == null ? String::splitCamelCase($param) : $labels[$param]);
    }

    /**
     * Set attribute labels for model variables
     * @return array
     */
    public function setLabels()
    {
        return [];
    }

    /**
     * Validate defined rules in app
     * @return bool
     */
    public final function validateRules()
    {
        $rules = $this->setRules();
        $success = true; // set is success

        $default_property = [];
        $reflection = new \ReflectionClass($this);
        foreach ($reflection->getProperties(\ReflectionProperty::IS_PUBLIC) as $obj) {
            $default_property[$obj->getName()] = $obj->getValue($this);
        }

        foreach ($rules as $rule) {
            // 0 = name, 1 = filter name, 2 = filter value
            if ($rule[0] === null || $rule[1] === null) {
                continue;
            }
            $validate = false;
            if (Object::isArray($rule[0])) {
                $validate_foreach = true;
                foreach ($rule[0] as $field_name) {
                    if (!$this->validateRecursive($field_name, $rule[1], $rule[2], $rule[3])) {
                        $validate_foreach = false;
                    }
                }
                $validate = $validate_foreach;
            } else {
                $validate = $this->validateRecursive($rule[0], $rule[1], $rule[2], $rule[3]);
            }
            if ($validate === false) {
                $success = false;
            }
        }

        foreach ($this->wrongFields as $property) {
            $this->{$property} = App::$Security->strip_tags($default_property[$property]);
        }

        return $success;
    }

    protected final function validateRecursive($field_name, $filter_name, $filter_argv, $html = false)
    {
        $field_value = App::$Request->post($field_name);
        if (!$html && !Object::isArray($field_value)) {
            $field_value = App::$Security->strip_tags($field_value);
        } else {
            $field_value = App::$Security->purifier()->purify($field_value);
        }

        $check = false;
        try {
            if (String::contains('::', $filter_name)) { // sounds like a callback
                list($callback_class, $callback_method) = explode('::', $filter_name);
                $callback_class = '\\' . trim($callback_class, '\\');
                if (method_exists($callback_class, $callback_method)) {
                    $check = @$callback_class::$callback_method($field_value, $filter_argv); // callback class::method(name, value);
                } else {
                    throw new \Exception('Filter callback execution "' . $field_name . '" is not exist');
                }
            } elseif (method_exists('\Core\Filter\Native', $filter_name)) { // only full namespace\class path based :(
                if ($filter_argv != null) {
                    $check = Native::$filter_name($field_value, $filter_argv);
                } else {
                    $check = Native::$filter_name($field_value);
                }
            } else {
                throw new \Exception('Filter "' . $filter_name . '" is not exist');
            }
        } catch (\Exception $e) {
            App::$Debug->bar->getCollector('exceptions')->addException($e);
        }
        if ($check !== true) { // switch only on fail check.
            $this->wrongFields[] = $field_name;
        } else {
            if (property_exists($this, $field_name)) {
                $this->{$field_name} = $field_value; // refresh model property's from post data
            }
        }
        return $check;
    }

    /**
     * Set model validation rules
     * @return array
     */
    public function setRules()
    {
        return [];
    }

    /**
     * Export model values for safe-using in HTML pages.
     * @return $this
     */
    public final function export()
    {
        $properties = get_object_vars($this);
        foreach ($properties as $var => $value) {
            if (String::startsWith('_', $var)) { // ignore $_var
                continue;
            }
            $this->$var = \App::$Security->purifier()->purify($value);
        }
        return $this;
    }

    /**
     * Get validation rules for field
     * @param string $field
     * @return array
     */
    public final function getValidationRule($field)
    {
        $rules = $this->setRules();
        $response = [];

        foreach ($rules as $rule) {
            if(is_array($rule[0])) { // 2 or more rules [['field1', 'field2'], 'filter', 'filter_argv']
                foreach($rule[0] as $tfield) {
                    if($tfield == $field) {
                        $response[$rule[1]] = $rule[2]; // ['min_length' => 1, 'required' => null]
                    }
                }
            } else { // 1 rule ['field1', 'filter', 'filter_argv']
                if($rule[0] === $field) {
                    $response[$rule[1]] = $rule[2];
                }
            }
        }

        return $response;
    }
}