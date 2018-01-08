<?php

namespace Ffcms\Core\Helper\HTML;

use Ffcms\Core\App;
use Ffcms\Core\Arch\Model;
use Ffcms\Core\Exception\NativeException;
use Ffcms\Core\Exception\SyntaxException;
use Ffcms\Core\Helper\FileSystem\File;
use Ffcms\Core\Helper\HTML\Form\Constructor;
use Ffcms\Core\Helper\HTML\Form\FieldSelector;
use Ffcms\Core\Helper\HTML\System\NativeGenerator;
use Ffcms\Core\Helper\Type\Any;
use Ffcms\Core\Helper\Type\Arr;
use Ffcms\Core\Helper\Type\Obj;
use Ffcms\Core\Helper\Type\Str;

/**
 * Class Form. Simple HTML model generator for fast form building.
 * @package Ffcms\Core\Helper\HTML
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

    /** @var FieldSelector */
    public $field;


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
        if (Any::isArray($layerFiles) && count($layerFiles) > 0) {
            foreach (array_keys(static::$structLayer) as $type) {
                if (isset($layerFiles[$type]) && Any::isStr($layerFiles[$type])) {
                    static::$structLayer[$type] = $layerFiles[$type];
                }
            }
        }
        // initialize field selector helper
        $this->field = new FieldSelector($model, static::$structLayer);

        // set model submit method
        $property['method'] = $this->model->getSubmitMethod();

        $property['id'] = $this->name; // define form id
        // if action is not defined - define it
        if (!$property['action']) {
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
        $form = self::buildSingleTag('form', $this->formProperty, false);
        if ($this->model->_tokenRequired) {
            $form .= PHP_EOL . $this->field->hidden('_csrf_token', ['value' => $this->model->_csrf_token]);
        }

        return $form;
    }

    /**
     * Use $this->field->type() instead. Deprecated!
     * @param string $object
     * @param string $type
     * @param null|array $property
     * @param null|string $helper
     * @param null|string $layerFile
     * @return null|string
     * @deprecated
     */
    public function field(string $object, string $type, ?array $property = null, ?string $helper = null, ?string $layerFile = null)
    {
        $response = null;
        try {
            $response = $this->field->{$type}($object, $property, $helper, $layerFile);
        } catch (\Exception $e) {
        }
        return $response;
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
                if (Any::isArray($badAttr) && count($badAttr) > 0) {
                    foreach ($badAttr as $attr) {
                        $itemId = $formName . '-' . $attr;
                        try {
                            $render = App::$View->render(static::$structLayer['jsnotify'], ['itemId' => $itemId]);
                            App::$Alias->addPlainCode('js', $render);
                        } catch (SyntaxException $e) {
                            if (App::$Debug) {
                                App::$Debug->addException($e);
                            }
                        }
                    }
                }
            }
        }
        return '</form>';
    }
}
