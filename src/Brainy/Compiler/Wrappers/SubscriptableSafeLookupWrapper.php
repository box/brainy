<?php

namespace Box\Brainy\Compiler\Wrappers;

class SubscriptableSafeLookupWrapper extends SafeLookupWrapper
{
    /**
     * The __toString() method should always return the safe version.
     * @return string
     */
    public function __toString()
    {
        return '\Box\Brainy\Runtime\Lookups::safeVarLookup(' . $this->safeVersion . ', ' . $this->member . ')';
    }
}
