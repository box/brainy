<?php

namespace Box\Brainy\Compiler\Helpers;


class DoubleQuoted extends ParseTree
{
    /**
     * Create parse tree buffer for double quoted string subtrees
     *
     * @param object            $parser  parser object
     * @param ParseTree $subtree parsetree buffer
     */
    public function __construct($parser, ParseTree $subtree) {
        $this->parser = $parser;
        $this->subtrees[] = $subtree;
        if ($subtree instanceof Tag) {
            $this->parser->block_nesting_level = count($this->parser->compiler->_tag_stack);
        }
    }

    /**
     * Append buffer to subtree
     *
     * @param ParseTree $subtree parsetree buffer
     */
    public function append_subtree(ParseTree $subtree) {
        $last_subtree = count($this->subtrees) - 1;
        if ($last_subtree >= 0 && $this->subtrees[$last_subtree] instanceof Tag && $this->subtrees[$last_subtree]->saved_block_nesting < $this->parser->block_nesting_level) {
            if ($subtree instanceof Code) {
                $this->subtrees[$last_subtree]->data .= 'echo ' . $subtree->data . ';';
            } elseif ($subtree instanceof DoubleQuotedContent) {
                $this->subtrees[$last_subtree]->data .= $this->echo_data($subtree->data);
            } else {
                $this->subtrees[$last_subtree]->data .= $subtree->data;
            }
        } else {
            $this->subtrees[] = $subtree;
        }
        if ($subtree instanceof Tag) {
            $this->parser->block_nesting_level = count($this->parser->compiler->_tag_stack);
        }
    }

    /**
     * @return string
     */
    public function to_inline_data() {
        return $this->to_smarty_php();
    }

    /**
     * Merge subtree buffer content together
     *
     * @return string compiled template code
     */
    public function to_smarty_php() {
        $code = '';
        foreach ($this->subtrees as $subtree) {
            if ($code !== "") {
                $code .= ".";
            }

            $code .= $subtree->to_smarty_php();

            if (!$subtree instanceof DoubleQuotedContent) {
                $this->parser->compiler->has_variable_string = true;
            }
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
