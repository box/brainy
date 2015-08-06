<?php

namespace Box\Brainy\Templates;


class UndefinedVariable {

    /**
     * @param  string $name
     * @return null
     */
    public function __get($name) {
        return null;
    }

    /**
     * @return string
     */
    public function __toString() {
        return '';
    }

}
