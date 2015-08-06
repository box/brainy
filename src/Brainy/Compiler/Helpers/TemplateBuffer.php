<?php

namespace Box\Brainy\Compiler\Helpers;


class TemplateBuffer extends ParseTree
{
    /**
     * Array of template elements
     *
     * @var array
     */
    public $subtrees = array();

    /**
     * Create root of parse tree for template elements
     *
     * @param object $parser parse object
     */
    public function __construct($parser) {
        $this->parser = $parser;
    }

    /**
     * Append buffer to subtree
     *
     * @param _smarty_parsetree $subtree
     */
    public function append_subtree(ParseTree $subtree) {
        $this->subtrees[] = $subtree;
    }

    /**
     * @return string
     */
    public function to_inline_data() {
        $code = '';
        for ($key = 0, $cnt = count($this->subtrees); $key < $cnt; $key++) {
            $code .= $this->subtrees[$key]->to_inline_data();
        }

        return $code . "\n";
    }

    /**
     * Sanitize and merge subtree buffers together
     *
     * @return string template code content
     */
    public function to_smarty_php() {
        $code = '';
        $buffer = '';
        for ($key = 0, $cnt = count($this->subtrees); $key < $cnt; $key++) {
            if ($key + 2 < $cnt &&
                $this->subtrees[$key] instanceof LineBreak &&
                $this->subtrees[$key + 1] instanceof Tag &&
                $this->subtrees[$key + 1]->data === '' &&
                $this->subtrees[$key + 2] instanceof LineBreak) {

                $key++;
                continue;
            }
            $node = $this->subtrees[$key];
            if ($node->can_combine_inline_data()) {
                $buffer .= $node->to_inline_data();
                continue;
            }
            if ($buffer !== '') {
                $code .= $this->echo_data($buffer);
                $buffer = '';
            }
            $code .= $node->to_smarty_php();
        }
        if ($buffer !== '') {
            $code .= $this->echo_data($buffer);
        }
        return $code;
    }

    /**
     * @return bool
     */
    public function can_combine_inline_data() {
        return false;
    }

}
