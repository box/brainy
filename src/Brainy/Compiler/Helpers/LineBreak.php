<?php

namespace Box\Brainy\Compiler\Helpers;


class LineBreak extends ParseTree
{
    /**
     * Create buffer with linebreak content
     *
     * @param object $parser parser object
     * @param string $data   linebreak string
     */
    public function __construct($parser, $data) {
        $this->parser = $parser;
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function to_inline_data() {
        return $this->escape_data($this->data);
    }

    /**
     * Return linebreak
     *
     * @return string linebreak
     */
    public function to_smarty_php() {
        return $this->echo_data();
    }

    /**
     * @return bool
     */
    public function can_combine_inline_data() {
        return true;
    }

}
