<?php

namespace Box\Brainy\Compiler\Constructs;

use \Box\Brainy\Brainy;


class ConstructCall extends BaseConstruct
{
    /**
     * @param  \Box\Brainy\Compiler\TemplateCompiler $compiler A compiler reference
     * @param  array|null  $args     Arguments
     * @return mixed
     */
    public static function compileOpen(\Box\Brainy\Compiler\TemplateCompiler $compiler, $args)
    {

        $name = self::getRequiredArg($args, 'name');

        $paramArray = self::flattenCompiledArray($args);
        $paramArray = self::exportArray($paramArray);

        $tmpVar = '$' . $compiler->getUniqueVarName();
        // Evaluate the function name dynamically at runtime
        $output = "$tmpVar = $name;\n";
        // Safety Dance
        $output .= "if (!array_key_exists('functions', \$_smarty_tpl->tpl_vars['smarty']->value)) {\n";
        $output .= "  throw new \\Box\\Brainy\\Exceptions\\SmartyException('Call to undefined function \\'' . $tmpVar . '\\'. No defined functions.');\n";
        $output .= "}\n";
        $output .= "if (!array_key_exists($tmpVar, \$_smarty_tpl->tpl_vars['smarty']->value['functions'])) {\n";
        $output .= "  throw new \\Box\\Brainy\\Exceptions\\SmartyException('Call to undefined function \\'' . $tmpVar . '\\'. Defined functions: ' . implode(', ', array_keys(\$_smarty_tpl->tpl_vars['smarty']->value['functions'])));\n";
        $output .= "}\n";

        $output .= "\$_smarty_tpl->tpl_vars['smarty']->value['functions'][$tmpVar]($paramArray);\n";

        return $output;
    }

    /**
     * @param  \Box\Brainy\Compiler\TemplateCompiler $compiler A compiler reference
     * @param  array|null  $args     Arguments
     * @return mixed
     */
    public static function compileClose(\Box\Brainy\Compiler\TemplateCompiler $compiler, $args)
    {
    }
}
