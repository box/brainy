<?php

namespace Box\Brainy\Compiler\Constructs;

use \Box\Brainy\Brainy;


class ConstructElseIf extends BaseConstruct
{
    /**
     * @param  \Box\Brainy\Compiler\TemplateCompiler $compiler A compiler reference
     * @param  array|null  $args     Arguments
     * @param  array|null  $params   Parameters
     * @return mixed
     */
    public static function compileOpen(\Box\Brainy\Compiler\TemplateCompiler $compiler, array $args, array $params)
    {
        $cond = self::getRequiredArg($args, 'cond');
        self::closeTag($compiler, array('if', 'elseif'));
        self::openTag($compiler, 'elseif');
        return "} elseif ($cond) {\n";
    }
}
