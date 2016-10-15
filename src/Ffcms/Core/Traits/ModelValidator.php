<?php

namespace Ffcms\Core\Traits;


use Dflydev\DotAccessData\Data as DotData;
use Ffcms\Core\App;
use Ffcms\Core\Exception\SyntaxException;
use Ffcms\Core\Filter\Native;
use Ffcms\Core\Helper\Type\Obj;
use Ffcms\Core\Helper\Type\Str;

/**
 * Class ModelValidator. Extended realisation of model field validation
 * @package Ffcms\Core\Traits
 */
trait ModelValidator
{
    protected $_badAttr;
    protected $_sendMethod = 'POST';

    protected $_formName;

    public $_tokenRequired = false;
    protected $_tokenOk = true;

    /**
     * Initialize validator. Set csrf protection token from request data if available.
     * @param bool $csrf
     */
    public function initialize($csrf = false)
    {
        $this->_tokenRequired = $csrf;
        if ($csrf === true) {
            // get current token value from session
            $currentToken = App::$Session->get('_csrf_token', false);
            // set new token value to session
            $newToken = Str::randomLatinNumeric(mt_rand(32, 64));
            App::$Session->set('_csrf_token', $newToken);
            // if request is submited for this model - try to validate input data
            if ($this->send()) {
                // token is wrong - update bool state
                if ($currentToken !== $this->getRequest('_csrf_token', 'all')) {
                    $this->_tokenOk = false;
                }
            }
            // set token data to display
            $this->_csrf_token = $newToken;
        }
    }

    /**
     * Start validation for defined fields in rules() model method.
     * @param array|null $rules
     * @return bool
     * @throws SyntaxException
     */
    public function runValidate(array $rules = null)
    {
        // skip validation on empty rules
        if ($rules === null || !Obj::isArray($rules)) {
            return true;
        }

        $success = true;
        // list each rule as single one
        foreach ($rules as $rule) {
            // 0 = field (property) name, 1 = filter name, 2 = filter value
            if ($rule[0] === null || $rule[1] === null) {
                continue;
            }

            // check if target field defined as array and make recursive validation
            if (Obj::isArray($rule[0])) {
                $validate_foreach = true;
                foreach ($rule[0] as $field_name) {
                    // end false condition
                    if (!$this->validateRecursive($field_name, $rule[1], $rule[2])) {
                        $validate_foreach = false;
                    }
                }
                // assign total
                $validate = $validate_foreach;
            } else {
                $validate = $this->validateRecursive($rule[0], $rule[1], $rule[2]);
            }

            // do not change condition on "true" check's (end-false-condition)
            if ($validate === false) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * Try to recursive validate field by defined rules and set result to model properties if validation is successful passed
     * @param string|array $field_name
     * @param string $filter_name
     * @param mixed $filter_argv
     * @return bool
     * @throws SyntaxException
     */
    public function validateRecursive($field_name, $filter_name, $filter_argv = null)
    {
        // check if we got it from form defined request method
        if (App::$Request->getMethod() !== $this->_sendMethod) {
            return false;
        }

        // get field value from user input data
        $field_value = $this->getFieldValue($field_name);

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
                    $dotData->set($dot_path, $field_value); // todo: check me!!! Here can be bug of fail parsing dots and passing path-value
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
     * Get field value from input POST/GET/AJAX data with defined security level (html - safe html, !secure = fully unescaped)
     * @param string $field_name
     * @return array|null|string
     * @throws \InvalidArgumentException
     */
    private function getFieldValue($field_name)
    {
        // get type of input data (where we must look it up)
        $sources = [];
        $types = [];
        $inputType = Str::lowerCase($this->_sendMethod);
        $filterType = 'text';
        // check input data type. Maybe file or input (text)
        if (method_exists($this, 'sources')) {
            $sources = $this->sources();
        }
        if (method_exists($this, 'types')) {
            $types = $this->types();
        }
        // validate sources for current field
        if (Obj::isArray($sources) && array_key_exists($field_name, $sources)) {
            $inputType = Str::lowerCase($sources[$field_name]);
        }
        if (Obj::isArray($types) && array_key_exists($field_name, $types)) {
            $filterType = Str::lowerCase($types[$field_name]);
        }

        // get clear field value
        $field_value = $this->getRequest($field_name, $inputType);

        // apply security filter for input data
        if ($inputType !== 'file') {
            if ($filterType === 'html') {
                $field_value = App::$Security->secureHtml($field_value);
            } elseif ($filterType !== '!secure') {
                $field_value = App::$Security->strip_tags($field_value);
            }
        }

        return $field_value;
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
        $this->_sendMethod = Str::upperCase($acceptMethod);
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
     * @throws \InvalidArgumentException
     */
    final public function send()
    {
        if (!Str::equalIgnoreCase($this->_sendMethod, App::$Request->getMethod())) {
            return false;
        }

        return $this->getRequest('submit', $this->_sendMethod) !== null;
    }

    /**
     * Form default name (used in field building)
     * @return string
     */
    public function getFormName()
    {
        if ($this->_formName === null) {
            $cname = get_class($this);
            $this->_formName = substr($cname, strrpos($cname, '\\') + 1);
        }

        return $this->_formName;
    }

    /**
     * @deprecated
     * Get input params GET/POST/PUT method
     * @param string $param
     * @return string|null
     */
    public function getInput($param)
    {
        return $this->getRequest($param, $this->_sendMethod);
    }

    /**
     * @deprecated
     * Get uploaded file from user via POST request
     * @param string $param
     * @return \Symfony\Component\HttpFoundation\File\UploadedFile|null
     */
    public function getFile($param)
    {
        return $this->getRequest($param, 'file');
    }

    /**
     * Get input param for current model form based on param name and request method
     * @param string $param
     * @param string|null $method
     * @return string|null|array
     * @throws \InvalidArgumentException
     */
    public function getRequest($param, $method = null)
    {
        // build param query for http foundation request
        $paramQuery = $this->getFormName();
        if (Str::contains('.', $param)) {
            foreach (explode('.', $param) as $item) {
                $paramQuery .= '[' . $item . ']';
            }
        } else {
            $paramQuery .= '[' . $param . ']';
        }

        if ($method === null) {
            $method = $this->_sendMethod;
        }

        // get request based on method and param query
        $method = Str::lowerCase($method);
        switch ($method) {
            case 'get':
                return App::$Request->query->get($paramQuery, null, true);
            case 'post':
                return App::$Request->request->get($paramQuery, null, true);
            case 'file':
                return App::$Request->files->get($paramQuery, null, true);
            default:
                return App::$Request->get($paramQuery, null, true);

        }
    }
}