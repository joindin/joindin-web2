<?php

namespace Application;

class Config implements \ArrayAccess
{
    protected $settings;

    public function __construct(array $settings = [])
    {
        $this->settings = $settings;
    }

    public function offsetSet($offset, $value): void
    {
        throw new \Exception("You cannot set a config value!");
    }
    public function offsetExists($offset): bool
    {
        return isset($this->settings[$offset]);
    }
    public function offsetUnset($offset): void
    {
        throw new \Exception("You cannot unset a config value!");
    }
    public function offsetGet($offset): mixed
    {
        return array_key_exists($offset, $this->settings) ? $this->settings[$offset] : null;
    }
}
