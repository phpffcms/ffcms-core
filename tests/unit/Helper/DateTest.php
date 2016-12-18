<?php
namespace Ffcms\Core\Helper;

class DateTest extends \Codeception\TestCase\Test
{
    /**
     * @var \Ffcms\Core\UnitTester
     */
    protected $tester;

    private $date, $stamp;

    public function _before()
    {
        $this->date = new \DateTime('2016-10-25 00:00:00');
        $this->stamp = $this->date->getTimestamp();
    }

    public function testConvertToDatetime()
    {
        $this->assertSame(date('d.m.Y', $this->stamp), Date::convertToDatetime($this->stamp, Date::FORMAT_TO_DAY));
        $this->assertSame(date('d.m.Y H:i', $this->stamp), Date::convertToDatetime($this->stamp, Date::FORMAT_TO_HOUR));
        $this->assertSame($this->date->format('d.m.Y'), Date::convertToDatetime($this->stamp, Date::FORMAT_TO_DAY));
        $plus3day = $this->stamp + 259200;
        $this->assertSame((new \DateTime('2016-10-28 00:00:00'))->format('d.m.Y'), Date::convertToDatetime($plus3day, Date::FORMAT_TO_DAY));
    }

    public function testConvertToTimestamp()
    {
        $this->assertSame($this->stamp, Date::convertToTimestamp($this->date->format('Y-m-d H:i:s')));
        $this->assertSame(0, Date::convertToTimestamp('shitty bug')); // false positive test
    }

    public function testHumanize()
    {
        $this->assertSame('0 seconds ago', Date::humanize(date('d.m.Y H:i:s')));
        $this->assertSame($this->date->format('d.m.Y H:i'), Date::humanize($this->stamp));
    }
}