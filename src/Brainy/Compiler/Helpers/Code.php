<?php

namespace Box\Brainy\Compiler\Helpers;


class Code extends ParseTree
{
    /**
     * Create parse tree buffer for code fragment
     *
     * @param object $parser parser object
     * @param string $data   content
     */
    public function __construct($parser, $data) {
        $this->parser = $parser;
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function to_inline_data() {
        return $this->to_smarty_php();
    }

    /**
     * Return buffer content in parentheses
     *
     * @return string content
     */
    public function to_smarty_php() {
        return sprintf("(%s)", $this->data);
    }

    /**
     * @return bool
     */
    public function can_combine_inline_data() {
        return false;
    }

}
