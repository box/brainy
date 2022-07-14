<?php

namespace Box\Brainy\Compiler\Wrappers;

abstract class SafeLookupWrapper
{
    protected $unsafeVersion;
    protected $safeVersion;
    protected $member;

    /**
     * @param string $unsafe
     * @param string $safe
     * @param string $member
     */
    public function __construct($unsafe, $safe, $member)
    {
        $this->unsafeVersion = $unsafe;
        $this->safeVersion = $safe;
        $this->member = $member;
    }

    /**
     * @return string
     */
    public function getUnsafeRecursive()
    {
        return $this->unsafeVersion . '[' . $this->member . ']';
    }

    /**
     * @return string
     */
    public function getUnsafe()
    {
        return $this->safeVersion . '[' . $this->member . ']';
    }
}
