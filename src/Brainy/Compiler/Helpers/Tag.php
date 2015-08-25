<?php

namespace Box\Brainy\Compiler\Helpers;


class Tag extends ParseTree
{
    /**
     * Saved block nesting level
     * @var int
     */
    public $saved_block_nesting;

    /**
     * Create parse tree buffer for Smarty tag
     *
     * @param object $parser parser object
     * @param string $data   content
     */
    public function __construct($parser, $data) {
        $this->parser = $parser;
        $this->data = $data;
        $this->saved_block_nesting = $parser->block_nesting_level;
    }

    /**
     * @return string
     */
    public function to_inline_data() {
        return $this->data;
    }

    /**
     * Return buffer content
     *
     * @return string content
     */
    public function to_smarty_php() {
        return 'echo ' . $this->data . ';';
    }

    /**
     * @return bool
     */
    public function can_combine_inline_data() {
        return false;
    }

}
