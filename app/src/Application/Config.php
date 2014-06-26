<?php
/**
 * Created by PhpStorm.
 * User: jcarmony
 * Date: 6/26/14
 * Time: 8:35 PM
 */

namespace Application;

class Config implements \ArrayAccess
{
    protected $settings = array();

    public function __construct($settings){
        $this->settings = $settings;
    }

    public function offsetSet($key, $value) {
        throw new \Exception("You cannot set a config value!");
    }
    public function offsetExists($key) {
        return isset($this->settings[$key]);
    }
    public function offsetUnset($offset) {
        throw new \Exception("You cannot unset a config value!");
    }
    public function offsetGet($key) {
        return isset($this->settings[$key]) ? $this->settings[$key] : null;
    }
}