<?php

namespace Box\Brainy\Compiler\Constructs;

use \Box\Brainy\Exceptions\SmartyCompilerException;


abstract class ClosedBaseConstruct extends BaseConstruct
{
    /**
     * Compiles the closing tag for a function
     * @param  \Box\Brainy\Compiler\TemplateCompiler $compiler A compiler reference
     * @param  array|null  $args     Arguments
     * @return mixed
     */
    public static function compileClose(\Box\Brainy\Compiler\TemplateCompiler $compiler, $args)
    {
        throw new \Exception('Not Implemented!');
    }

}
