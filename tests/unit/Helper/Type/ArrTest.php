<?php

namespace Ffcms\Core\Helper\Type;

class ArrTest extends \Codeception\TestCase\Test
{
    /**
     * @var \Ffcms\Core\UnitTester
     */
    protected $tester;

    public function testIn()
    {
        $this->assertTrue(Arr::in('test', ['a', 'b', 'test'], true));
        $this->assertFalse(Arr::in(123, ['a', '123', 'test'], true));
        $this->assertTrue(Arr::in(123, ['a', '123', 'test'], false));
        $this->assertFalse(Arr::in('wtf', ['a', '123', 'test'], false));
    }

    public function testMerge()
    {
        $this->assertSame(['a' => 'b', 'c' => 'd'], Arr::merge(['a' => 'b'], ['c' => 'd']));
        $this->assertSame(['a' => 'd'], Arr::merge(['a' => 'b'], ['a' => 'd']));
        $this->assertSame(['a', 'b'], Arr::merge(['a'], ['b']));
    }

    public function testMergeRecursive()
    {
        $this->assertSame(['a' => 'b', 'c' => ['d' => ['e', 'f']]], Arr::mergeRecursive(['a' => 'b', 'c' => ['d' => 'e']], ['c' => ['d' => 'f']]));
    }

    public function testGetByPath()
    {
        $haystack = [
            'key1' => [
                'key2' => 'value2',
                'value1'
            ],
            'key3' => [
                'shit'
            ]
        ];
        $this->assertSame('value2', Arr::getByPath('key1.key2', $haystack));
        $this->assertSame(['key2' => 'value2', 'value1'], Arr::getByPath('key1', $haystack));
        $this->assertSame(null, Arr::getByPath('key3.shit', $haystack));
    }

    public function testPluck()
    {
        $arr = [
            'key1' => [
                'title' => 'some data'
            ],
            'key2' => [
                'title' => 'other data'
            ]
        ];

        $this->assertSame(['some data', 'other data'], Arr::pluck('title', $arr));
    }

    public function testOnlyNumericValues()
    {
        $this->assertTrue(Arr::onlyNumericValues([1,2,3]));
        $this->assertTrue(Arr::onlyNumericValues(['1', 2, 3]));
        $this->assertFalse(Arr::onlyNumericValues(['d', 2, '3']));
    }
}