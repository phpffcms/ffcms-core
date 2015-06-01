<?php

namespace Ffcms\Core\Arch;

use Ffcms\Core\App;
use Ffcms\Core\Exception\SyntaxException;
use Dflydev\DotAccessData\Data as DotData;
use Ffcms\Core\Helper\Object;
use Ffcms\Core\Helper\String;
use Ffcms\Core\Filter\Native;
use Ffcms\Core\Traits\DynamicGlobal;

class Model
{
    use DynamicGlobal;

    protected $wrongFields;
    protected $formName;

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
     * Set special type for input data. Example: ['avatar' => 'file', 'login' => 'input']
     * @return array
     */
    public function inputTypes()
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

        // prevent warnings
        if (Object::isArray($this->wrongFields) && count($this->wrongFields) > 0) {
            foreach ($this->wrongFields as $property) {
                if (String::contains('.', $property)) { // sounds like dot-separated array property
                    $propertyName = strstr($property, '.', true); // get property name
                    $propertyArray = trim(strstr($property, '.'), '.'); // get dot-based array path

                    $defaultValue = new DotData($default_property); // load default property

                    $dotData = new DotData($this->{$propertyName}); // load local property variable
                    $dotData->set($propertyArray, $defaultValue->get($property)); // set to local prop. variable default value

                    $this->{$propertyName} = $dotData->export(); // export to model
                } else {
                    $this->{$property} = App::$Security->strip_tags($default_property[$property]); // just set ;)
                }
            }
        }

        return $success;
    }

    protected final function validateRecursive($field_name, $filter_name, $filter_argv, $html = false)
    {
        // check if we got it from POST request
        if (App::$Request->getMethod() !== 'POST') {
            return false;
        }

        // check input data type. Maybe file or input (text)
        $inputTypes = $this->inputTypes();
        // sounds like file
        if ($inputTypes[$field_name] === 'file') {
            $field_value = $this->getFile($field_name);
        } else { // sounds like plain post data
            $field_value = $this->getInput($field_name);
            // remove or safe use html
            $field_value = $html ? App::$Security->secureHtml($field_value) : App::$Security->strip_tags($field_value);
        }

        $check = false;
        if (String::contains('::', $filter_name)) { // sounds like a callback
            list($callback_class, $callback_method) = explode('::', $filter_name);
            $callback_class = '\\' . trim($callback_class, '\\');
            if (method_exists($callback_class, $callback_method)) {
                $check = @$callback_class::$callback_method($field_value, $filter_argv); // callback class::method(name, value);
            } else {
                throw new SyntaxException('Filter callback execution "' . $field_name . '" is not exist');
            }
        } elseif (method_exists('Ffcms\Core\Filter\Native', $filter_name)) { // only full namespace\class path based :(
            if ($filter_argv != null) {
                $check = Native::$filter_name($field_value, $filter_argv);
            } else {
                $check = Native::$filter_name($field_value);
            }
        } else {
            throw new SyntaxException('Filter "' . $filter_name . '" is not exist');
        }
        if ($check !== true) { // switch only on fail check.
            $this->wrongFields[] = $field_name;
        } else {
            $field_set_name = $field_name;
            // prevent array-type setting
            if (String::contains('.', $field_set_name)) {
                $field_set_name = strstr($field_set_name, '.', true);
            }
            if (property_exists($this, $field_set_name)) {
                if ($field_name !== $field_set_name) { // array-based property
                    $dot_path = trim(strstr($field_name, '.'), '.');
                    // use dot-data provider to compile output array
                    $dotData = new DotData($this->{$field_set_name});
                    $dotData->set($dot_path, $field_value);
                    // export data from dot-data lib to model property
                    $this->{$field_set_name} = $dotData->export();
                } else { // just single property
                    $this->{$field_name} = $field_value; // refresh model property's from post data
                }
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
            $this->$var = App::$Security->secureHtml($value);
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
            if (Object::isArray($rule[0])) { // 2 or more rules [['field1', 'field2'], 'filter', 'filter_argv']
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

    /**
     * Form default name (used in field building)
     * @return string
     */
    public function getFormName()
    {
        if (null === $this->formName) {
            $cname = get_class($this);
            $this->formName = 'Form' . substr($cname, strrpos($cname, '\\')+1);
        }

        return $this->formName;
    }

    /**
     * Check if form submited
     * @return bool
     */
    public function isPostSubmit()
    {
        if (App::$Request->getMethod() !== 'POST') {
            return false;
        }

        return null !== $this->getInput('submit');
    }

    /**
     * Get input params GET/POST/PUT method
     * @param string $param
     * @return string|null
     */
    public function getInput($param)
    {
        $objName = $this->getFormName();
        if (String::contains('.', $param)) {
            foreach (explode('.', $param) as $item) {
                $objName .= '[' . $item . ']';
            }
        } else {
            $objName .= '[' . $param . ']';
        }
        return App::$Request->get($objName, null, true);
    }

    /**
     * Get uploaded file from user via POST request
     * @param string $param
     * @return \Symfony\Component\HttpFoundation\File\UploadedFile|null
     */
    public function getFile($param)
    {
        $fileName = $this->getFormName();
        if (String::contains('.', $param)) {
            foreach (explode('.', $param) as $obj) {
                $fileName .= '[' . $obj . ']';
            }
        } else {
            $fileName .= '[' . $param . ']';
        }
        return App::$Request->files->get($fileName, null, true);
    }

    /**
     * Get form after-validation wrong fields name
     * @return array|null
     */
    public function getWrongFields()
    {
        return $this->wrongFields;
    }

}