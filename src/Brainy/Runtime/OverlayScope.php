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
        // This might seem kind of wasteful in terms of perf. I.e., you might
        // assume something like this would be faster:
        //
        // return isset($this->base[$offset]) || $this->written && isset($this->overlaid[$offset])
        //
        // In actual fact, it's not very good in practice. This is because more
        // often than not, $this->base is another OverlayScope object. That
        // causes this weird cascade to happen on every single lookup.
        if ($this->written) {
            return isset($this->overlaid[$offset]) || isset($this->base[$offset]);
        } else {
            return isset($this->base[$offset]);
        }
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
        // We don't test with isset() because that should have been done outside of this.
        $out = $this->base[$offset];
        if ($out !== null) {
            // Cache the value for future lookups.
            $this->offsetSet($offset, $out);
        }
        return $out;
    }

}
