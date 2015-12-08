<?php

namespace Box\Brainy\Compiler\Wrappers;

class SafeLookupWrapper
{
    private $unsafeVersion;
    private $safeVersion;

    /**
     * @param string $unsafe
     * @param string $safe
     */
    public function __construct($unsafe, $safe)
    {
        $this->unsafeVersion = $unsafe;
        $this->safeVersion = $safe;
    }

    /**
     * @return string
     */
    public function getUnsafe()
    {
        return $this->unsafeVersion;
    }

    /**
     * The __toString() method should always return the safe version.
     * @return string
     */
    public function __toString()
    {
        return $this->safeVersion;
    }
}
