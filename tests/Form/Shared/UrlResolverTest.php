<?php

namespace Tests\Form\Shared;

use Form\Exception\UnresolvableUrl;
use Form\Shared\UrlResolver;
use PHPUnit\Framework\TestCase;

class UrlResolverTest extends TestCase
{

    /** @dataProvider resolveProvider */
    public function testPositiveResolve($base, $resolved)
    {
        $resolver = new UrlResolver();

        $this->assertSame($resolved, $resolver->resolve($base));
        $this->assertSame($resolved, $resolver->resolve($base));
    }

    /** @dataProvider failingResolveProvider */
    public function testFailingResolve($base)
    {
        $resolver = new UrlResolver();

        $this->expectException(UnresolvableUrl::class);
        $resolver->resolve($base);
    }

    public static function resolveProvider() : array
    {
        return [[
            'http://apple.de',
            'https://www.apple.com/de/'
        ]];
    }

    public static function failingResolveProvider() : array
    {
        return [[
            'http://example.unresolvable',
        ]];
    }
}
