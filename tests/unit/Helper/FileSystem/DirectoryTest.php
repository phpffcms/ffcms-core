<?php

namespace Ffcms\Core\Helper\FileSystem;

class DirectoryTest extends \Codeception\TestCase\Test
{
    /**
     * @var \Ffcms\Core\UnitTester
     */
    protected $tester;

    public function testExists()
    {
        $this->assertTrue(Directory::exist('/Apps'));
        $this->assertTrue(Directory::exist(root . '/Apps'));
        $this->assertFalse(Directory::exist('/some/unknown/dir'));
        $this->assertFalse(Directory::exist('/index.php'));
    }

    public function testWritable()
    {
        $this->assertTrue(Directory::writable('/upload'));
        $this->assertTrue(Directory::writable(root . '/upload'));
    }

    public function testCreateRenameRemove()
    {
        $this->assertTrue(Directory::create('/upload/test'));
        $this->assertTrue(Directory::exist('/upload/test'));

        $this->assertTrue(Directory::rename('/upload/test', 'newtest'));
        $this->assertTrue(Directory::exist('/upload/newtest'));

        $this->assertTrue(Directory::remove('/upload/newtest'));
    }

    public function testScan()
    {
        $dirs = Directory::scan('/Apps', GLOB_ONLYDIR, true);
        $this->assertContains('Controller', $dirs);
        $this->assertContains('View', $dirs);
    }

    public function testSize()
    {
        $this->assertGreaterThan(0, Directory::size('/Apps'));
        $this->assertSame(0, Directory::size('/some/unknown/dir'));
    }
}