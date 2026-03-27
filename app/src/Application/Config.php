<?php

namespace Application;

class Config implements \ArrayAccess
{
    protected array $settings;

    public function __construct(array $settings = [])
    {
        $this->settings = $settings;
    }

    public function offsetSet($key, $value)
    {
        throw new \Exception("You cannot set a config value!");
    }

    public function offsetExists($key)
    {
        return isset($this->settings[$key]);
    }

    public function offsetUnset($offset)
    {
        throw new \Exception("You cannot unset a config value!");
    }

    public function offsetGet($key)
    {
        return $this->settings[$key] ?? null;
    }
}
