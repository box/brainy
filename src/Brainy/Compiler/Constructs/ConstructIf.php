<?php

namespace Box\Brainy\Compiler\Constructs;

use \Box\Brainy\Brainy;


class ConstructIf extends ClosedBaseConstruct
{
    /**
     * Compiles the opening tag for a function
     * @param  \Box\Brainy\Compiler\TemplateCompiler $compiler A compiler reference
     * @param  array|null  $args     Arguments
     * @return mixed
     */
    public static function compileOpen(\Box\Brainy\Compiler\TemplateCompiler $compiler, $args)
    {
        $cond = self::getRequiredArg($args, 'cond');
        self::openTag($compiler, 'if');
        return "if ($cond) {\n";
    }

    /**
     * Compiles the closing tag for a function
     * @param  \Box\Brainy\Compiler\TemplateCompiler $compiler A compiler reference
     * @param  array|null  $args     Arguments
     * @return mixed
     */
    public static function compileClose(\Box\Brainy\Compiler\TemplateCompiler $compiler, $args)
    {
        self::closeTag($compiler, array('if', 'elseif', 'else'));
        return "}\n";
    }
}
