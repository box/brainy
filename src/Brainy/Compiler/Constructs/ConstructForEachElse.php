<?php

namespace Box\Brainy\Compiler\Constructs;

class ConstructForEachElse extends BaseConstruct
{
    /**
     * @param  \Box\Brainy\Compiler\TemplateCompiler $compiler A compiler reference
     * @param  array|null                            $args     Arguments
     * @return mixed
     */
    public static function compileOpen(\Box\Brainy\Compiler\TemplateCompiler $compiler, $args)
    {
        self::closeTag($compiler, array('foreach'));
        self::openTag($compiler, 'foreachelse', array('foreachelse'));

        return "}\n} else {\n";
    }
}
