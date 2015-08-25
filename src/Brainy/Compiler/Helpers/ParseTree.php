<?php

namespace Box\Brainy\Compiler\Helpers;


abstract class ParseTree
{
    /**
     * Parser object
     * @var object
     */
    public $parser;
    /**
     * Buffer content
     * @var mixed
     */
    public $data;

    /**
     * Return buffer
     *
     * @return string buffer content
     */
    abstract public function to_smarty_php();

    /**
     * Return buffer
     *
     * @return string buffer content
     */
    abstract public function to_inline_data();

    /**
     * @param string|null|void $data
     * @return string
     */
    public function echo_data($data = null) {
        if (is_null($data)) {
            $data = $this->to_inline_data();
        }
        $data = var_export($data);
        return "echo $data;\n";
    }

    /**
     * Return escaped data
     *
     * @param string $toEscape
     * @return string escaped string
     */
    protected function escape_data($toEscape) {
        $out = str_replace("\\", '\\\\', $toEscape);
        $out = str_replace("\n", '\n', $out);
        $out = str_replace("\r", '\r', $out);
        $out = str_replace("\t", '\t', $out);
        $out = str_replace('$', '\$', $out);
        $out = str_replace('"', '\"', $out);
        return $out;
    }

    /**
     * @return bool
     */
    abstract public function can_combine_inline_data();

}
