<?php

namespace Symfony\Component\Validator\Tests\Mapping\Loader;

use Symfony\Component\Validator\Mapping\ClassMetadata;

abstract class AbstractStaticMethodLoader
{
    abstract public static function loadMetadata(ClassMetadata $metadata);
}
