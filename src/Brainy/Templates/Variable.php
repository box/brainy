<?php

namespace Box\Brainy\Templates;


class Variable
{
    /**
     * @var mixed
     */
    public $value = null;

    /**
     * the scope the variable will have  (local,parent or root)
     */
    public $scope = \Box\Brainy\Brainy::SCOPE_LOCAL;

    /**
     * create Smarty variable object
     *
     * @param mixed   $value   the value to assign
     * @param int     $scope   the scope the variable will have  (local,parent or root)
     */
    public function __construct($value = null, $scope = \Box\Brainy\Brainy::SCOPE_LOCAL) {
        $this->value = $value;
        $this->scope = $scope;
    }

    /**
     * @return string
     */
    public function __toString() {
        return (string) $this->value;
    }

}
