<?php

namespace Box\Brainy\Compiler\Constructs;

use \Box\Brainy\Brainy;
use \Box\Brainy\SmartyBC;

class ConstructAssign extends BaseConstruct
{
    /**
     * Compiles the opening tag for a function
     * @param  \Box\Brainy\Compiler\TemplateCompiler $compiler A compiler reference
     * @param  array|null                            $args     Arguments
     * @return mixed
     */
    public static function compileOpen(\Box\Brainy\Compiler\TemplateCompiler $compiler, $args)
    {
        $var = self::getRequiredArg($args, 'var');
        $value = self::getRequiredArg($args, 'value');

        $scope = self::getScope($args);

        // implement Smarty2's behaviour of variables assigned by reference
        if ($compiler->template->smarty instanceof SmartyBC && Brainy::$assignment_compat === Brainy::ASSIGN_COMPAT) {
            $output = "if (isset(\$_smarty_tpl->tpl_vars[$var])) {\n";
            $output .= "  \$_smarty_tpl->tpl_vars[$var] = clone \$_smarty_tpl->tpl_vars[$var];\n";
            $output .= "  \$_smarty_tpl->tpl_vars[$var]->value = $value;\n";
            $output .= "  \$_smarty_tpl->tpl_vars[$var]->scope = $scope;\n";
            $output .= "} else {\$_smarty_tpl->setVariable($var, $value, $scope);}\n";
        } else {
            $output = "\$_smarty_tpl->setVariable($var, $value, $scope);\n";
        }
        return $output;
    }

    /**
     * Returns the scope, given the raw scope string
     * @param  string        $raw
     * @param  int|null|void $default The default scope, or null
     * @return int
     */
    public static function getScope($args, $default = null)
    {
        $scopeRaw = self::getOptionalArg($args, 'scope', self::getDefaultScope($default));
        switch (\Box\Brainy\Compiler\Decompile::decompileString($scopeRaw)) {
            case 'local':
                return Brainy::SCOPE_LOCAL;
            case 'parent':
                return Brainy::SCOPE_PARENT;
            case 'root':
                return Brainy::SCOPE_ROOT;
            case 'global':
                return Brainy::SCOPE_GLOBAL;
            default:
                $compiler->trigger_template_error('illegal value for "scope" attribute: ' . $scopeRaw, $compiler->lex->taglineno);
        }
    }

    /**
     * @param int|null|void $default
     * @return string
     */
    private static function getDefaultScope($default)
    {
        switch ($default ?: Brainy::$default_assign_scope) {
            case Brainy::SCOPE_LOCAL:
                return '"local"';
            case Brainy::SCOPE_PARENT:
                return '"parent"';
            case Brainy::SCOPE_ROOT:
                return '"root"';
            case Brainy::SCOPE_GLOBAL:
                return '"global"';
        }
    }
}
