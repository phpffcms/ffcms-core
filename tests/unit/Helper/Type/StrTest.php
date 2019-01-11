<?php

namespace Ffcms\Core\Helper\Type;

class StrTest extends \Codeception\TestCase\Test
{
    /**
     * @var \Ffcms\Core\UnitTester
     */
    protected $tester;

    public function testLikeEmpty()
    {
        $this->assertTrue(Str::likeEmpty(null));
        $this->assertTrue(Str::likeEmpty(false));
        $this->assertTrue(Str::likeEmpty(''));
        $this->assertFalse(Str::likeEmpty(true));
        $this->assertFalse(Str::likeEmpty(1));
        $this->assertFalse(Str::likeEmpty(0));
        $this->assertFalse(Str::likeEmpty('data'));
    }

    public function testStartsWith()
    {
        $this->assertTrue(Str::startsWith('th', 'this'));
        $this->assertTrue(Str::startsWith('эт', 'этотест')); // utf8
        $this->assertFalse(Str::startsWith('th', 'isht'));
    }

    public function testEndsWith()
    {
        $this->assertTrue(Str::endsWith('is', 'this'));
        $this->assertTrue(Str::endsWith('то', 'тестэто')); // utf8
        $this->assertFalse(Str::endsWith('is', 'isht'));
        $this->assertTrue(Str::endsWith('1', 'this1'));
    }

    public function testFirstIn()
    {
        $this->assertSame('test', Str::firstIn('test.me', '.'));
        $this->assertSame('test', Str::firstIn('test.me.baby.tonight', '.'));
        $this->assertSame('это', Str::firstIn('это проверка', ' ')); //utf8
        $this->assertSame('', Str::firstIn('t', 'ttttt'));
    }

    public function testLastIn()
    {
        $this->assertSame('me', Str::lastIn('test.me', '.', true));
        $this->assertSame('.me', Str::lastIn('test.it.for.me', '.'));
        $this->assertSame('тестер', Str::lastIn('привет тестер', ' ', true));
    }

    public function testCleanExtension()
    {
        $this->assertSame('file', Str::cleanExtension('file.sh'));
        $this->assertSame('file.tag', Str::cleanExtension('file.tag.gz'));
        $this->assertSame('some string', Str::cleanExtension('some string'));
    }

    public function testLength()
    {
        $this->assertSame(4, Str::length('this'));
        $this->assertSame(3, Str::length(123));
        $this->assertSame(4, Str::length(10.5));
        $this->assertSame(3, Str::length('это'));
    }

    public function testLowerCase()
    {
        $this->assertSame('this is test', Str::lowerCase('ThIs iS tEST'));
        $this->assertSame('это тест', Str::lowerCase('ЭтО ТЕст'));
        $this->assertSame('123', Str::lowerCase(123));
    }

    public function testUpperCase()
    {
        $this->assertSame('THIS IS TEST', Str::upperCase('ThIs iS tEST'));
        $this->assertSame('ЭТО ТЕСТ', Str::upperCase('ЭтО ТЕст'));
        $this->assertSame('123', Str::upperCase(123));
    }

    public function testEntryCount()
    {
        $this->assertSame(3, Str::entryCount('this is test', 's'));
        $this->assertSame(0, Str::entryCount('this is test', 'a'));
        $this->assertSame(1, Str::entryCount('this is 1 test', '1'));
    }

    public function testSplitCamelCase()
    {
        $this->assertSame('This is test', Str::splitCamelCase('thisIsTest'));
    }

    public function testSub()
    {
        $this->assertSame('th', Str::sub('this', 0, 2));
        $this->assertSame('эт', Str::sub('это', 0, 2));
        $this->assertSame('abcdef', Str::sub('abcdef', 0));
        $this->assertSame('abcdef', Str::sub('abcdef', 0, null));
        $this->assertSame('de',     Str::sub('abcdef', 3, 2));
        $this->assertSame('def',    Str::sub('abcdef', 3));
        $this->assertSame('def',    Str::sub('abcdef', 3, null));
        $this->assertSame('cd',     Str::sub('abcdef', -4, 2));
        $this->assertSame('cdef',   Str::sub('abcdef', -4));
        $this->assertSame('cdef',   Str::sub('abcdef', -4, null));
        $this->assertSame('',   Str::sub('abcdef', 4, 0));
        $this->assertSame('',   Str::sub('abcdef', -4, 0));
        $this->assertSame('это', Str::sub('это', 0));
        $this->assertSame('это', Str::sub('это', 0, null));
        $this->assertSame('то',     Str::sub('это', 1, 2));
        $this->assertSame('о',    Str::sub('это', 2));
        $this->assertSame('о',    Str::sub('это', 2, null));
    }

    public function testReplace()
    {
        $this->assertSame('this not test', Str::replace('are', 'not', 'this are test'));
        $this->assertSame('yooy oy test', Str::replace(['th', 'is'], ['yo', 'oy'], 'this is test'));
    }

    public function testContains()
    {
        $this->assertTrue(Str::contains('is', 'this is test'));
        $this->assertFalse(Str::contains('is', 'test'));
        $this->assertTrue(Str::contains('то', 'это тест'));
    }

    public function testIsUrl()
    {
        $this->assertTrue(Str::isUrl('http://ffcms.org'));
        $this->assertTrue(Str::isUrl('https://ffcms.org'));
        $this->assertFalse(Str::isUrl('ffcms.org'));
        $this->assertFalse(Str::isUrl('www.ffcms.org'));
        $this->assertFalse(Str::isUrl('main@gmail.com'));
    }

    public function testRandomLatinNumeric()
    {
        $this->assertSame(32, strlen(Str::randomLatinNumeric(32)));
        $this->assertNotRegExp('/[^A-Za-z0-9]/', Str::randomLatinNumeric(32));
    }

    public function testRandomLatin()
    {
        $this->assertSame(32, strlen(Str::randomLatin(32)));
        $this->assertNotRegExp('/[^A-Za-z]/', Str::randomLatin(32));
    }

    public function testIsEmail()
    {
        $this->assertTrue(Str::isEmail('root@ffcms.org'));
        $this->assertFalse(Str::isEmail('ffcms.org'));
        $this->assertFalse(Str::isEmail('root.ffcms.org'));
        $this->assertTrue(Str::isEmail('root-not@ffcms.org'));
        $this->assertTrue(Str::isEmail('root.not@ffcms.org'));
    }

    public function testIsPhone()
    {
        $this->assertTrue(Str::isPhone('+71234567890'));
        $this->assertTrue(Str::isPhone('88004567890'));
        $this->assertTrue(Str::isPhone('+38004567890'));
    }

    public function testConcat()
    {
        $this->assertSame('this.test', Str::concat('.', 'this', 'test'));
        $this->assertSame('hello my world', Str::concat(' ', 'hello', 'my', 'world'));
        $this->assertSame('привет мир', Str::concat(' ', 'привет', 'мир'));
    }

    public function testEqualIngoreCase()
    {
        $this->assertTrue(Str::equalIgnoreCase('HeLlO', 'hello'));
        $this->assertFalse(Str::equalIgnoreCase('HeLlO123', 'hello'));
        $this->assertFalse(Str::equalIgnoreCase('ПрИвЕт', 'привет')); // not support utf8
    }

}