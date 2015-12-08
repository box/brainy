<?php

namespace Box\Brainy\Compiler\Constructs;

class ConstructContinue extends BaseConstruct
{
    /**
     * @param  \Box\Brainy\Compiler\TemplateCompiler $compiler A compiler reference
     * @param  array|null                            $args     Arguments
     * @return mixed
     */
    public static function compileOpen(\Box\Brainy\Compiler\TemplateCompiler $compiler, $args)
    {
        $levels = self::getOptionalArg($args, 'levels');

        if (!$levels && isset($args[0])) {
            $compiler->assertIsNotStrict('Continue shorthand is not allowed in strict mode. Use the levels="" attribute instead.');
            $levels = $args[0];
        }

        if ($levels) {
            return "continue $levels;\n";
        }

        return "continue;\n";
    }
}
