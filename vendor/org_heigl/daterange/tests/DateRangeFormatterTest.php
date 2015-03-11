<?php
/**
 * Copyright (c)2015-2015 heiglandreas
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIBILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @author    Andreas Heigl<andreas@heigl.org>
 * @copyright ©2015-2015 Andreas Heigl
 * @license   http://www.opesource.org/licenses/mit-license.php MIT-License
 * @version   0.0
 * @since     22.02.15
 * @link      https://github.com/heiglandreas/
 */

namespace Org_Heigl\DateRangeTest;

use Org_Heigl\DateRange\DateRangeFormatter;
use Org_Heigl\UnitTestHelper;

class DateRangeFormatterTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @param $start
     * @param $end
     * @param $interval
     * @dataProvider dateIntervalProvider
     */
    public function testSettingTestableParts($format, $expected)
    {
        $formatter = new DateRangeFormatter();
        $formatter->setFormat($format);

        $sweetsThief = function (DateRangeFormatter $formatter) {
            return $formatter->testableParts;
        };

        $sweetsThief = \Closure::bind($sweetsThief, null, $formatter);

        $this->assertEquals($expected, $sweetsThief($formatter));

    }

    public function dateIntervalProvider()
    {
        return array(
            array('d.m.Y', array(3,1,0)),
            array('\d.m.\Y', array(1)),
            array('qWertz', array(3,2)),
        );
    }

    /**
     * @param $format
     * @param $start
     * @param $end
     * @param $expected
     * @dataProvider gettingDiffPointProvider
     */
    public function testGettingDiffPoint($format, $start, $end, $expected)
    {
        $formatter = new DateRangeFormatter();
        $formatter->setFormat($format);

        $method = UnitTestHelper::getMethod($formatter, 'getDiffPoint');
        $diffPoint = $method->invoke($formatter, new \DateTime($start), new \DateTime($end));

        $this->assertEquals($expected, $diffPoint);
    }

    public function gettingDiffPointProvider()
    {
        return array(
            array('d.m.Y', '12.3.2014', '13.3.2014', DateRangeFormatter::DAY),
            array('d.m.Y', '12.3.2014', '13.4.2014', DateRangeFormatter::MONTH),
            array('d.m.Y', '12.3.2014', '13.3.2015', DateRangeFormatter::YEAR),
        );
    }

    public function testGettingDiffPointWithoutDiff()
    {
        $formatter = new DateRangeFormatter();
        $formatter->setFormat('d.m.Y');

        $method = UnitTestHelper::getMethod($formatter, 'getDiffPoint');
        $diffPoint = $method->invoke($formatter, new \DateTime('12.2.2014'), new \DateTime('12.2.2014'));

        $this->assertEquals(null, $diffPoint);

        $formatter->setFormat('H:i:s');
        $diffPoint = $method->invoke($formatter, new \DateTime('12.2.2014'), new \DateTime('12.2.2014'));
        $this->assertEquals(null, $diffPoint);


    }

    /**
     * @param $format
     * @param $diffPoint
     * @param $expected
     * @dataProvider splittingFormatProvider
     *
     */
    public function testSplittingFormat($format, $diffPoint, $expected)
    {
        $formatter = new DateRangeFormatter();
        $formatter->setFormat($format);

        $method = UnitTestHelper::getMethod($formatter, 'splitFormat');
        $splitFormat = $method->invoke($formatter, $diffPoint);

        $this->assertEquals($expected, $splitFormat);
    }

    public function splittingFormatProvider()
    {
        return array(
            array('d.m.Y', DateRangeFormatter::DAY, array('d.','m.Y')),
            array('d.m.Y', DateRangeFormatter::MONTH, array('d.m.','Y')),
            array('d.m.Y', DateRangeFormatter::YEAR, array('d.m.Y','')),
            array('m/d/Y', DateRangeFormatter::DAY, array('m/d/','Y')),
        );
    }

    public function testSplittingWrongFormat()
    {
        $formatter = new DateRangeFormatter();
        $formatter->setFormat('H:i:s');

        $method = UnitTestHelper::getMethod($formatter, 'splitFormat');
        $splitFormat = $method->invoke($formatter, DateRangeFormatter::DAY);

        $this->assertEquals(array('H:i:s', ''), $splitFormat);

    }

    /**
     * @param $format
     * @param $start
     * @param $end
     * @param $result
     * @dataProvider gettingDateDiffProvider
     */
    public function testGettingDateDiff($format, $start, $end, $result)
    {
        $formatter = new DateRangeFormatter();
        $formatter->setFormat($format);

        $this->assertEquals($result, $formatter->getDateRange(new \DateTime($start), new \DateTime($end)));
    }

    public function gettingDateDiffProvider()
    {
        return array(
            array('d.m.Y', '12.2.2014', '13.2.2014', '12. - 13.02.2014'),
            array('m/d/Y', '12.2.2014', '13.2.2014', '02/12/ - 02/13/2014'),
            array('d.m.Y', '12.2.2014', '12.2.2014', '12.02.2014'),
        );
    }

    public function testSettingSeparator()
    {
        $formatter = new DateRangeFormatter();
        $this->assertAttributeEquals(' - ', 'combine', $formatter);
        $this->assertSame($formatter, $formatter->setSeparator(' until '));
        $this->assertAttributeEquals(' until ', 'combine', $formatter);
    }
}
