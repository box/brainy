<?php

namespace Box\Brainy\Compiler\Constructs;

class ConstructCapture extends ClosedBaseConstruct
{
    /**
     * @param  \Box\Brainy\Compiler\TemplateCompiler $compiler A compiler reference
     * @param  array|null                            $args     Arguments
     * @return mixed
     */
    public static function compileOpen(\Box\Brainy\Compiler\TemplateCompiler $compiler, $args)
    {
        $name = self::getOptionalArg($args, 'name');
        $assign = self::getOptionalArg($args, 'assign');

        if (!$name && !$assign && isset($args[0])) {
            $compiler->assert_is_not_strict('Capture shorthand is not allowed in strict mode. Use the name="" attribute instead.');
            $name = $args[0];
        }

        if (!$name && !$assign) {
            $name = "'default'";
        }

        if ($name && $assign) {
            throw new \Box\Brainy\Exceptions\SmartyCompilerException('{capture} tags may not set both `name` and `assign`');
        }

        self::openTag($compiler, 'capture', array($name, $assign));
        return "ob_start();\n";
    }

    /**
     * @param  \Box\Brainy\Compiler\TemplateCompiler $compiler A compiler reference
     * @param  array|null                            $args     Arguments
     * @return mixed
     */
    public static function compileClose(\Box\Brainy\Compiler\TemplateCompiler $compiler, $args)
    {
        list($name, $assign) = self::closeTag($compiler, array('capture'));

        if (isset($assign)) {
            return '$_smarty_tpl->setVariable(' . $assign . ', ob_get_clean());';
        } elseif (isset($name)) {
            $output = '';
            $output .= "if (!array_key_exists('capture', \$_smarty_tpl->tpl_vars['smarty']->value)) \$_smarty_tpl->tpl_vars['smarty']->value['capture'] = array();\n";
            $output .= '$_smarty_tpl->tpl_vars[\'smarty\']->value[\'capture\'][' . $name . '] = ob_get_clean();';
            return $output;
        }

    }
}
