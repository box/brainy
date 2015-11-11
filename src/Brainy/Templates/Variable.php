<?php

namespace Box\Brainy\Templates;


class Variable
{
    /**
     * @var mixed
     */
    public $value = null;

    /**
     * create Smarty variable object
     *
     * @param mixed   $value   the value to assign
     */
    public function __construct($value = null) {
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function __toString() {
        return (string) $this->value;
    }

}
