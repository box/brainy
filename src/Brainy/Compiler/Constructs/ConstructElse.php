<?php

namespace Box\Brainy\Compiler\Constructs;

class ConstructElse extends BaseConstruct
{
    /**
     * @param  \Box\Brainy\Compiler\TemplateCompiler $compiler A compiler reference
     * @param  array|null                            $args     Arguments
     * @return mixed
     */
    public static function compileOpen(\Box\Brainy\Compiler\TemplateCompiler $compiler, $args)
    {
        self::closeTag($compiler, array('if', 'elseif'));
        self::openTag($compiler, 'elseif');
        return "} else {\n";
    }
}
