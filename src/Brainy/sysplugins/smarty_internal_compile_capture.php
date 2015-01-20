<?php
/**
 * Smarty Internal Plugin Compile Capture
 *
 * Compiles the {capture} tag
 *
 * @package Brainy
 * @subpackage Compiler
 * @author Uwe Tews
 * @author Matt Basta
 */

/**
 * Smarty Internal Plugin Compile Capture Class
 *
 * @package Brainy
 * @subpackage Compiler
 */
class Smarty_Internal_Compile_Capture extends Smarty_Internal_CompileBase
{
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
    public $optional_attributes = array('name', 'assign', 'append');

    /**
     * Compiles code for the {capture} tag
     *
     * @param  array  $args     array with attributes from parser
     * @param  object $compiler compiler object
     * @return string compiled code
     */
    public function compile($args, $compiler) {
        // check and get attributes
        $_attr = $this->getAttributes($compiler, $args);

        $name = isset($_attr['name']) ? $_attr['name'] : "'default'";
        $assign = isset($_attr['assign']) ? $_attr['assign'] : 'null';
        $append = isset($_attr['append']) ? $_attr['append'] : 'null';

        $compiler->_capture_stack[0][] = array($name, $assign, $append);
        return "ob_start();\n";
    }

}

/**
 * Smarty Internal Plugin Compile Captureclose Class
 *
 * @package Brainy
 * @subpackage Compiler
 */
class Smarty_Internal_Compile_CaptureClose extends Smarty_Internal_CompileBase
{
    /**
     * Compiles code for the {/capture} tag
     *
     * @param  array  $args     array with attributes from parser
     * @param  object $compiler compiler object
     * @return string compiled code
     */
    public function compile($args, $compiler) {
        // check and get attributes
        $_attr = $this->getAttributes($compiler, $args);

        list($name, $assign, $append) = array_pop($compiler->_capture_stack[0]);

        $output = '';

        if (isset($assign)) {
            $output .= '$_smarty_tpl->assign(' . $assign . ', ob_get_contents());';
        }
        if (isset($append)) {
            $output .= '$_smarty_tpl->append(' . $append . ', ob_get_contents());';
        }
        $output .= 'Smarty::$_smarty_vars[\'capture\'][' . $name . '] = ob_get_clean();';

        return $output;
    }

}
