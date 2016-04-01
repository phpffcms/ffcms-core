<?php

namespace Ffcms\Core\Helper\HTML;

use Ffcms\Core\App;
use Ffcms\Core\Exception\SyntaxException;
use Ffcms\Core\Helper\HTML\System\NativeGenerator;
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
    protected $structure = '<div class="form-group"><label for="%name%" class="col-md-3 control-label">%label%</label><div class="col-md-9">%item% <p class="help-block">%help%</p></div></div>';
    protected $structureCheckbox = '<div class="form-group"><div class="col-md-9 col-md-offset-3"><div class="checkbox"><label>%item% %label%</label></div><p class="help-block">%help%</p></div></div>';
    protected $structureCheckboxes = '<div class="checkbox"><label>%item%</label></div>';
    protected $structureJSError = '$("#%itemId%").parent().parent(".form-group").addClass("has-error")';
    protected $name;
    protected $formProperty = [];
    /** @var Model */
    protected $model;


    /**
     * Build form based on model properties
     * @param Model $model
     * @param array $property
     * @param array $structure
     * @throws SyntaxException
     */
    public function __construct($model, array $property = null, array $structure = null)
    {
        // prevent white-screen locks when model is not passed or passed wrong
        if (!$model instanceof Model) {
            throw new SyntaxException('Bad model type passed in form builder. Check for init: new Form()');
        }

        $this->model = $model;
        $this->name = $model->getFormName();

        // set custom html build structure form fields
        if (Obj::isArray($structure)) {
            if (isset($structure['base']) && !Str::likeEmpty($structure['base'])) {
                $this->structure = $structure['base'];
            }
            if (isset($structure['checkbox']) && !Str::likeEmpty($structure['checkbox'])) {
                $this->structureCheckbox = $structure['checkbox'];
            }
            if (isset($structure['checkboxes']) && !Str::likeEmpty($structure['checkboxes'])) {
                $this->structureCheckboxes = $structure['checkboxes'];
            }
            if (isset($structure['jserror']) && !Str::likeEmpty($structure['jserror'])) {
                $this->structureJSError = $structure['jserror'];
            }
        }

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
     * Display form field. Allowed type: text, password, textarea, checkbox, select, checkboxes, file, captcha, email, hidden
     * @param $object
     * @param $type
     * @param null|array $property
     * @param null|string $helper
     * @param null|string $structure
     * @return null|string
     */
    public function field($object, $type, $property = null, $helper = null, $structure = null)
    {
        if ($this->model === null) {
            if (App::$Debug !== null) {
                App::$Debug->addMessage('Form model is not defined for field name: ' . strip_tags($object));
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
                App::$Debug->addMessage('Form field "' . $object . '" is not defined in model: ' . get_class($this->model), 'error');
            }
            return null;
        }

        if ($structure === null) {
            if ($type === 'checkbox') {
                $structure = $this->structureCheckbox;
            } else {
                $structure = $this->structure;
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
        //$itemBody = $this->dataTypeTag($type, $object, $itemValue, $property);
        $constructor = new Constructor($this->model, $this->name, $type);
        $elementDOM = $constructor->makeTag($object, $itemValue, $property);
        
        // if item is hidden - return tag without assign of global template
        if ($type === 'hidden') {
            return $elementDOM;
        }

        return Str::replace(
            ['%name%', '%label%', '%item%', '%help%'],
            [$labelFor, $labelText, $elementDOM, self::nohtml($helper)],
            $structure
        );
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
                    $jsError = $this->structureJSError;
                    foreach ($badAttr as $attr) {
                        $itemId = $formName . '-' . $attr;
                        App::$Alias->addPlainCode('js', Str::replace('%itemId%', $itemId, $jsError));
                    }
                }
            }
        }
        return '</form>';
    }
}