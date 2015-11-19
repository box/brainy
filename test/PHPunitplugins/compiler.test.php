<?php
// compiler.test.php
class smarty_compiler_test extends \Box\Brainy\Compiler\Constructs\BaseConstruct
{
    public static function compileOpen(\Box\Brainy\Compiler\TemplateCompiler $compiler, $args)
    {
        self::getRequiredArg($args, 'data');

        self::openTag($compiler, 'test');

        return "echo 'test output';\n";
    }

    public static function compileClose(\Box\Brainy\Compiler\TemplateCompiler $compiler, $args)
    {
        self::closeTag($compiler, 'test');
        return '';
    }

}
