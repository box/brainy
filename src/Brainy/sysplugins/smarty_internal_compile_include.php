<?php
/**
 * Smarty Internal Plugin Compile Include
 *
 * Compiles the {include} tag
 *
 * @package Brainy
 * @subpackage Compiler
 * @author Uwe Tews
 */

/**
 * Smarty Internal Plugin Compile Include Class
 *
 * @package Brainy
 * @subpackage Compiler
 */
class Smarty_Internal_Compile_Include extends Smarty_Internal_CompileBase
{
    /**
     * Attribute definition: Overwrites base class.
     *
     * @var array
     * @see Smarty_Internal_CompileBase
     */
    public $required_attributes = array('file');
    /**
     * Attribute definition: Overwrites base class.
     *
     * @var array
     * @see Smarty_Internal_CompileBase
     */
    public $shorttag_order = array('file');
    /**
     * Attribute definition: Overwrites base class.
     *
     * @var array
     * @see Smarty_Internal_CompileBase
     */
    public $option_flags = array('inline');
    /**
     * Attribute definition: Overwrites base class.
     *
     * @var array
     * @see Smarty_Internal_CompileBase
     */
    public $optional_attributes = array('_any');

    /**
     * Compiles code for the {include} tag
     *
     * @param  array $args      array with attributes from parser
     * @param  object $compiler  compiler object
     * @param  array $parameter array with compilation parameter
     * @return string compiled code
     */
    public function compile($args, $compiler, $parameter) {
        // check and get attributes
        $_attr = $this->getAttributes($compiler, $args);
        // save posible attributes
        $include_file = $_attr['file'];

        if (isset($_attr['assign'])) {
            // output will be stored in a smarty variable instead of beind displayed
            $_assign = $_attr['assign'];
        }

        $_parent_scope = Brainy::SCOPE_LOCAL;
        if (isset($_attr['scope'])) {
            $_attr['scope'] = trim($_attr['scope'], "'\"");
            if ($_attr['scope'] == 'parent') {
                $_parent_scope = Brainy::SCOPE_PARENT;
            } elseif ($_attr['scope'] == 'root') {
                $_parent_scope = Brainy::SCOPE_ROOT;
            } elseif ($_attr['scope'] == 'global') {
                $_parent_scope = Brainy::SCOPE_GLOBAL;
            }
        }

        // flag if included template code should be merged into caller
        $merge_compiled_includes = ($compiler->smarty->merge_compiled_includes ||
                                    ($compiler->inheritance && $compiler->smarty->inheritance_merge_compiled_includes) ||
                                    $_attr['inline'] === true) &&
                                   !$compiler->template->source->recompiled;


        $_compile_id = isset($_attr['compile_id']) ? $_attr['compile_id'] : '$_smarty_tpl->compile_id';

        $has_compiled_template = false;
        if ($merge_compiled_includes) {
            // variable template name ?
            if ($compiler->has_variable_string ||
                ($include_file[0] !== '"' && $include_file[0] !== "'") ||
                count(token_get_all($include_file)) > 1) {

                if ($compiler->inheritance && $compiler->smarty->inheritance_merge_compiled_includes) {
                    $compiler->trigger_template_error('variable template file names not allow within {block} tags');
                } elseif ($compiler->smarty->merge_compiled_includes) {
                    $compiler->trigger_template_error('variable template file names not allowed when merge_compiled_includes is enabled');
                } elseif ($_attr['inline']) {
                    $compiler->trigger_template_error('variable template file names not allowed on {include} function with `inline`');
                }
            }
            // variable compile_id?
            if (isset($_attr['compile_id'])) {
                if (!((substr_count($_attr['compile_id'], '"') == 2 || substr_count($_attr['compile_id'], "'") == 2))
                    || substr_count($_attr['compile_id'], '(') != 0 || substr_count($_attr['compile_id'], '$_smarty_tpl->') != 0
                ) {
                    $merge_compiled_includes = false;
                    if ($compiler->inheritance && $compiler->smarty->inheritance_merge_compiled_includes) {
                        $compiler->trigger_template_error('variable compile_id not allow within {block} tags');
                    }
                }
            }


            // we must observe different compile_id
            $uid = sha1($_compile_id);
            $_smarty_tpl = $compiler->template;
            if ($include_file[0] === "'") {
                $include_file = '"' . substr($include_file, 1, strlen($include_file) - 2) . '"';
            }
            $tpl_name = json_decode($include_file);
            if (!isset($compiler->smarty->merged_templates_func[$tpl_name][$uid])) {
                $tpl = new Template($tpl_name, $compiler->smarty, $compiler->template, $compiler->template->compile_id);
                // save unique function name
                $compiler->smarty->merged_templates_func[$tpl_name][$uid]['func'] = $tpl->properties['unifunc'] = 'content_' . str_replace(array('.',','), '_', uniqid('', true));
                if ($compiler->inheritance) {
                    $tpl->compiler->inheritance = true;
                }
                // make sure whole chain gets compiled
                if ($tpl->source->exists) {
                    // get compiled code
                    $compiled_code = $tpl->compiler->compileTemplate($tpl);
                    // release compiler object to free memory
                    unset($tpl->compiler);
                    // merge compiled code for {function} tags
                    $compiler->template->properties['function'] = array_merge($compiler->template->properties['function'], $tpl->properties['function']);
                    // merge filedependency
                    $tpl->properties['file_dependency'][$tpl->source->uid] = array($tpl->source->filepath, $tpl->source->timestamp, $tpl->source->type);
                    $compiler->template->properties['file_dependency'] = array_merge($compiler->template->properties['file_dependency'], $tpl->properties['file_dependency']);
                    // remove header code
                    $compiled_code = preg_replace("/(<\?php \/\* Brainy(.+?)\/\*\/%%SmartyHeaderCode%%\*\/\n)/s", '', $compiled_code);
                    $compiler->merged_templates[$tpl->properties['unifunc']] = $compiled_code;
                    $has_compiled_template = true;
                    unset ($tpl);
                }
            } else {
                $has_compiled_template = true;
            }
        }
        // delete {include} standard attributes
        unset($_attr['file'], $_attr['assign'], $_attr['compile_id'], $_attr['scope'], $_attr['inline']);

        // remaining attributes must be assigned as smarty variable
        $_vars = 'array()';
        $_has_vars = !empty($_attr);
        if ($_has_vars) {
            if ($_parent_scope !== Brainy::SCOPE_LOCAL) {
                $compiler->trigger_template_error('variable passing not allowed in parent/global scope', $compiler->lex->taglineno);
            }
            // create variables
            foreach ($_attr as $key => $value) {
                $_pairs[] = "'$key'=>$value";
            }
            $_vars = 'array(' . join(',', $_pairs) . ')';
        }
        if ($has_compiled_template) {
            $_output = "/*  Call merged included template \"" . $tpl_name . "\" */\n";
            $_output .= "\$_tpl_stack[] = \$_smarty_tpl;\n";
            $_output .= " \$_smarty_tpl = \$_smarty_tpl->setupInlineSubTemplate($include_file, $_compile_id, $_vars, $_parent_scope);\n";
            if (isset($_assign)) {
                $_output .= 'ob_start(); ';
            }
            $_output .= $compiler->smarty->merged_templates_func[$tpl_name][$uid]['func'] . "(\$_smarty_tpl);\n";
            $_output .= "\$_smarty_tpl = array_pop(\$_tpl_stack); ";
            if (isset($_assign)) {
                $_output .= " \$_smarty_tpl->tpl_vars[$_assign] = new Smarty_variable(ob_get_clean());";
            }
            $_output .= "\n/*  End of included template \"" . $tpl_name . "\" */";

            return $_output;
        }

        // was there an assign attribute
        if (isset($_assign)) {
            return "\$_smarty_tpl->tpl_vars[$_assign] = new Smarty_variable(\$_smarty_tpl->getSubTemplate($include_file, $_compile_id, $_vars, $_parent_scope));\n";;
        }
        return "echo \$_smarty_tpl->getSubTemplate($include_file, $_compile_id, $_vars, $_parent_scope);\n";
    }
}
