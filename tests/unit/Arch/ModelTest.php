<?php

namespace Ffcms\Core\Arch;

use Codeception\Util\Stub;
use Ffcms\Core\App;

class ModelTest extends \Codeception\TestCase\Test
{
    /**
     * @var \Ffcms\Core\UnitTester
     */
    protected $tester;

    /** @var DemoModel */
    private $class;

    public function _before()
    {
        $this->class = new DemoModel(false);
        $this->class->setSubmitMethod('POST');
        App::$Request->setMethod('POST');

        // emulate request data for model
        App::$Request->request->add([
            'DemoModel' => [
                'login' => 'tester',
                'submit' => 'Save',
                'pass' => 'tester',
                'data' => [
                    'text' => 'Some <b>plain</b> text',
                    'html' => '<p>data here</p><script>alert(1)</script>'
                ],
                'boolCheckbox' => '1'
            ]
        ]);
    }

    public function testModelAttributes()
    {
        $this->class->test = 'test';
        $this->assertSame('test', $this->class->test);
    }

    public function testLabels()
    {
        $this->assertSame('Login', $this->class->getLabel('login'));
        $this->assertSame('Password', $this->class->getLabel('pass'));

        $this->assertSame('Text', $this->class->getLabel('data.text'));
    }

    public function testGetAllProperties()
    {
        $attr = $this->class->getAllProperties();
        $this->assertArrayHasKey('login', $attr);
        $this->assertArrayHasKey('pass', $attr);
    }

    public function testModelEmulation()
    {
        $this->assertTrue($this->class->send());
        $this->assertTrue($this->class->validate());

        $this->assertSame('tester', $this->class->login);
        $this->assertSame('<p>data here</p>', $this->class->data['html']);
        $this->assertSame('Some plain text', $this->class->data['text']);
    }
}

class DemoModel extends Model
{
    public $login;
    public $pass;

    public $data = [];

    public $boolCheckbox;

    public $test;

    public $_tokenRequired = false;
    protected $_formName = 'DemoModel';

    public function rules(): array
    {
        return [
            [['login', 'pass'], 'required'],
            ['boolCheckbox', 'required'],
            ['data.text', 'used'],
            ['data.html', 'required'],
            ['login', 'length_min', 3],
            ['boolCheckbox', 'in', [0, 1]]
        ];
    }

    public function types(): array
    {
        return [
            'data' => 'html',
            'data.html' => 'html',
            'data.text' => 'text'
        ];
    }

    public function labels(): array
    {
        return [
            'login' => 'Login',
            'pass' => 'Password',
            'data.text' => 'Text',
            'data.html' => 'Html text'
        ];
    }

    public function make()
    {
        return true;
    }
}