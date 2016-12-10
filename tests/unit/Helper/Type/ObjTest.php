<?php
namespace Ffcms\Core\Helper\Type;

class ObjTest extends \Codeception\TestCase\Test
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

    public function testIsInt()
    {
        $this->assertTrue(Obj::isInt(10));
        $this->assertTrue(Obj::isInt(0x01)); // hex 16
        $this->assertFalse(Obj::isInt(10.5));
        $this->assertFalse(Obj::isInt(true));
        $this->assertFalse(Obj::isInt('var'));
        $this->assertFalse(Obj::isInt('10'));
        $this->assertFalse(Obj::isInt([10]));
        $this->assertFalse(Obj::isInt($this->object));
    }

    public function testIsLikeInt()
    {
        $this->assertTrue(Obj::isLikeInt(10));
        $this->assertTrue(Obj::isLikeInt(0x01)); // hex 16
        $this->assertFalse(Obj::isLikeInt('0x01'));
        $this->assertFalse(Obj::isLikeInt(10.5));
        $this->assertTrue(Obj::isLikeInt(true));
        $this->assertFalse(Obj::isLikeInt('var'));
        $this->assertTrue(Obj::isLikeInt('10'));
        $this->assertFalse(Obj::isLikeInt([10]));
        $this->assertFalse(Obj::isInt($this->object));
    }

    public function testIsString()
    {
        $this->assertFalse(Obj::isString(10));
        $this->assertFalse(Obj::isString(10.5));
        $this->assertFalse(Obj::isString(true));
        $this->assertTrue(Obj::isString('var'));
        $this->assertTrue(Obj::isString('10'));
        $this->assertFalse(Obj::isString([10]));
        $this->assertFalse(Obj::isInt($this->object));
    }

    public function testIsArray()
    {
        $this->assertTrue(Obj::isArray(['1' => '2']));
        $this->assertFalse(Obj::isArray(false));
        $this->assertFalse(Obj::isArray(1));
        $this->assertFalse(Obj::isArray($this->object));
    }

    public function testIsFloat()
    {
        $this->assertFalse(Obj::isFloat(10));
        $this->assertTrue(Obj::isFloat(10.5));
        $this->assertFalse(Obj::isFloat(true));
        $this->assertFalse(Obj::isFloat('var'));
        $this->assertFalse(Obj::isFloat('10.5'));
        $this->assertFalse(Obj::isFloat('10,5'));
    }

    public function testIsLikeFloat()
    {
        $this->assertTrue(Obj::isLikeFloat(10.5));
        $this->assertTrue(Obj::isLikeFloat('10.5'));
        $this->assertFalse(Obj::isLikeFloat('10,5'));
        $this->assertTrue(Obj::isLikeFloat('10'));
        $this->assertTrue(Obj::isLikeFloat(10));
    }

    public function testIsLikeBoolean()
    {
        $this->assertTrue(Obj::isLikeBoolean(true));
        $this->assertTrue(Obj::isLikeBoolean('true'));
        $this->assertTrue(Obj::isLikeBoolean(1));
        $this->assertTrue(Obj::isLikeBoolean('1'));
        $this->assertTrue(Obj::isLikeBoolean('on'));
        $this->assertTrue(Obj::isLikeBoolean('yes'));
        $this->assertFalse(Obj::isLikeBoolean('yep'));
        $this->assertFalse(Obj::isLikeBoolean('somestring'));
        $this->assertFalse(Obj::isLikeBoolean([true]));
    }

    public function testIsObject()
    {
        $this->assertTrue(Obj::isObject($this->object));
        $this->assertFalse(Obj::isObject($this->object->a));
        $this->assertFalse(Obj::isObject(['a' => 'b']));
        $this->assertTrue(Obj::isObject($this));
        $this->assertFalse(Obj::isObject(1));
        $this->assertFalse(Obj::isObject(true));
        $this->assertFalse(Obj::isObject(__CLASS__));
    }

    public function testIsIterable()
    {
        $this->assertFalse(Obj::isIterable($this->object));
        $this->assertTrue(Obj::isIterable(['a' => 'b']));
        $this->assertFalse(Obj::isIterable(1));
        $this->assertFalse(Obj::isIterable(true));
        $this->assertTrue(Obj::isIterable($this->createIterableYield()));
    }

    public function testGuessType()
    {
        $this->assertSame(Obj::guessType('data'), 'data');
        $this->assertSame(Obj::guessType('123'), 123);
        $this->assertSame(Obj::guessType('true'), true);
        $this->assertSame(Obj::guessType('10.5'), 10.5);
        $this->assertSame(Obj::guessType(0x01), 1);
    }

    private function createIterableYield()
    {
        yield 1;
        yield 2 => 3;
    }
}