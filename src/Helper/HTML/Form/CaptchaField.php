<?php
namespace Ffcms\Core\Helper\HTML\Form;

use Ffcms\Core\App;
use Ffcms\Core\Helper\HTML\System\NativeGenerator;

class CaptchaField extends NativeGenerator implements iField
{
    private $properties;
    private $name;
    
    /**
     * CaptchaField constructor. Pass attributes inside model.
     * @param array $properties
     * @param string $name
     * @param string|null $value
     */
    public function __construct($properties, $name, $value = null)
    {
        $this->properties = $properties;
        $this->name = $name;
    }

    /**
     * Build captcha response
     * {@inheritDoc}
     * @see \Ffcms\Core\Helper\HTML\Form\iField::make()
     */
    public function make()
    {
        // if captcha is 'full-type' based just return rendered output
        if (App::$Captcha->isFull()) {
            return App::$Captcha->get();
        }
        // get image link
        $image = App::$Captcha->get();
        $response = '<img id="src-secure-image" src="' . $image . '" alt="captcha" onClick="this.src=\''.$image.'&rnd=\'+Math.random()" />';
        // render response tag with image
        $this->properties['type'] = 'text';
        $response .= self::buildSingleTag('input', $this->properties);
        return $response;
    }
}
