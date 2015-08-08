<?php

namespace Box\Brainy\Templates;


class LoopVariable extends Variable
{
    public $step = 1;
    public $total = INF;
    public $value = 0;
    public $iteration = 0;

    public $first = false;
    public $last = false;

    /**
     * @param int|float   $step
     * @param int|float   $total
     */
    public function __construct($step, $total) {
        $this->step = $step;
        $this->total = $total;
    }

    /**
     * @return string
     */
    public function __toString() {
        return 'loop';
    }

}
