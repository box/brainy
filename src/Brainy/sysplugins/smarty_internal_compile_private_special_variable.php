<?php
/**
 * Smarty Internal Plugin Compile Special Smarty Variable
 *
 * Compiles the special $smarty variables
 *
 * @package Brainy
 * @subpackage Compiler
 * @author Uwe Tews
 */

/**
 * Smarty Internal Plugin Compile special Smarty Variable Class
 *
 * @package Brainy
 * @subpackage Compiler
 */
class Smarty_Internal_Compile_Private_Special_Variable extends Smarty_Internal_CompileBase
{
    /**
     * Compiles code for the speical $smarty variables
     *
     * @param  array  $args     array with attributes from parser
     * @param  object $compiler compiler object
     * @param  string $parameter The parameter being accessed on the smarty variable
     * @param  string|null $modifier The member of the parameter to fetch.
     * @return string compiled code
     */
    public function compile($args, $compiler, $parameter, $modifier = null) {
        $compiled_ref = ' ';
        $variable = substr($parameter, 1, strlen($parameter)-2);
        switch ($variable) {
            case 'foreach':
                return "\$_smarty_tpl->tpl_vars['smarty']->value['foreach'][$modifier]";
            case 'section':
                $compiler->assert_is_not_strict('{$smarty.section} is not allowed in strict mode');
                return "\$_smarty_tpl->tpl_vars['smarty']->value['section'][$modifier]";
            case 'capture':
                return "Smarty::\$_smarty_vars['capture'][$modifier]";
            case 'now':
                return 'time()';
            case 'cookies':
                $compiler->assert_is_not_strict('{$smarty.cookies} is not allowed in strict mode');
                if (isset($compiler->smarty->security_policy) && !$compiler->smarty->security_policy->allow_super_globals) {
                    $compiler->trigger_template_error("(secure mode) super globals not permitted");
                    break;
                }
                $compiled_ref = '$_COOKIE';
                break;

            case 'get':
            case 'post':
            case 'env':
            case 'server':
            case 'session':
            case 'request':
                $compiler->assert_is_not_strict(
                    '{$smarty.' . $variable . '} is not allowed in strict ' .
                    'mode because it can expose sensitive data or increase ' .
                    'the risk of XSS.'
                );
                if (isset($compiler->smarty->security_policy) && !$compiler->smarty->security_policy->allow_super_globals) {
                    $compiler->trigger_template_error("(secure mode) super globals not permitted");
                    return;
                }
                $unsafe = '$_' . strtoupper($variable) . "[$modifier]";
                if ($compiler->smarty->safe_lookups === Smarty::LOOKUP_UNSAFE) {
                    return $unsafe;
                } else {
                    return new BrainySafeLookupWrapper(
                        $unsafe,
                        'smarty_safe_array_lookup($_' . strtoupper($variable) . ', ' . $modifier . ', ' . $compiler->smarty->safe_lookups . ')'
                    );
                }

            case 'template':
                return 'basename($_smarty_tpl->source->filepath)';

            case 'template_object':
                $compiler->assert_is_not_strict('{$smarty.template_object} is not allowed in strict mode');
                $compiled_ref = '$_smarty_tpl';
                break;

            case 'current_dir':
                return 'dirname($_smarty_tpl->source->filepath)';

            case 'version':
                $_version = Smarty::SMARTY_VERSION;
                return "'$_version'";

            case 'const':
                $compiler->assert_is_not_strict('{$smarty.const} is not allowed in strict mode');
                if (isset($compiler->smarty->security_policy) && !$compiler->smarty->security_policy->allow_constants) {
                    $compiler->trigger_template_error("(secure mode) constants not permitted");
                    return;
                }

                return "(defined($modifier) ? constant($modifier) : $modifier)";

            case 'ldelim':
                $_ldelim = $compiler->smarty->left_delimiter;
                return "'$_ldelim'";

            case 'rdelim':
                $_rdelim = $compiler->smarty->right_delimiter;
                return "'$_rdelim'";

            default:
                $compiler->trigger_template_error('$smarty.' . trim($variable, "'") . ' is invalid');
                return;
        }
        if ($modifier !== null) {
            $compiled_ref .= "[$modifier]";
        }

        return $compiled_ref;
    }

}
