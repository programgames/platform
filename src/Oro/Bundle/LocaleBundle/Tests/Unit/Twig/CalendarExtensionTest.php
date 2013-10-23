<?php

namespace Oro\Bundle\LocaleBundle\Tests\Unit\Twig;

use Oro\Bundle\LocaleBundle\Twig\CalendarExtension;
use Oro\Bundle\LocaleBundle\Model\Calendar;

class CalendarExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CalendarExtension
     */
    protected $extension;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $calendar;

    protected function setUp()
    {
        $this->calendar = $this->getMockBuilder('Oro\Bundle\LocaleBundle\Model\Calendar')
            ->disableOriginalConstructor()
            ->getMock();
        $this->extension = new CalendarExtension($this->calendar);
    }

    public function testGetFunctions()
    {
        $filters = $this->extension->getFunctions();

        $this->assertCount(3, $filters);

        $this->assertInstanceOf('Twig_SimpleFilter', $filters[0]);
        $this->assertEquals('oro_calendar_month_names', $filters[0]->getName());

        $this->assertInstanceOf('Twig_SimpleFilter', $filters[1]);
        $this->assertEquals('oro_calendar_day_of_week_names', $filters[1]->getName());

        $this->assertInstanceOf('Twig_SimpleFilter', $filters[2]);
        $this->assertEquals('oro_calendar_first_day_of_week', $filters[2]->getName());
    }

    public function testGetMonthNames()
    {
        $width = Calendar::WIDTH_NARROW;
        $locale = 'en_US';
        $expectedResult = array('expected_result');

        $this->calendar->expects($this->once())->method('getMonthNames')
            ->with($width, $locale)
            ->will($this->returnValue($expectedResult));

        $this->assertEquals($expectedResult, $this->extension->getMonthNames($width, $locale));
    }

    public function testGetDayOfWeekNames()
    {
        $width = Calendar::WIDTH_ABBREVIATED;
        $locale = 'en_US';
        $expectedResult = array('expected_result');

        $this->calendar->expects($this->once())->method('getDayOfWeekNames')
            ->with($width, $locale)
            ->will($this->returnValue($expectedResult));

        $this->assertEquals($expectedResult, $this->extension->getDayOfWeekNames($width, $locale));
    }

    public function testGetFirstDayOfWeek()
    {
        $locale = 'en_US';
        $expectedResult = Calendar::DOW_MONDAY;

        $this->calendar->expects($this->once())->method('getFirstDayOfWeek')
            ->with($locale)
            ->will($this->returnValue($expectedResult));

        $this->assertEquals($expectedResult, $this->extension->getFirstDayOfWeek($locale));
    }

    public function testGetName()
    {
        $this->assertEquals('oro_locale_calendar', $this->extension->getName());
    }
}
