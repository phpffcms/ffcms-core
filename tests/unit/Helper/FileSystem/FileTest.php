<?php

namespace Ffcms\Core\Helper\FileSystem;

define('DS', DIRECTORY_SEPARATOR);

class FileTest extends \Codeception\TestCase\Test
{
    /**
     * @var \Ffcms\Core\UnitTester
     */
    protected $tester;

    public function testRead()
    {
        // check if result of read instance of string
        $this->assertInternalType('string', File::read('/composer.json'));
        $this->assertInternalType('string', File::read(root . '/composer.json'));
        $this->assertInternalType('string', File::read('/Private/Config/Default.php'));

        // folder is not file, should return false
        $this->assertFalse(File::read('/Apps'));
        $this->assertFalse(File::read('/some/unknown/file.txt'));
    }

    public function testExists()
    {
        $this->assertTrue(File::exist('/composer.json'));
        $this->assertTrue(File::exist(root . '/composer.json'));
        $this->assertTrue(File::exist(root . '/Private/Config/Default.php'));

        $this->assertFalse(File::exist('/Apps'));
        $this->assertFalse(File::exist('/some/unknown/file.txt'));
    }

    public function testReadable()
    {
        $this->assertTrue(File::readable('/composer.json'));
        $this->assertTrue(File::readable(root . '/composer.json'));
        $this->assertTrue(File::readable(root . '/Private/Config/Default.php'));
    }

    public function testWritable()
    {
        $this->assertTrue(File::writable('/Private/Config/Default.php'));
        $this->assertTrue(File::writable(root . '/Private/Config/Default.php'));
    }

    public function testCreateWriteAndRemove()
    {
        $this->assertGreaterThan(0, File::write('/upload/test.txt', 'test'));
        $this->assertTrue(File::exist('/upload/test.txt'));
        $this->assertSame('test', File::read('/upload/test.txt'));
        $this->assertTrue(File::remove('/upload/test.txt'));
        $this->assertFalse(File::exist('/upload/test.txt'));
    }

    public function testMtime()
    {
        $this->assertGreaterThan(0, File::mTime('/composer.json'));
        $this->assertSame(0, File::mTime('/some/unknown/file.txt'));
    }

    public function testListFiles()
    {
        $files = File::listFiles('/Apps/Controller/Front/', ['.php'], true);
        $this->assertContains('Main.php', $files);
        $this->assertContains('Content.php', $files);
        $this->assertContains('User.php', $files);
    }

    public function testSize()
    {
        $this->assertGreaterThan(0, File::size('/index.php'));
        $this->assertSame(0, File::size('/some/unknown/file.txt'));
    }

    public function testGetMd5()
    {
        $this->assertNotFalse(File::getMd5('/index.php'));
        $this->assertInternalType('string', File::getMd5('/index.php'));

        $this->assertFalse(File::getMd5('/some/unknown/file.txt'));
    }

    public function testCurlGetFromUrl()
    {
        $this->assertInternalType('string', File::getFromUrl('https://google.com'));

        $this->assertNull(File::getFromUrl('wrongurlformat'));
    }

}