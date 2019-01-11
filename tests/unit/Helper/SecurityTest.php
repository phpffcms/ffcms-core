<?php

namespace Ffcms\Core\Helper;

class SecurityTest extends \Codeception\TestCase\Test
{
    /**
     * @var \Ffcms\Core\UnitTester
     */
    protected $tester;

    /** @var Security */
    private $class;

    public function _before()
    {
        $this->class = new Security();
    }

    public function testSecureHtml()
    {
        $this->assertSame('<p>test text</p>', $this->class->secureHtml('<p rel="javascript:alert(1)">test text</p>'));
        $this->assertSame('<a>test</a>', $this->class->secureHtml('<a href="javascript:void(0)">test</a>'));

        $this->assertSame('<p>legal <b>text</b></p>', $this->class->secureHtml('<p>legal <b>text</b></p>'));
    }

    public function testStripTags()
    {
        $this->assertSame('test text', $this->class->strip_tags('<p>test <b>text</b></p>'));
        $this->assertSame(['test', 'text'], $this->class->strip_tags(['<p>test</p>', '<h1>text</h1>']));
    }

    public function testStripPhpTags()
    {
        $this->assertSame('test $var = &quot;value&quot;', $this->class->strip_php_tags('<?php $var = "test";?>test $var = "value"'));
    }

    public function testEscapeQuotes()
    {
        $this->assertSame('test  text  here', $this->class->escapeQuotes('test " text \' here'));
    }

    public function testSimpleHash()
    {
        $this->assertSame('13471545', $this->class->simpleHash('test string'));
        $this->assertNull($this->class->simpleHash(['a', 'b']));
        $this->assertNull($this->class->simpleHash(new \stdClass()));
    }

}