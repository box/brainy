<?php

namespace Box\Brainy\Runtime;


class OverlayScope implements \ArrayAccess
{
    protected $overlaid = array();
    protected $base;
    protected $written = false;

    /**
     * @param \Box\Brainy\Templates\Variable[] &$base
     */
    public function __construct(&$base)
    {
        $this->base = &$base;
    }

    /**
     * Set the value of a variable in the scope
     * @param  string|int $offset
     * @param  mixed $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->overlaid[$offset] = $value;
        $this->written = true;
    }

    /**
     * Test if a value exists
     * @param  string|int $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->base[$offset]) || $this->written && isset($this->overlaid[$offset]);
    }

    /**
     * Remove a value that exists
     * @param  string|int $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        if (!$this->written) return;
        if (isset($this->overlaid[$offset])) {
            $this->overlaid[$offset] = null;
        }
    }

    /**
     * Return a value from the scope
     * @param  string|int $offset
     * @return \Box\Brainy\Templates\Variable|null
     */
    public function offsetGet($offset)
    {
        if ($this->written && isset($this->overlaid[$offset])) return $this->overlaid[$offset];
        if (isset($this->base[$offset])) return $this->base[$offset];
        return null;
    }

}
