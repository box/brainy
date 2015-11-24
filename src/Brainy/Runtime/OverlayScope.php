<?php

namespace Box\Brainy\Runtime;


class OverlayScope implements \ArrayAccess
{
    protected $overlaid = array();
    protected $base;
    protected $written = false;

    public function __construct(&$base)
    {
        $this->base = &$base;
    }

    public function offsetSet($offset, $value)
    {
        $this->overlaid[$offset] = $value;
        $this->written = true;
    }

    public function offsetExists($offset)
    {
        return isset($this->base[$offset]) || $this->written && isset($this->overlaid[$offset]);
    }

    public function offsetUnset($offset)
    {
        if (!$this->written) return;
        if (isset($this->overlaid[$offset])) {
            $this->overlaid[$offset] = null;
        }
    }

    public function offsetGet($offset)
    {
        if ($this->written && isset($this->overlaid[$offset])) return $this->overlaid[$offset];
        if (isset($this->base[$offset])) return $this->base[$offset];
        return null;
    }

}
