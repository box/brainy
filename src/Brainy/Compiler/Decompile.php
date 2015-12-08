<?php

namespace Box\Brainy\Compiler;

class Decompile
{
    /**
     * @param  mixed $value
     * @return boolean
     */
    public static function isCompiledString($value)
    {
        $value = (string) $value;
        return count(token_get_all($value)) === 1 &&
            ($value[0] === '"' || $value[0] === "'");
    }

    /**
     * @param  mixed $value
     * @return string
     */
    public static function decompileString($value)
    {
        if (is_numeric($value)) {
            return (string) $value;
        }
        $value = (string) $value;

        if (!self::isCompiledString($value)) {
            throw new \Box\Brainy\Exceptions\SmartyCompilerException('Expected static string, got "' . $value . '"');
        }

        if ($value[0] === "'" && $value[strlen($value) - 1] === "'") {
            $value = '"' . substr($value, 1, -1) . '"';
        }
        return json_decode($value);
    }
}
