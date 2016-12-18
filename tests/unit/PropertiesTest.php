<?php

namespace Ffcms\Core;

class PropertiesTest extends \Codeception\TestCase\Test
{
    /**
     * @var \Ffcms\Core\UnitTester
     */
    protected $tester;

    /** @var Properties */
    private $class;

    public function _before()
    {
        $this->class = new Properties();
    }

    public function testLoad()
    {
        $this->assertTrue($this->tester->invokeMethod($this->class, 'load', ['Default']));
        $this->assertTrue($this->tester->invokeMethod($this->class, 'load', ['default']));
        $this->assertTrue($this->tester->invokeMethod($this->class, 'load', ['Object']));
        $this->assertTrue($this->tester->invokeMethod($this->class, 'load', ['Permissions']));
        $this->assertFalse($this->tester->invokeMethod($this->class, 'load', ['UnknownConfigFile']));
    }

    public function testGet()
    {
        $this->assertContains('http', $this->class->get('baseProto'));
        $this->assertSame('en', $this->class->get('baseLanguage'));
        $this->assertTrue($this->class->get('testSuite', 'default', true));
        $this->assertFalse($this->class->get('shittyconfig', 'nofile'));
    }

    public function testGetAll()
    {
        $this->assertInternalType('array', $this->class->getAll());
        $this->assertArrayHasKey('baseDomain', $this->class->getAll());
    }

    public function testUpdateConfig()
    {
        $this->assertTrue($this->class->updateConfig('default', ['testSuite' => false]));
        $this->assertFalse($this->class->get('testSuite'));
        $this->assertTrue($this->class->updateConfig('default', ['testSuite' => true]));
    }

    public function testWriteConfig()
    {
        file_put_contents(root . '/Private/Config/Acceptance.php', '<?php echo "Hey bugs i wanna test you ;)"; ');
        $this->assertTrue($this->class->writeConfig('acceptance', ['testGuy' => true, 'shittybug' => 'nope:)']));
        $this->assertSame(['testGuy' => true, 'shittybug' => 'nope:)'], $this->class->getAll('acceptance'));
        unlink(root . '/Private/Config/Acceptance.php');
    }
}