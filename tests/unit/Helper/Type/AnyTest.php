<?php

namespace Ffcms\Core\Helper\Type;

use \Codeception\TestCase\Test;


class AnyTest extends Test
{
    /**
     * @var \Ffcms\Core\UnitTester
     */
    protected $tester;

    private $object;

    protected function _before()
    {
        $this->object = new \stdClass();
        $this->object->a = 1;
    }

    // test isEmpty method
    public function testIsEmpty()
    {
        $this->assertTrue(Any::isEmpty(null));
        $this->assertTrue(Any::isEmpty(''));
        $this->assertTrue(Any::isEmpty([]));
        $this->assertTrue(Any::isEmpty(false));
        $this->assertFalse(Any::isEmpty(0));
        $this->assertFalse(Any::isEmpty(0.0));
        $this->assertFalse(Any::isEmpty(0.0));
    }

    public function testIsInt()
    {
        $int1 = 10;
        $int2 = 0x01; // hex 16
        $double1 = 10.5;
        $bool = true;
        $str1 = 'var';
        $str2 = '10';
        $arr = [10];

        $this->assertTrue(Any::isInt($int1));
        $this->assertTrue(Any::isInt($int2)); // hex 16
        $this->assertFalse(Any::isInt($double1));
        $this->assertTrue(Any::isInt($bool));
        $this->assertFalse(Any::isInt($str1));
        $this->assertTrue(Any::isInt($str2));
        $this->assertFalse(Any::isInt($arr));
        $this->assertFalse(Any::isInt($this->object));
    }

    public function testIsString()
    {
        $this->assertFalse(Any::isStr(10));
        $this->assertFalse(Any::isStr(10.5));
        $this->assertFalse(Any::isStr(true));
        $this->assertTrue(Any::isStr('var'));
        $this->assertTrue(Any::isStr('10'));
        $this->assertFalse(Any::isStr([10]));
        $this->assertFalse(Any::isInt($this->object));
    }

    public function testIsArray()
    {
        $this->assertTrue(Any::isArray(['1' => '2']));
        $this->assertFalse(Any::isArray(false));
        $this->assertFalse(Any::isArray(1));
        $this->assertFalse(Any::isArray($this->object));
    }

    public function testIsFloat()
    {
        $int = 10;
        $double = 10.5;
        $bool = true;
        $str1 = 'var';
        $str2 = '10.5';
        $str3 = '10,5';

        $this->assertTrue(Any::isFloat($int));
        $this->assertTrue(Any::isFloat($double));
        $this->assertTrue(Any::isFloat($bool));
        $this->assertFalse(Any::isFloat($str1));
        $this->assertTrue(Any::isFloat($str2));
        $this->assertFalse(Any::isFloat($str3));
    }

    public function testIsBool()
    {
        $bool1 = true;
        $int1 = 1;
        $str1 = '0';
        $str2 = '5';
        $str3 = 'some string';

        $this->assertTrue(Any::isBool($bool1));
        $this->assertTrue(Any::isBool($int1));
        $this->assertTrue(Any::isBool($str1));
        $this->assertFalse(Any::isBool($str2));
        $this->assertFalse(Any::isBool($str3));
    }

    public function testIsIterable()
    {
        $this->assertFalse(Any::isIterable($this->object));
        $this->assertTrue(Any::isIterable(['a' => 'b']));
        $this->assertFalse(Any::isIterable(1));
        $this->assertFalse(Any::isIterable(true));
        $this->assertTrue(Any::isIterable($this->createIterableYield()));
    }

    private function createIterableYield()
    {
        yield 1;
        yield 2 => 3;
    }
}