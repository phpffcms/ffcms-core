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
    }
}