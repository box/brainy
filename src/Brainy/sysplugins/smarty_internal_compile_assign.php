<?php
/**
 * Smarty Internal Plugin Compile Assign
 *
 * Compiles the {assign} tag
 *
 * @package Brainy
 * @subpackage Compiler
 * @author Uwe Tews
 */

/**
 * Smarty Internal Plugin Compile Assign Class
 *
 * @package Brainy
 * @subpackage Compiler
 */
class Smarty_Internal_Compile_Assign extends Smarty_Internal_CompileBase
{
    /**
     * Compiles code for the {assign} tag
     *
     * @param  array  $args      array with attributes from parser
     * @param  object $compiler  compiler object
     * @param  array  $parameter array with compilation parameter
     * @return string compiled code
     */
    public function compile($args, $compiler, $parameter) {
        // the following must be assigned at runtime because it will be overwritten in Smarty_Internal_Compile_Append
        $this->required_attributes = array('var', 'value');
        $this->shorttag_order = array('var', 'value');
        $this->optional_attributes = array('scope');

        $_scope = Brainy::$default_assign_scope;

        // check and get attributes
        $_attr = $this->getAttributes($compiler, $args);
        // scope setup
        if (isset($_attr['scope'])) {
            $_attr['scope'] = trim($_attr['scope'], "'\"");
            if ($_attr['scope'] == 'local') {
                $_scope = Brainy::SCOPE_LOCAL;
            } elseif ($_attr['scope'] == 'parent') {
                $_scope = Brainy::SCOPE_PARENT;
            } elseif ($_attr['scope'] == 'root') {
                $_scope = Brainy::SCOPE_ROOT;
            } elseif ($_attr['scope'] == 'global') {
                $_scope = Brainy::SCOPE_GLOBAL;
            } else {
                $compiler->trigger_template_error('illegal value for "scope" attribute', $compiler->lex->taglineno);
            }
        }
        // compiled output
        $output = '';
        if (isset($parameter['smarty_internal_index'])) {
            $output .= "\$_smarty_tpl->createLocalArrayVariable($_attr[var], null, $_scope);\n";
            $output .= "\$_smarty_tpl->tpl_vars[$_attr[var]]->value$parameter[smarty_internal_index] = $_attr[value];\n";
        } else {
            // implement Smarty2's behaviour of variables assigned by reference
            if ($compiler->template->smarty instanceof SmartyBC && Brainy::$assignment_compat === Brainy::ASSIGN_COMPAT) {
                $output .= "if (isset(\$_smarty_tpl->tpl_vars[$_attr[var]])) {\n";
                $output .= "  \$_smarty_tpl->tpl_vars[$_attr[var]] = clone \$_smarty_tpl->tpl_vars[$_attr[var]];\n";
                $output .= "  \$_smarty_tpl->tpl_vars[$_attr[var]]->value = $_attr[value];\n";
                $output .= "  \$_smarty_tpl->tpl_vars[$_attr[var]]->scope = $_scope;\n";
                $output .= "} else \$_smarty_tpl->assign($_attr[var], $_attr[value], $_scope);\n";
            } else {
                $output .= "\$_smarty_tpl->assign($_attr[var], $_attr[value], $_scope);\n";
            }
        }

        return $output;
    }

}
