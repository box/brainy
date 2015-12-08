<?php

namespace Box\Brainy\Compiler\Wrappers;

class SmartyVarPoisonWrapper
{

    public $type = null;

    /**
     * @param string $type The type of the lookup
     */
    public function __construct($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        throw new \Box\Brainy\Exceptions\SmartyCompilerException(
            '"$smarty.foreach" or "$smarty.capture" were used directly. This is not allowed.'
        );
    }
}
