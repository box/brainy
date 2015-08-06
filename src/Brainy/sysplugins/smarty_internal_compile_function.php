<?php
/**
 * Smarty Internal Plugin Compile Function
 *
 * Compiles the {function} {/function} tags
 *
 * @package Brainy
 * @subpackage Compiler
 * @author Uwe Tews
 */

/**
 * Smarty Internal Plugin Compile Function Class
 *
 * @package Brainy
 * @subpackage Compiler
 */
class Smarty_Internal_Compile_Function extends Smarty_Internal_CompileBase
{
    /**
     * Attribute definition: Overwrites base class.
     *
     * @var array
     * @see Smarty_Internal_CompileBase
     */
    public $required_attributes = array('name');
    /**
     * Attribute definition: Overwrites base class.
     *
     * @var array
     * @see Smarty_Internal_CompileBase
     */
    public $shorttag_order = array('name');
    /**
     * Attribute definition: Overwrites base class.
     *
     * @var array
     * @see Smarty_Internal_CompileBase
     */
    public $optional_attributes = array('_any');

    /**
     * Compiles code for the {function} tag
     *
     * @param  array   $args      array with attributes from parser
     * @param  object  $compiler  compiler object
     * @param  array   $parameter array with compilation parameter
     * @return boolean true
     */
    public function compile($args, $compiler, $parameter) {
        // check and get attributes
        $_attr = $this->getAttributes($compiler, $args);

        $save = array($_attr, $compiler->parser->current_buffer, $compiler->template->required_plugins);
        $this->openTag($compiler, 'function', $save);
        $_name = trim($_attr['name'], "'\"");
        unset($_attr['name']);
        // set flag that we are compiling a template function
        $compiler->compiles_template_function = true;
        $compiler->template->properties['function'][$_name]['parameter'] = array();
        $_smarty_tpl = $compiler->template;
        foreach ($_attr as $_key => $_data) {
            eval ('$tmp='.$_data.';');
            $compiler->template->properties['function'][$_name]['parameter'][$_key] = $_data;
        }
        $compiler->smarty->template_functions[$_name]['parameter'] = $compiler->template->properties['function'][$_name]['parameter'];

        // TODO: Is this $saved_tpl_vars business actually doing anything?
        $output = "if (!function_exists('smarty_template_function_{$_name}')) {
function smarty_template_function_{$_name}(\$_smarty_tpl, \$params) {
\$saved_tpl_vars = \$_smarty_tpl->tpl_vars;\n";

        foreach ($_attr as $_key => $_data) {
            $enc_key = var_export($_key, true);
            $output .= "    if (isset(\$params[" . $enc_key . "])) {\n";
            $output .= "        \$_smarty_tpl->tpl_vars[" . $enc_key . "] = new Smarty_variable(\$params[" . $enc_key . "]);\n";
            $output .= "        unset(\$params[" . $enc_key . "]);\n";
            $output .= "    } else {\n";
            $output .= "        \$_smarty_tpl->tpl_vars[" . $enc_key . "] = new Smarty_variable(" . $_data . ");\n";
            $output .= "    }\n";
        }

        $output .= "    foreach (\$params as \$key => \$value) {\$_smarty_tpl->tpl_vars[\$key] = new Smarty_variable(\$value);}\n";

        // Init temporay context
        $compiler->template->required_plugins = array('compiled' => array());
        $compiler->parser->current_buffer = new _smarty_template_buffer($compiler->parser);
        $compiler->parser->current_buffer->append_subtree(new _smarty_tag($compiler->parser, $output));
        $compiler->has_code = false;
        $compiler->template->properties['function'][$_name]['compiled'] = '';
        return true;
    }

}

/**
 * Smarty Internal Plugin Compile Functionclose Class
 *
 * @package Brainy
 * @subpackage Compiler
 */
class Smarty_Internal_Compile_Functionclose extends Smarty_Internal_CompileBase
{
    /**
     * Compiles code for the {/function} tag
     *
     * @param  array   $args      array with attributes from parser
     * @param  object  $compiler  compiler object
     * @param  array   $parameter array with compilation parameter
     * @return boolean true
     */
    public function compile($args, $compiler, $parameter) {
        $_attr = $this->getAttributes($compiler, $args);
        $saved_data = $this->closeTag($compiler, array('function'));
        $_name = trim($saved_data[0]['name'], "'\"");
        // build plugin include code
        $plugins_string = '';
        if (!empty($compiler->template->required_plugins['compiled'])) {
            foreach ($compiler->template->required_plugins['compiled'] as $tmp) {
                foreach ($tmp as $data) {
                    $plugins_string .= "if (!is_callable('{$data['function']}')) include '{$data['file']}';\n";
                }
            }
        }
         // remove last line break from function definition
         $last = count($compiler->parser->current_buffer->subtrees) - 1;
         if ($compiler->parser->current_buffer->subtrees[$last] instanceof _smarty_linebreak) {
             unset($compiler->parser->current_buffer->subtrees[$last]);
         }
        $output = $plugins_string . $compiler->parser->current_buffer->to_smarty_php() . "\$_smarty_tpl->tpl_vars = \$saved_tpl_vars;
foreach (Brainy::\$global_tpl_vars as \$key => \$value) if(!isset(\$_smarty_tpl->tpl_vars[\$key])) \$_smarty_tpl->tpl_vars[\$key] = \$value;}}\n";
        // reset flag that we are compiling a template function
        $compiler->compiles_template_function = false;
        // restore old compiler status
        $compiler->parser->current_buffer = $saved_data[1];
        $compiler->template->required_plugins = $saved_data[2];

        return $output;
    }

}
