<?php

namespace Box\Brainy\Compiler\Constructs;

class ConstructBreak extends BaseConstruct
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
            $compiler->assertIsNotStrict('Break shorthand is not allowed in strict mode. Use the levels="" attribute instead.');
            $levels = $args[0];
        }

        if ($levels) {
            return "break $levels;\n";
        }

        return "break;\n";
    }
}
