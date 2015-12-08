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
    abstract public function toSmartyPHP();

    /**
     * Return the raw string contents of the node
     *
     * @return string
     */
    abstract public function toInlineData();

    /**
     * @param string $data
     * @return string
     */
    public function echoData($data)
    {
        return "echo $data;\n";
    }

    /**
     * Return escaped data
     *
     * @param  string $toEscape
     * @return string escaped string
     */
    protected function escapeData($toEscape)
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
    abstract public function canCombineInlineData();
}
