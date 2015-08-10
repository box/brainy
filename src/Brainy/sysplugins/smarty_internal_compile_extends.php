<?php

/**
 * Smarty Internal Plugin Compile extend
 *
 * Compiles the {extends} tag
 *
 * @package Brainy
 * @subpackage Compiler
 * @author Uwe Tews
 */

/**
 * Smarty Internal Plugin Compile extend Class
 *
 * @package Brainy
 * @subpackage Compiler
 */
class Smarty_Internal_Compile_Extends extends Smarty_Internal_CompileBase
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
     * Compiles code for the {extends} tag
     *
     * @param array $args     array with attributes from parser
     * @param object $compiler compiler object
     * @return string compiled code
     */
    public function compile($args, $compiler) {
        // check and get attributes
        $_attr = $this->getAttributes($compiler, $args);
        if (strpos($_attr['file'], '$_tmp') !== false) {
            $compiler->trigger_template_error('illegal value for file attribute', $compiler->lex->taglineno);
        }

        $_smarty_tpl = $compiler->template;

        $name = $_attr['file'];
        if ($name[0] === "'" && $name[strlen($name) - 1] === "'") {
            $name = '"' . substr($name, 1, -1) . '"';
        }
        $tpl_name = json_decode($name);
        // create template object
        $_template = Template($tpl_name, $compiler->smarty, $compiler->template);
        // check for recursion
        $uid = $_template->source->uid;
        if (isset($compiler->extends_uid[$uid])) {
            $compiler->trigger_template_error("illegal recursive call of \"$name\"", $this->lex->line - 1);
        }
        $compiler->extends_uid[$uid] = true;
        if (empty($_template->source->components)) {
            array_unshift($compiler->sources, $_template->source);
        } else {
            foreach ($_template->source->components as $source) {
                array_unshift($compiler->sources, $source);
                $uid = $source->uid;
                if (isset($compiler->extends_uid[$uid])) {
                    $compiler->trigger_template_error("illegal recursive call of \"{$source->filepath}\"", $this->lex->line - 1);
                }
                $compiler->extends_uid[$uid] = true;
            }
        }
        unset ($_template);
        $compiler->inheritance_child = true;
        $compiler->lex->yypushstate(\Box\Brainy\Compiler\Lexer::CHILDBODY);
        return '';
    }
}
