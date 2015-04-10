<?php

namespace Ffcms\Core\Arch;

use Core\App;
use Core\Helper\Object;
use Core\Helper\String;
use Core\Filter\Native;

abstract class Model extends \Core\Arch\Constructors\Magic
{

    public final function construct()
    {
        $this->before();
    }

    public function before()
    {
    }

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

        $unset_property = [];
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
            $save_html = ($rule[3] === 'html');
            $rule_value = App::$Request->post($rule[0]);
            if (!$save_html && !Object::isArray($rule_value)) {
                $rule_value = App::$Security->strip_tags($rule_value);
            } else {
                $rule_value = App::$Security->purifier()->purify($rule_value);
            }
            $rule_filter = $rule[1];
            $rule_filter_argv = $rule[2];

            try {
                if (method_exists('\Core\Filter\Native', $rule_filter)) { // only full namespace\class path based :(
                    $check = false;
                    if ($rule_filter_argv != null) {
                        $check = Native::$rule_filter($rule_value, $rule_filter_argv);
                    } else {
                        $check = Native::$rule_filter($rule_value);
                    }
                    if ($check === false) { // switch only on fail check.
                        $success = false;
                        $unset_property[] = $rule[0];
                    } else {
                        if (property_exists($this, $rule[0])) {
                            $this->{$rule[0]} = $rule_value; // refresh model property's from post data
                        }
                    }
                } else {
                    throw new \Exception('Filter "' . $rule_filter . '" is not exist');
                }
            } catch (\Exception $e) {
                App::$Debug->bar->getCollector('exceptions')->addException($e);
                $success = false;
            }
        }

        foreach ($unset_property as $property) {
            $this->{$property} = App::$Security->strip_tags($default_property[$property]);
        }

        return $success;
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
            if (String::startsWith('_', $var)) // ignore $_var
                continue;
            $this->$var = \App::$Security->purifier()->purify($value);
        }
        return $this;
    }

    protected final function reservedNames()
    {
        return [
            'labels'
        ];
    }


}