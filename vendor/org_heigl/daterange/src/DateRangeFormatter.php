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

namespace Org_Heigl\DateRange;

use DateInterval;
use DateTimeInterface;

class DateRangeFormatter
{
    const YEAR = 0;
    const MONTH = 1;
    const WEEK = 2;
    const DAY = 3;

    private $checks = array(
        'd' => self::DAY,
        'D' => self::DAY,
        'j' => self::DAY,
        'l' => self::DAY,
        'N' => self::DAY,
        'S' => self::DAY,
        'w' => self::DAY,
        'z' => self::DAY,
        'W' => self::WEEK,
        'F' => self::MONTH,
        'm' => self::MONTH,
        'M' => self::MONTH,
        'n' => self::MONTH,
        'o' => self::YEAR,
        'Y' => self::YEAR,
        'y' => self::YEAR,
    );

    protected $format = 'd. m. Y';

    private $testableParts = array(
        self::DAY, self::MONTH, self::YEAR
    );

    protected $combine = ' - ';

    /**
     * Set the output-format
     *
     * This can be a formating string as for the date()-function.
     *
     * @param string $format
     *
     * @return self
     */
    public function setFormat($format)
    {
        $this->format = $format;

        $this->setTestableParts();

        return $this;
    }

    /**
     * Set the separator
     *
     * @param string $separator
     *
     * @return self
     */
    public function setSeparator($separator)
    {
        $this->combine = $separator;

        return $this;
    }

    public function setTestableParts()
    {
        $this->testableParts = array();

        foreach ($this->checks as $key => $value) {
            if (in_array($value, $this->testableParts)) {
                continue;
            }

            if (preg_match('/(?<!\\\\)' . $key . '/', $this->format)) {
                $this->testableParts[] = $value;
            }
        }
    }

    protected function getDiffPoint(DateTimeInterface $start, DateTimeInterface $end)
    {
        $tester = array(
            self::YEAR      => 'Y',
            self::MONTH     => 'n',
            self::WEEK      => 'W',
            self::DAY       => 'j',
        );
        foreach ($tester as $part => $test) {
            if (! in_array($test, $this->testableParts)) {
                continue;
            }

            if ($start->format($test) != $end->format($test)) {
                return $part;
            }
        }

        return null;
    }

    protected function splitFormat($diffPoint)
    {
        $broke = $stable = array();
        foreach ($this->checks as $check => $mate) {
            if ($mate >= $diffPoint) {
                $broke[] = $check;
            } else {
                $stable[] = $check;
            }
        }
        if (! $stable) {
            return array($this->format, '');
        }
        $regEx = '/(?<!\\\\)([' . implode('|', $stable) . '])(?!.*[' . implode('|', $broke) . '])/';
        if (! preg_match($regEx, $this->format, $matches, PREG_OFFSET_CAPTURE)) {
            return array($this->format, '');
        }

        $offset = $matches[0][1];

        return array(substr($this->format, 0, $offset), substr($this->format, $offset));
    }

    public function getDateRange(DateTimeInterface $start, DateTimeInterface $end)
    {
        $arrays = $this->splitFormat($this->getDiffPoint($start, $end));
        if (! $arrays[1]) {
            return $start->format($arrays[0]);
        }
        $string = '';
        $string .= $start->format($arrays[0]);
        $string .= $this->combine;
        $string .= $end->format($arrays[0]);
        $string .= $end->format($arrays[1]);
        
        return $string;
    }
}
