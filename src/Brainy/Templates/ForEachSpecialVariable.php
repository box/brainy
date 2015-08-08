<?php

namespace Box\Brainy\Templates;


class ForEachSpecialVariable extends Variable
{
    public $total = 0;
    public $iteration = 0;
    public $index = 0;
    public $show = false;
    public $_loop = false;

    public $first = false;
    public $last = false;

    /**
     * @return string
     */
    public function __toString() {
        return 'foreach';
    }

}
