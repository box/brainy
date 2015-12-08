<?php

namespace Box\Brainy\Compiler\Helpers;


class DoubleQuoted extends ParseTree
{

    private $parser;
    private $subtrees = array();

    /**
     * @param object $parser parser object
     */
    public function __construct($parser)
    {
        $this->parser = $parser;
    }

    /**
     * @param ParseTree $subtree parsetree buffer
     */
    public function append_subtree(ParseTree $subtree)
    {
        if ($subtree instanceof Tag) {
            throw new \Box\Brainy\Exceptions\SmartyCompilerException('Cannot use tags inside double quoted strings');
        }
        $this->subtrees[] = $subtree;
    }

    /**
     * @return string
     */
    public function to_inline_data()
    {
        return $this->to_smarty_php();
    }

    /**
     * @return string compiled template code
     */
    public function to_smarty_php()
    {
        $code = '';
        $buffer = '';
        foreach ($this->subtrees as $subtree) {
            if ($subtree->can_combine_inline_data()) {
                $buffer .= $subtree->to_inline_data();
                continue;
            }

            if ($code) {
                $code .= '.';
            }

            if ($buffer) {
                $code .= $this->escape_data($buffer) . '.';
                $buffer = '';
            }

            $code .= $subtree->to_smarty_php();

        }

        if ($buffer) {
            if ($code) {
                $code .= '.';
            }
            $code .= $this->escape_data($buffer);
        }

        return $code;
    }

    /**
     * @return bool
     */
    public function can_combine_inline_data()
    {
        return false;
    }

}
