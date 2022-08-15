<?php

namespace Ffcms\Core\Traits;

use Dflydev\DotAccessData\Data as DotData;
use Ffcms\Core\App;
use Ffcms\Core\Exception\SyntaxException;
use Ffcms\Core\Helper\Crypt;
use Ffcms\Core\Helper\ModelFilters;
use Ffcms\Core\Helper\Type\Any;
use Ffcms\Core\Helper\Type\Arr;
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
        if ($csrf) {
            // get current token value from session
            $currentToken = App::$Session->get('_csrf_token', false);
            // set new token value to session
            $newToken = Crypt::randomString(mt_rand(32, 64));
            App::$Session->set('_csrf_token', $newToken);
            // if request is submited for this model - try to validate input data
            if ($this->send()) {
                // token is wrong - update bool state
                if ($currentToken !== $this->getRequest('_csrf_token')) {
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
        if ($rules === null) {
            return true;
        }

        $success = true;
        // list each rule as single one
        foreach ($rules as $rule) {
            // 0 = field (property) name, 1 = filter name, 2 = filter value
            if ($rule[0] === null || $rule[1] === null) {
                continue;
            }

            $propertyName = $rule[0];
            $validationRule = $rule[1];
            $validationValue = null;
            if (isset($rule[2])) {
                $validationValue = $rule[2];
            }

            // check if target field defined as array and make recursive validation
            if (Any::isArray($propertyName)) {
                $cumulateValidation = true;
                foreach ($propertyName as $attrNme) {
                    // end false condition
                    if (!$this->validateRecursive($attrNme, $validationRule, $validationValue)) {
                        $cumulateValidation = false;
                    }
                }
                // assign total
                $validate = $cumulateValidation;
            } else {
                $validate = $this->validateRecursive($propertyName, $validationRule, $validationValue);
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
     * @param string|array $propertyName
     * @param string $filterName
     * @param mixed $filterArgs
     * @return bool
     * @throws SyntaxException
     */
    public function validateRecursive($propertyName, $filterName, $filterArgs = null)
    {
        // check if we got it from form defined request method
        if (App::$Request->getMethod() !== $this->_sendMethod) {
            return false;
        }

        // get field value from user input data
        $fieldValue = $this->getFieldValue($propertyName);

        $check = false;
        // maybe no filter required?
        if ($filterName === 'used') {
            $check = true;
        } elseif (Str::contains('::', $filterName)) { // sounds like a callback class::method::method
            // string to array via delimiter ::
            $callbackArray = explode('::', $filterName);
            // first item is a class name
            $class = array_shift($callbackArray);
            // last item its a function
            $method = array_pop($callbackArray);
            // left any items? maybe post-static callbacks?
            if (count($callbackArray) > 0) {
                foreach ($callbackArray as $obj) {
                    if (Str::startsWith('$', $obj) && property_exists($class, ltrim($obj, '$'))) { // sounds like a variable
                        $obj = ltrim($obj, '$'); // trim variable symbol '$'
                        $class = $class::${$obj}; // make magic :)
                    } elseif (method_exists($class, $obj)) { // maybe its a function?
                        $class = $class::$obj; // call function
                    } else {
                        throw new SyntaxException('Filter callback execution failed: ' . $filterName);
                    }
                }
            }

            // check is endpoint method exist
            if (method_exists($class, $method)) {
                $check = @$class::$method($fieldValue, $filterArgs);
            } else {
                throw new SyntaxException('Filter callback execution failed: ' . $filterName);
            }
        } elseif (method_exists('Ffcms\Core\Helper\ModelFilters', $filterName)) { // only full namespace\class path based :(
            if ($filterArgs != null) {
                $check = ModelFilters::$filterName($fieldValue, $filterArgs);
            } else {
                $check = ModelFilters::$filterName($fieldValue);
            }
        } else {
            throw new SyntaxException('Filter "' . $filterName . '" is not exist');
        }

        // if one from all validation tests is fail - mark as incorrect attribute
        if ($check !== true) {
            $this->_badAttr[] = $propertyName;
            if (App::$Debug) {
                App::$Debug->addMessage('Validation failed. Property: ' . $propertyName . ', filter: ' . $filterName, 'warning');
            }
        } else {
            $field_set_name = $propertyName;
            // prevent array-type setting
            if (Str::contains('.', $field_set_name)) {
                $field_set_name = strstr($field_set_name, '.', true);
            }
            if (property_exists($this, $field_set_name)) {
                if ($propertyName !== $field_set_name) { // array-based property
                    $dot_path = trim(strstr($propertyName, '.'), '.');
                    // prevent throws any exceptions for null and false objects
                    if (!Any::isArray($this->{$field_set_name})) {
                        $this->{$field_set_name} = [];
                    }

                    // use dot-data provider to compile output array
                    $dotData = new DotData($this->{$field_set_name});
                    $dotData->set($dot_path, $fieldValue); // todo: check me!!! Here can be bug of fail parsing dots and passing path-value
                    // export data from dot-data lib to model property
                    $this->{$field_set_name} = $dotData->export();
                } else { // just single property
                    $this->{$propertyName} = $fieldValue; // refresh model property's from post data
                }
            }
        }
        return $check;
    }

    /**
     * Get field value from input POST/GET/AJAX data with defined security level (html - safe html, !secure = fully unescaped)
     * @param string $propertyName
     * @return array|null|string
     * @throws \InvalidArgumentException
     */
    private function getFieldValue($propertyName)
    {
        // get type of input data (where we must look it up)
        $inputType = Str::lowerCase($this->_sendMethod);
        $filterType = 'text';
        // get declared field sources and types
        $sources = $this->sources();
        $types = $this->types();
        // validate sources for current field
        if (array_key_exists($propertyName, $sources)) {
            $inputType = Str::lowerCase($sources[$propertyName]);
        }

        // check if field is array-nested element by dots and use first element as general
        $filterField = $propertyName;
        if (array_key_exists($filterField, $types)) {
            $filterType = Str::lowerCase($types[$filterField]);
        }

        // get clear field value
        $propertyValue = $this->getRequest($propertyName, $inputType);

        // apply security filter for input data
        if ($inputType !== 'file') {
            if ($filterType === 'html') {
                $propertyValue = App::$Security->secureHtml($propertyValue);
            } elseif ($filterType !== '!secure') {
                $propertyValue = App::$Security->strip_tags($propertyValue);
            }
        }

        return $propertyValue;
    }

    /**
     * Get fail validation attributes as array if exist
     * @return null|array
     */
    public function getBadAttributes(): ?array
    {
        return $this->_badAttr;
    }

    /**
     * Set model send method type. Allowed: post, get
     * @param string $acceptMethod
     */
    final public function setSubmitMethod($acceptMethod): void
    {
        $this->_sendMethod = Str::upperCase($acceptMethod);
    }

    /**
     * Get model submit method. Allowed: post, get
     * @return string
     */
    final public function getSubmitMethod(): ?string
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
     * Get input value based on param path and request method
     * @param string $param
     * @param string|null $method
     * @return mixed
     */
    public function getRequest($param, $method = null)
    {
        if ($method === null) {
            $method = $this->_sendMethod;
        }

        $form = $this->getFormName();
        $request = false;
        $method = Str::lowerCase($method);
        // get root request as array or string
        switch ($method) {
            case 'get':
                $request = App::$Request->query->get($form, null);
                break;
            case 'post':
                $request = App::$Request->request->get($form, null);
                break;
            case 'file':
                $request = App::$Request->files->get($form, null);
                break;
            default:
                $request = App::$Request->get($form, null);
                break;
        }

        if (!$request || !is_array($request)) {
            return null;
        }

        $response = null;
        // param is a dot-separated array type
        if (Str::contains('.', $param)) {
            $response = $request;
            foreach (explode('.', $param) as $path) {
                if ($response !== null && !array_key_exists($path, $response)) {
                    return null;
                }
                // find deep array nesting offset
                $response = $response[$path];
            }
        } else {
            $response = $request[$param];
        }

        return $response;
    }
}
