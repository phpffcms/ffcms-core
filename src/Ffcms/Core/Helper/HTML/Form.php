<?php

namespace Ffcms\Core\Helper\HTML;

use Ffcms\Core\App;
use Ffcms\Core\Exception\NativeException;
use Ffcms\Core\Exception\SyntaxException;
use Ffcms\Core\Helper\HTML\System\NativeGenerator;
use Ffcms\Core\Helper\Security;
use Ffcms\Core\Helper\Type\Arr;
use Ffcms\Core\Helper\FileSystem\File;
use Ffcms\Core\Helper\Type\Obj;
use Ffcms\Core\Helper\Type\Str;
use Ffcms\Core\Arch\Model;
use Ffcms\Core\Helper\HTML\Form\Constructor;

/**
 * Class Form. Simple HTML model generator for fast form building.
 */
class Form extends NativeGenerator
{
    /** @var array */
    private static $structLayer = [
        'base' => 'native/form/base_layer',
        'checkbox' => 'native/form/checkbox_layer',
        'checkboxes' => 'native/form/checkboxes_layer',
        'radio' => 'native/form/radio_layer',
        'jsnotify' => 'native/form/jsnotify'
    ];

    /** @var string */
    private $name;
    private $formProperty = [];
    /** @var Model */
    private $model;


    /**
     * Form constructor. Build form based on model properties
     * @param Model $model
     * @param array|null $property
     * @param array|null $layerFiles
     * @throws SyntaxException
     */
    public function __construct($model, array $property = null, array $layerFiles = null)
    {
        // prevent white-screen locks when model is not passed or passed wrong
        if (!$model instanceof Model) {
            throw new SyntaxException('Bad model type passed in form builder. Check for init: new Form()');
        }

        $this->model = $model;
        $this->name = $model->getFormName();
        
        // check if passed custom layer file
        if (Obj::isArray($layerFiles) && count($layerFiles) > 0) {
            foreach (array_keys(static::$structLayer) as $type) {
                if (isset($layerFiles[$type]) && Obj::isString($layerFiles[$type])) {
                    static::$structLayer[$type] = $layerFiles[$type];
                }
            }
        }
        // set model submit method
        $property['method'] = $this->model->getSubmitMethod();

        $property['id'] = $this->name; // define form id
        // if action is not defined - define it
        if (Str::likeEmpty($property['action'])) {
            $property['action'] = App::$Request->getFullUrl();
        }

        // set property global for this form
        $this->formProperty = $property;
    }

    /**
     * Open form tag with prepared properties
     * @return string
     */
    public function start()
    {
        return '<form' . self::applyProperty($this->formProperty) . '>';
    }

    /**
     * Build csrf token key and return input[type=hidden] tag
     * @param string $name
     * @return string
     */
    public function csrfToken($name)
    {
        // get token from session data (will be destoyed after form validation)
        $token = App::$Session->get($name, false);
        // if no token data in session - generate it and save in session data
        if ($token === false) {
            $token = Str::randomLatinNumeric(mt_rand(32, 64));
            App::$Session->set($name, $token);
        }

        // build input[type=hidden] with token value
        return $this->field($name, 'hidden', ['value' => $token]);
    }

    /**
     * Display form field. Allowed type: text, password, textarea, checkbox, select, checkboxes, file, captcha, email, hidden
     * @param $object
     * @param $type
     * @param null|array $property
     * @param null|string $helper
     * @param null|string $layerFile
     * @return null|string
     * @throws NativeException
     * @throws SyntaxException
     */
    public function field($object, $type, $property = null, $helper = null, $layerFile = null)
    {
        if ($this->model === null) {
            if (App::$Debug !== null) {
                App::$Debug->addMessage('Form model is not defined for field name: [' . strip_tags($object) . ']');
            }
            return null;
        }

        // can be dots separated object
        $propertyName = $object;
        if (Str::contains('.', $propertyName)) {
            $propertyName = strstr($propertyName, '.', true);
        }

        // check if model contains current tag name as property
        if (!property_exists($this->model, $propertyName)) {
            if (App::$Debug !== null) {
                App::$Debug->addMessage('Form field ["' . $object . '"] is not defined in model: [' . get_class($this->model) . ']', 'error');
            }
            return null;
        }
        
        // prepare layer template file path
        if ($layerFile === null) {
            switch ($type) {
                case 'checkbox':
                    $layerFile = static::$structLayer['checkbox'];
                    break;
                case 'radio':
                    $layerFile = static::$structLayer['radio'];
                    break;
                default:
                    $layerFile = static::$structLayer['base'];
                    break;
            }
        }
        
        // prepare labels text and label "for" attr 
        $labelFor = $this->name . '-' . $propertyName;
        $labelText = $this->model->getLabel($object);
        $itemValue = $this->model->{$propertyName};
        // sounds like a dot-separated $object
        if ($propertyName !== $object) {
            $nesting = trim(strstr($object, '.'), '.');
            $labelFor .= '-' . Str::replace('.', '-', $nesting);
            $itemValue = Arr::getByPath($nesting, $itemValue);
        }

        // initialize form fields constructor and build output dom html value
        $constructor = new Constructor($this->model, $this->name, $type);
        $elementDOM = $constructor->makeTag($object, $itemValue, $property);
        
        // if item is hidden - return tag without assign of global template
        if ($type === 'hidden') {
            return $elementDOM;
        }
        
        // render output viewer
        return App::$View->render($layerFile, [
            'name' => $labelFor,
            'label' => $labelText,
            'item' => $elementDOM,
            'help' => self::nohtml($helper)
        ]);
    }

    /**
     * Display submit button for current form
     * @param string $title
     * @param array $property
     * @return string
     */
    public function submitButton($title, array $property = [])
    {
        $property['type'] = 'submit';
        $property['name'] = $this->name . '[submit]';
        $property['value'] = $title;
        return self::buildSingleTag('input', $property);
    }

    /**
     * Finish current form.
     * @param bool $validate
     * @return string
     */
    public function finish($validate = true)
    {
        // pre-validate form fields based on model rules and jquery.validation
        if ($validate) {
            App::$Alias->addPlainCode('js', '$().ready(function() { $("#' . $this->name . '").validate(); });');
            App::$Alias->setCustomLibrary('js', '/vendor/bower/jquery-validation/dist/jquery.validate.min.js');
            if (App::$Request->getLanguage() !== 'en') {
                $localeFile = '/vendor/bower/jquery-validation/src/localization/messages_' . App::$Request->getLanguage() . '.js';
                if (File::exist($localeFile)) {
                    App::$Alias->setCustomLibrary('js', $localeFile);
                }
            }
            // if model is not empty - add js error color notification
            if ($this->model !== null) {
                $badAttr = $this->model->getBadAttribute();
                $formName = $this->model->getFormName();
                if (Obj::isArray($badAttr) && count($badAttr) > 0) {
                    foreach ($badAttr as $attr) {
                        $itemId = $formName . '-' . $attr;
                        $render = App::$View->render(static::$structLayer['jsnotify'], ['itemId' => $itemId]);
                        App::$Alias->addPlainCode('js', $render);
                    }
                }
            }
        }
        return '</form>';
    }
}