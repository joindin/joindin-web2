# org.heigl.DateRange

This small library tries to ease printing of date-ranges.

[![Build Status](https://travis-ci.org/heiglandreas/org.heigl.daterange.svg?branch=master)](https://travis-ci.org/heiglandreas/org.heigl.daterange)
[![Code Climate](https://codeclimate.com/github/heiglandreas/org.heigl.daterange/badges/gpa.svg)](https://codeclimate.com/github/heiglandreas/org.heigl.daterange)
[![Test Coverage](https://codeclimate.com/github/heiglandreas/org.heigl.daterange/badges/coverage.svg)](https://codeclimate.com/github/heiglandreas/org.heigl.daterange)
[![Coverage Status](https://coveralls.io/repos/heiglandreas/org.heigl.daterange/badge.svg?branch=master)](https://coveralls.io/r/heiglandreas/org.heigl.daterange?branch=master)

## Installation

Installation is easy via composer. Simply type this in your terminal to add the
DateRange-Library to your ```composer.conf```-file:

    composer require org_heigl/daterange


## Usage

You can then use the DateRange library by creating a DateRange-instance,
setting a format and a separator and then simply calling ```getDateRange()```
with the start-date and the end date as parameters.

Simple example:

```php
<?php
use Org_Heigl\DateRangeFormatter

$dateRange = new DateRangeFormatter();
$dateRange->setFormat('d.m.Y');
$dateRange->setSeparator(' - ');
echo $dateRange->getDateRange(new \DateTime('12.3.2015'), new \DateTime('13.4.2015'));
// Will print: 12.03. - 13.04.2014
echo $dateRange->getDateRange(new \DateTime('12.3.2015'), new \DateTime('13.3.2015'));
// Will print: 12. - 13.03.2014
```

More complex example:

```php
<?php
use Org_Heigl\DateRangeFormatter

$dateRange = new DateRangeFormatter();
$dateRange->setFormat('m/d/Y');
$dateRange->setSeparator(' - ');
echo $dateRange->getDateRange(new \DateTime('12.3.2015'), new \DateTime('13.3.2015'));
// Will print: 3/12/ - 3/13/2014
```
