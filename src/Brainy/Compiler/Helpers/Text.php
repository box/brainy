<?php

namespace Box\Brainy\Compiler\Helpers;


class Text extends ParseTree
{
    /**
     * @param string $data   text
     */
    public function __construct($data) {
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function to_inline_data() {
        return $this->data;
    }

    /**
     * @return strint text
     */
    public function to_smarty_php() {
        return var_export($this->data, true);
    }

    /**
     * @return bool
     */
    public function can_combine_inline_data() {
        return true;
    }

}
