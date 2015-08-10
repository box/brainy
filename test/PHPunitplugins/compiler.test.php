<?php
// compiler.test.php
class smarty_compiler_test extends \Box\Brainy\Compiler\Constructs\BaseConstruct
{
    public static function compileOpen($compiler, $args, $params)
    {
        self::getRequiredArg($args, 'data');

        self::openTag($compiler, 'test');

        return "echo 'test output';\n";
    }

    public static function compileClose($compiler, $args, $params)
    {
        self::closeTag($compiler, 'test');
        return '';
    }

}
