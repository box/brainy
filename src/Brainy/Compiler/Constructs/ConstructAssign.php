<?php

namespace Box\Brainy\Compiler\Constructs;

use \Box\Brainy\Brainy;
use \Box\Brainy\SmartyBC;


class ConstructAssign extends BaseConstruct
{
    /**
     * Compiles the opening tag for a function
     * @param  \Box\Brainy\Compiler\TemplateCompiler $compiler A compiler reference
     * @param  array|null  $args     Arguments
     * @param  array|null  $params   Parameters
     * @return mixed
     */
    public static function compileOpen(\Box\Brainy\Compiler\TemplateCompiler $compiler, $args, $params)
    {
        $var = self::getRequiredArg($args, 'var');
        $value = self::getRequiredArg($args, 'value');
        $scopeRaw = self::getOptionalArg($args, 'scope', Brainy::$default_assign_scope);

        if ($scopeRaw == 'local') {
            $scope = Brainy::SCOPE_LOCAL;
        } elseif ($scopeRaw == 'parent') {
            $scope = Brainy::SCOPE_PARENT;
        } elseif ($scopeRaw == 'root') {
            $scope = Brainy::SCOPE_ROOT;
        } elseif ($scopeRaw == 'global') {
            $scope = Brainy::SCOPE_GLOBAL;
        } else {
            $compiler->trigger_template_error('illegal value for "scope" attribute', $compiler->lex->taglineno);
        }

        // implement Smarty2's behaviour of variables assigned by reference
        if ($compiler->template->smarty instanceof SmartyBC && Brainy::$assignment_compat === Brainy::ASSIGN_COMPAT) {
            $output = "if (isset(\$_smarty_tpl->tpl_vars[$var])) {\n";
            $output .= "  \$_smarty_tpl->tpl_vars[$var] = clone \$_smarty_tpl->tpl_vars[$var];\n";
            $output .= "  \$_smarty_tpl->tpl_vars[$var]->value = $value;\n";
            $output .= "  \$_smarty_tpl->tpl_vars[$var]->scope = $scope;\n";
            $output .= "} else {\$_smarty_tpl->assign($var, $value, $scope);}\n";
        } else {
            $output = "\$_smarty_tpl->assign($var, $value, $scope);\n";
        }
        return $output;
    }
}
