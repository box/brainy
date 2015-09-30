<?php
/**
 * Smarty Internal Plugin Compile Registered Function
 *
 * Compiles code for the execution of a registered function
 *
 * @package Brainy
 * @subpackage Compiler
 * @author Uwe Tews
 */

/**
 * Smarty Internal Plugin Compile Registered Function Class
 *
 * @package Brainy
 * @subpackage Compiler
 */
class Smarty_Internal_Compile_Private_Registered_Function extends Smarty_Internal_CompileBase
{
    /**
     * Attribute definition: Overwrites base class.
     *
     * @var array
     * @see Smarty_Internal_CompileBase
     */
    public $optional_attributes = array('_any');

    /**
     * Compiles code for the execution of a registered function
     *
     * @param  array  $args      array with attributes from parser
     * @param  object $compiler  compiler object
     * @param  array  $parameter array with compilation parameter
     * @param  string $tag       name of function
     * @return string compiled code
     */
    public function compile($args, $compiler, $parameter, $tag) {
        // This tag does create output
        $compiler->has_output = true;
        // check and get attributes
        $_attr = $this->getAttributes($compiler, $args);
        $_params = var_export($_attr, true);
        $function = $compiler->smarty->registered_plugins[Brainy::PLUGIN_FUNCTION][$tag];
        // compile code
        return "echo {$function}({$_params},\$_smarty_tpl);\n";
    }

}
