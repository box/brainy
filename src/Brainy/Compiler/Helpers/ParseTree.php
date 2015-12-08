<?php

namespace Box\Brainy\Compiler\Helpers;


abstract class ParseTree
{
    /**
     * Buffer content
     * @var mixed
     */
    public $data;

    /**
     * Returns a PHP expression
     *
     * @return string
     */
    abstract public function to_smarty_php();

    /**
     * Return the raw string contents of the node
     *
     * @return string
     */
    abstract public function to_inline_data();

    /**
     * @param string $data
     * @return string
     */
    public function echo_data($data) 
    {
        return "echo $data;\n";
    }

    /**
     * Return escaped data
     *
     * @param  string $toEscape
     * @return string escaped string
     */
    protected function escape_data($toEscape) 
    {
        $toEscape = (string) $toEscape;
        // Escape the data
        $data = var_export($toEscape, true);
        // $data = mb_substr($data, 1, mb_strlen($data) - 2);
        return $data;
    }

    /**
     * @return bool
     */
    abstract public function can_combine_inline_data();

}
