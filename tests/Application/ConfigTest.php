<?php

namespace Application;

use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    /** @var Config */
    protected $config;

    public function setUp(): void
    {
        $this->config = new Config(
            [
                'db'              => 'mysql',
                'engine'          => 'fast',
                'testing'         => true,
                'another setting' => 17,
            ]
        );
    }

    public function testConfigSetThrowsException(): void
    {
        $this->expectException(\Exception::class);
        $this->config->offsetSet('qwerty', mt_rand(10, 99));
    }

    public function testConfigUnsetThrowsException(): void
    {
        $this->expectException(\Exception::class);
        $this->config->offsetUnset('anything');
    }

    public function testConfigExistsReturnsCorrectBool(): void
    {
        $this->assertTrue($this->config->offsetExists('db'));
        $this->assertFalse($this->config->offsetExists('csv'));
    }

    /**
     * @dataProvider getSettings
     */
    public function testConfigCanGetSetting($settings, $key, $expected): void
    {
        $config = new Config($settings);
        $value  = $config->offsetGet($key);

        $this->assertEquals($expected, $value);
    }

    public function testConfigGetReturnsNullWhenNotFound(): void
    {
        $value = $this->config->offsetGet('not found');

        $this->assertNull($value);
    }

    public function getSettings(): array
    {
        return [
            [['abc' => 123], 'abc', 123],
            [[123], 0, 123],
            [['blarg' => 'some config'], 'blarg', 'some config'],
        ];
    }
}
