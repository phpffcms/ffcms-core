<?php

namespace Ffcms\Core\Arch;

use Ffcms\Core\App;
use Ffcms\Core\Exception\SyntaxException;
use Dflydev\DotAccessData\Data as DotData;
use Ffcms\Core\Helper\Type\Obj;
use Ffcms\Core\Helper\Type\Str;
use Ffcms\Core\Filter\Native;
use Ffcms\Core\Traits\DynamicGlobal;

class Model
{
    use DynamicGlobal;

    protected $_wrongFields;
    protected $_formName;

    private $_sendMethod = 'POST';

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
     * Set model send method type. Allowed: post, get
     * @param string $acceptMethod
     */
    final public function setSubmitMethod($acceptMethod)
    {
        $this->_sendMethod = strtoupper($acceptMethod);
    }

    /**
     * Get model submit method. Allowed: post, get
     * @return string
     */
    final public function getSubmitMethod()
    {
        return $this->_sendMethod;
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
     * @throws SyntaxException
     */
    final public function validate()
    {
        $rules = $this->rules();
        $success = true; // set is success

        $default_property = $this->getAllProperties();

        foreach ($rules as $rule) {
            // 0 = name, 1 = filter name, 2 = filter value
            if ($rule[0] === null || $rule[1] === null) {
                continue;
            }
            if (Obj::isArray($rule[0])) {
                $validate_foreach = true;
                foreach ($rule[0] as $field_name) {
                    if (!$this->validateRecursive($field_name, $rule[1], $rule[2], $rule[3], $rule[4])) {
                        $validate_foreach = false;
                    }
                }
                $validate = $validate_foreach;
            } else {
                $validate = $this->validateRecursive($rule[0], $rule[1], $rule[2], $rule[3], $rule[4]);
            }
            if ($validate === false) {
                $success = false;
            }
        }

        // prevent warnings
        if (Obj::isArray($this->_wrongFields) && count($this->_wrongFields) > 0) {
            foreach ($this->_wrongFields as $property) {
                if (Str::contains('.', $property)) { // sounds like dot-separated array property
                    $propertyName = strstr($property, '.', true); // get property name
                    $propertyArray = trim(strstr($property, '.'), '.'); // get dot-based array path

                    $defaultValue = new DotData($default_property); // load default property

                    $dotData = new DotData($this->{$propertyName}); // load local property variable
                    $dotData->set($propertyArray, $defaultValue->get($property)); // set to local prop. variable default value

                    $this->{$propertyName} = $dotData->export(); // export to model
                } else {
                    $this->{$property} = App::$Security->strip_tags($default_property[$property]); // just set ;)
                }
                // add message about wrong fields. $property is not
                $propertyLabel = $property;
                if ($this->getLabel($property) !== null) {
                    $propertyLabel = $this->getLabel($property);
                }
                App::$Session->getFlashBag()->add('warning', __('Field "%field%" is incorrect', ['field' => $propertyLabel]));
            }
        }

        return $success;
    }

    /**
     * Recursive validation of rules
     * @param $field_name
     * @param $filter_name
     * @param $filter_argv
     * @param bool $html
     * @return bool
     * @throws SyntaxException
     */
    final protected function validateRecursive($field_name, $filter_name, $filter_argv, $html = false, $secure = false)
    {
        // check if we got it from form defined request method
        if (App::$Request->getMethod() !== $this->_sendMethod) {
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
            if ($html === false) {
                $field_value = App::$Security->strip_tags($field_value);
            } else {
                if ($secure !== true) {
                    $field_value = App::$Security->secureHtml($field_value);
                }
            }
        }

        $check = false;
        // maybe no filter required?
        if ($filter_name === 'used') {
            $check = true;
        } elseif (Str::contains('::', $filter_name)) { // sounds like a callback class::method::method
            // string to array via delimiter ::
            $callbackArray = explode('::', $filter_name);
            // first item is a class name
            $class = array_shift($callbackArray);
            // last item its a function
            $method = array_pop($callbackArray);
            // left any items? maybe post-static callbacks?
            if (count($callbackArray) > 0) {
                foreach ($callbackArray as $obj) {
                    if (Str::startsWith('$', $obj) && property_exists($class, ltrim($obj, '$'))) { // sounds like a variable
                        $obj = ltrim($obj, '$'); // trim variable symbol '$'
                        $class = $class::$$obj; // make magic :)
                    } elseif (method_exists($class, $obj)) { // maybe its a function?
                        $class = $class::$obj; // call function
                    } else {
                        throw new SyntaxException('Filter callback execution failed: ' . $filter_name);
                    }

                }
            }

            // check is endpoint method exist
            if (method_exists($class, $method)) {
                $check = @$class::$method($field_value, $filter_argv);
            } else {
                throw new SyntaxException('Filter callback execution failed: ' . $filter_name);
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
            $this->_wrongFields[] = $field_name;
        } else {
            $field_set_name = $field_name;
            // prevent array-type setting
            if (Str::contains('.', $field_set_name)) {
                $field_set_name = strstr($field_set_name, '.', true);
            }
            if (property_exists($this, $field_set_name)) {
                if ($field_name !== $field_set_name) { // array-based property
                    $dot_path = trim(strstr($field_name, '.'), '.');
                    // prevent throws any exceptions for null and false objects
                    if (!Obj::isArray($this->{$field_set_name})) {
                        $this->{$field_set_name} = [];
                    }
                    // use dot-data provider to compile output array
                    $dotData = new DotData($this->{$field_set_name});
                    $dotData->set($dot_path, $field_value); // todo: check me!!! bug here
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
     * Set of model validation rules
     * @return array
     */
    public function rules()
    {
        return [];
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

    /**
     * Form default name (used in field building)
     * @return string
     */
    public function getFormName()
    {
        if (null === $this->_formName) {
            $cname = get_class($this);
            $this->_formName = substr($cname, strrpos($cname, '\\') + 1);
        }

        return $this->_formName;
    }

    /**
     * Check if model get POST-based request as submit of SEND data
     * @return bool
     */
    final public function send()
    {
        if (App::$Request->getMethod() !== $this->_sendMethod) {
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
        if (Str::contains('.', $param)) {
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
        if (Str::contains('.', $param)) {
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
        return $this->_wrongFields;
    }

}