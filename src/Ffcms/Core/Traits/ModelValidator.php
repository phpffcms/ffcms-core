<?php

namespace Ffcms\Core\Traits;


use Ffcms\Core\App;
use Ffcms\Core\Exception\SyntaxException;
use Ffcms\Core\Filter\Native;
use Ffcms\Core\Helper\Type\Obj;
use Ffcms\Core\Helper\Type\Str;
use Dflydev\DotAccessData\Data as DotData;

trait ModelValidator
{
    protected $_badAttr;
    protected $_sendMethod = 'POST';

    protected $_formName;

    public function runValidate(array $rules = null)
    {
        // skip validation on empty rules
        if ($rules === null) {
            return true;
        }

        $success = true;
        // each
        foreach ($rules as $rule) {
            // 0 = name, 1 = filter name, 2 = filter value
            if ($rule[0] === null || $rule[1] === null) {
                continue;
            }

            if (Obj::isArray($rule[0])) {
                $validate_foreach = true;
                foreach ($rule[0] as $field_name) {
                    // end false condition
                    if (!$this->validateRecursive($field_name, $rule[1], $rule[2], $rule[3], $rule[4])) {
                        $validate_foreach = false;
                    }
                }
                // assign total
                $validate = $validate_foreach;
            } else {
                $validate = $this->validateRecursive($rule[0], $rule[1], $rule[2], $rule[3], $rule[4]);
            }

            // do not change condition on "true" check's (end-false-condition)
            if ($validate === false) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * @param $field_name
     * @param $filter_name
     * @param $filter_argv
     * @param bool $html
     * @param bool $secure
     * @return bool
     * @throws SyntaxException
     */
    public function validateRecursive($field_name, $filter_name, $filter_argv, $html = false, $secure = false)
    {
        // check if we got it from form defined request method
        if (App::$Request->getMethod() !== $this->_sendMethod) {
            return false;
        }

        $inputTypes = [];
        // check input data type. Maybe file or input (text)
        if (method_exists($this, 'inputTypes')) {
            $inputTypes = $this->inputTypes();
        }

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
            $this->_badAttr[] = $field_name;
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
     * Get fail validation attributes as array if exist
     * @return null|array
     */
    public function getBadAttribute()
    {
        return $this->_badAttr;
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
     * Set special type for input data. Example: ['avatar' => 'file', 'login' => 'input']
     * @return array
     */
    public function inputTypes()
    {
        return [];
    }
}