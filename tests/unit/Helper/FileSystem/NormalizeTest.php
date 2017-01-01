<?php

namespace Ffcms\Core\Helper\FileSystem;

define('DS', DIRECTORY_SEPARATOR);

class NormalizeTest extends \Codeception\TestCase\Test
{
    /**
     * @var \Ffcms\Core\UnitTester
     */
    protected $tester;

    public function testDiskPath()
    {
        $this->assertSame('test' . DS . 'path' . DS . 'dir', Normalize::diskPath('/test/path/dir'));
        $this->assertSame('test' . DS . 'path', Normalize::diskPath('/test/path/dir/../'));
        $this->assertSame('', Normalize::diskPath('/../../'));
    }

    public function testDiskFullPath()
    {
        $this->assertSame(root . DS . 'test' . DS . 'path', Normalize::diskFullPath('/test/path'));
        $this->assertSame(root . DS . 'test', Normalize::diskFullPath('/test/path/../'));
    }
}