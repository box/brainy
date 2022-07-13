<?php

namespace Box\Brainy\Compiler\Wrappers;

class ArraySafeLookupWrapper extends SafeLookupWrapper
{

    /**
     * The __toString() method should always return the safe version.
     * @return string
     */
    public function __toString()
    {
        return '\Box\Brainy\Runtime\Lookups::safeArrayLookup(' . $this->safeVersion . ', ' . $this->member . ')';
    }
}
