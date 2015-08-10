<?php

namespace Box\Brainy\Compiler\Helpers;


class Text extends ParseTree
{
    /**
     * Create template text buffer
     *
     * @param object $parser parser object
     * @param string $data   text
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
     * Return buffer content
     *
     * @return strint text
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
