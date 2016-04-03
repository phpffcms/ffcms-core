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

    protected function _after()
    {
    }

    // test isInt
    public function testIsInt()
    {
        $this->assertTrue(Obj::isInt(10));
        $this->assertFalse(Obj::isInt(10.5));
        $this->assertFalse(Obj::isInt(true));
        $this->assertFalse(Obj::isInt('var'));
        $this->assertFalse(Obj::isInt('10'));
        $this->assertFalse(Obj::isInt([10]));
        $this->assertFalse(Obj::isInt($this->object));
    }
    
    // test is like int method
    public function testIsLikeInt()
    {
        $this->assertTrue(Obj::isLikeInt(10));
        $this->assertFalse(Obj::isLikeInt(10.5));
        $this->assertTrue(Obj::isLikeInt(true));
        $this->assertFalse(Obj::isLikeInt('var'));
        $this->assertTrue(Obj::isLikeInt('10'));
        $this->assertFalse(Obj::isLikeInt([10]));
        $this->assertFalse(Obj::isInt($this->object));
    }
    
    // test is string method
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
}