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
    public function appendSubtree(ParseTree $subtree)
    {
        $this->subtrees[] = $subtree;
    }

    /**
     * @return string
     */
    public function toInlineData()
    {
        throw new \Box\Brainy\Exceptions\SmartyException('Template buffer cast to inline template data');
    }

    /**
     * Sanitize and merge subtree buffers together
     *
     * @return string template code content
     */
    public function toSmartyPHP()
    {
        $code = '';
        $buffer = '';
        foreach ($this->subtrees as $node) {
            if ($node->canCombineInlineData()) {
                $buffer .= $node->toInlineData();
                continue;
            }

            if ($buffer !== '') {
                $code .= $this->echoData(var_export($buffer, true));
                $buffer = '';
            }

            $code .= $node->toSmartyPHP();
        }

        if ($buffer !== '') {
            $code .= $this->echoData(var_export($buffer, true));
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
