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
        throw new \Box\Brainy\Exceptions\SmartyException('Template buffer cast to inline template data');
    }

    /**
     * Sanitize and merge subtree buffers together
     *
     * @return string template code content
     */
    public function to_smarty_php() {
        $code = '';
        $buffer = '';
        foreach ($this->subtrees as $node) {
            if ($node->can_combine_inline_data()) {
                $buffer .= $node->to_inline_data();
                continue;
            }

            if ($buffer !== '') {
                $code .= $this->echo_data(var_export($buffer, true));
                $buffer = '';
            }

            $code .= $node->to_smarty_php();
        }

        if ($buffer !== '') {
            $code .= $this->echo_data(var_export($buffer, true));
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
