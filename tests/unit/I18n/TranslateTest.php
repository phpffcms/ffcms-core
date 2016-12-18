<?php

namespace Ffcms\Core\I18n;

use Ffcms\Core\App;

class TranslateTest extends \Codeception\TestCase\Test
{
    /**
     * @var \Ffcms\Core\UnitTester
     */
    protected $tester;

    /** @var Translate */
    private $class;

    public function _before()
    {
        $this->class = new Translate();
    }

    public function testGet()
    {
        App::$Request->setLanguage('ru');
        $this->assertSame('acceptance', $this->class->get('Codecept', 'unit tester'));
        $this->assertSame('no more bugs', $this->class->get('Codecept', 'shitty bug %var%', ['var' => 'bugs']));
        $this->assertSame('unknown text', $this->class->get('UnknownFile', 'unknown text'));
    }

    public function testLoad()
    {
        $this->assertSame([
            'unit tester' => 'acceptance',
            'shitty bug %var%' => 'no more %var%'
        ], $this->tester->invokeMethod($this->class, 'load', ['Codecept']));
        $this->assertSame([], $this->tester->invokeMethod($this->class, 'load', ['UnknownFile']));
    }

    public function testAppend()
    {
        $this->assertTrue($this->tester->invokeMethod($this->class, 'append', ['/I18n/Front/ru/Default.php']));
    }

    public function testGetAvailableLangs()
    {
        $this->assertContains('en', $this->class->getAvailableLangs());
    }

    public function testGetLocaleText()
    {
        $this->assertSame('Test text', $this->class->getLocaleText(['en' => 'Test text'], 'en'));
        $this->assertNull($this->class->getLocaleText(['en' => 'Test text'], 'ru'));
    }
}