<?php

namespace Box\Brainy\Runtime;


class OverlayScope implements \ArrayAccess
{
    protected $overlaid = array();
    protected $base;

    public function __construct(&$base)
    {
        $this->base = &$base;
    }

    public function offsetSet($offset, $value)
    {
        $this->overlaid[$offset] = $value;
    }

    public function offsetExists($offset)
    {
        return isset($this->overlaid[$offset]) || isset($this->base[$offset]);
    }

    public function offsetUnset($offset)
    {
        if (isset($this->overlaid[$offset])) {
            $this->overlaid[$offset] = null;
        }
    }

    public function offsetGet($offset)
    {
        if (isset($this->overlaid[$offset])) return $this->overlaid[$offset];
        if (isset($this->base[$offset])) return $this->base[$offset];
        return null;
    }

}
