<?php

namespace Box\Brainy\Compiler\Helpers;

class DoubleQuotedContent extends ParseTree
{
    /**
     * Create parse tree buffer with string content
     *
     * @param object $parser parser object
     * @param string $data   string section
     */
    public function __construct($data)
    {
        $this->data = stripcslashes($data);
    }

    /**
     * @return string
     */
    public function toInlineData()
    {
        // echo $this->data, stripslashes($this->data), var_export($this->data, true);exit;
        return $this->data;
    }

    /**
     * @return string doubled quoted string
     */
    public function toSmartyPHP()
    {
        return var_export($this->data, true);
    }

    /**
     * @return bool
     */
    public function canCombineInlineData()
    {
        return true;
    }
}
