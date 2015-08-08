<?php

namespace Box\Brainy\Compiler\Constructs;

use \Box\Brainy\Brainy;


class ConstructForEach extends BaseConstruct
{
    /**
     * @param  \Box\Brainy\Compiler\TemplateCompiler $compiler A compiler reference
     * @param  array|null  $args     Arguments
     * @param  array|null  $params   Parameters
     * @return mixed
     */
    public static function compileOpen(\Box\Brainy\Compiler\TemplateCompiler $compiler, array $args, array $params)
    {
        $from = self::getRequiredArg($args, 'from');
        $item = self::getRequiredArg($args, 'item');
        $key = self::getOptionalArg($args, 'key');

        $name = self::getOptionalArg($args, 'name');

        $usages = self::getUsages($compiler, $name);

        $output = "if (!empty($from)) {\n";
        $output .= "\$_smarty_tpl->tpl_vars[$item] = new \\Box\\Brainy\\Templates\\ForEachSpecialVariable();\n";
        if ($name) {
            $output .= "\$_smarty_tpl->tpl_vars['smarty']->value['foreach'][$name] = &\$_smarty_tpl->tpl_vars[$item];\n";
        }
        if ($key) {
            $output .= "\$_smarty_tpl->tpl_vars[$key] = new \\Box\\Brainy\\Templates\\Variable();\n";
        }

        $output .= "foreach ($from as \$_smarty_tpl->tpl_vars[$key]->value => \$_smarty_tpl->tpl_vars[$item]->value) {\n";

        self::openTag($compiler, 'foreach');

        return $output;
    }

    /**
     * Compiles the closing tag for a function
     * @param  \Box\Brainy\Compiler\TemplateCompiler $compiler A compiler reference
     * @param  array|null  $args     Arguments
     * @param  array|null  $params   Parameters
     * @return mixed
     */
    public static function compileClose(\Box\Brainy\Compiler\TemplateCompiler $compiler, array $args, array $params)
    {
        list($openTag) = self::closeTag($compiler, array('foreach', 'foreachelse'));

        if ($openTag == 'foreachelse') {
            return "}\n";
        } else {
            return "}\n}\n";
        }
    }

    /**
     * @param  \Box\Brainy\Compiler\TemplateCompiler $compiler
     * @param  string $name
     * @return array
     */
    private static function getUsages($compiler, $name)
    {
        $data = $compiler->lex->data;
        return array(
            'first' => strpos($data, '.first') !== false,
            'last' => strpos($data, '.last') !== false,
            'index' => strpos($data, '.index') !== false,
            'iteration' => strpos($data, '.iteration') !== false,
            'show' => strpos($data, '.show') !== false,
            'total' => strpos($data, '.total') !== false,
        );
    }
}
