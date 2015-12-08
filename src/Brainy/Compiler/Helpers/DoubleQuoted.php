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
    public function appendSubtree(ParseTree $subtree)
    {
        if ($subtree instanceof Tag) {
            throw new \Box\Brainy\Exceptions\SmartyCompilerException('Cannot use tags inside double quoted strings');
        }
        $this->subtrees[] = $subtree;
    }

    /**
     * @return string
     */
    public function toInlineData()
    {
        return $this->toSmartyPHP();
    }

    /**
     * @return string compiled template code
     */
    public function toSmartyPHP()
    {
        $code = '';
        $buffer = '';
        foreach ($this->subtrees as $subtree) {
            if ($subtree->canCombineInlineData()) {
                $buffer .= $subtree->toInlineData();
                continue;
            }

            if ($code) {
                $code .= '.';
            }

            if ($buffer) {
                $code .= $this->escapeData($buffer) . '.';
                $buffer = '';
            }

            $code .= $subtree->toSmartyPHP();

        }

        if ($buffer) {
            if ($code) {
                $code .= '.';
            }
            $code .= $this->escapeData($buffer);
        }

        return $code;
    }

    /**
     * @return bool
     */
    public function canCombineInlineData()
    {
        return false;
    }
}
