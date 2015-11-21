<?php

namespace Box\Brainy\Compiler\Constructs;

use \Box\Brainy\Brainy;


class ConstructFunction extends ClosedBaseConstruct
{
    /**
     * @param  \Box\Brainy\Compiler\TemplateCompiler $compiler A compiler reference
     * @param  array|null  $args     Arguments
     * @return mixed
     */
    public static function compileOpen(\Box\Brainy\Compiler\TemplateCompiler $compiler, $args)
    {

        self::openTag($compiler, 'function');
        $name = self::getRequiredArg($args, 'name');

        $paramConstructionArray = array();
        foreach (self::flattenCompiledArray($args) as $argName => $default) {
            if ($argName === 'name') {
                continue;
            }
            $safeArgName = var_export($argName, true);
            $paramConstructionArray[] = "  if (!isset(\$tmp->tpl_vars[$safeArgName])) \$tmp->setVariable($safeArgName, $default);\n";
        }

        $output = "if (!array_key_exists('functions', \$_smarty_tpl->tpl_vars['smarty']->value)) \$_smarty_tpl->tpl_vars['smarty']->value['functions'] = array();\n";
        $output .= "\$_smarty_tpl->tpl_vars['smarty']->value['functions'][" . $name . "] = function (\$params) use (\$_smarty_tpl) {\n";
        $output .= "  \$tmp = new \\Box\\Brainy\\Templates\\TemplateBase(\$_smarty_tpl->smarty);\n";
        $output .= "  \$tmp->parent = &\$_smarty_tpl;\n";
        $output .= "  \$tmp->cloneDataFrom(\$_smarty_tpl);\n";
        $output .= "  \$tmp->applyDataFrom(\$params);\n";
        $output .= "  \$tmp->tpl_vars['smarty'] = &\$_smarty_tpl->tpl_vars['smarty'];\n";
        $output .= implode('', $paramConstructionArray);
        $output .= "  \$_smarty_tpl = \$tmp;\n";

        return $output;
    }

    /**
     * @param  \Box\Brainy\Compiler\TemplateCompiler $compiler A compiler reference
     * @param  array|null  $args     Arguments
     * @return mixed
     */
    public static function compileClose(\Box\Brainy\Compiler\TemplateCompiler $compiler, $args)
    {
        self::closeTag($compiler, array('function'));
        return "};\n";
    }
}
