<?php

namespace Box\Brainy\Compiler\Helpers;

class Text extends ParseTree
{
    /**
     * @param string $data text
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function toInlineData()
    {
        return $this->data;
    }

    /**
     * @return strint text
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
