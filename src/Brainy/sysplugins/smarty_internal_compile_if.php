<?php
/**
* Smarty Internal Plugin Compile If
*
* Compiles the {if} {else} {elseif} {/if} tags
*
* @package Brainy
* @subpackage Compiler
* @author Uwe Tews
*/

/**
* Smarty Internal Plugin Compile If Class
*
* @package Brainy
* @subpackage Compiler
*/
class Smarty_Internal_Compile_If extends Smarty_Internal_CompileBase
{
    /**
    * Compiles code for the {if} tag
    *
    * @param array  $args       array with attributes from parser
    * @param object $compiler   compiler object
    * @param array  $parameter  array with compilation parameter
    * @return string compiled code
    */
    public function compile($args, $compiler, $parameter) {
        // check and get attributes
        $_attr = $this->getAttributes($compiler, $args);
        $this->openTag($compiler, 'if', array(1));

        if (!array_key_exists("if condition",$parameter)) {
            $compiler->trigger_template_error("missing if condition", $compiler->lex->taglineno);
        }

        return "if ({$parameter['if condition']}) {\n";
    }

}

/**
* Smarty Internal Plugin Compile Else Class
*
* @package Brainy
* @subpackage Compiler
*/
class Smarty_Internal_Compile_Else extends Smarty_Internal_CompileBase
{
    /**
    * Compiles code for the {else} tag
    *
    * @param array  $args       array with attributes from parser
    * @param object $compiler   compiler object
    * @param array  $parameter  array with compilation parameter
    * @return string compiled code
    */
    public function compile($args, $compiler, $parameter) {
        list($nesting) = $this->closeTag($compiler, array('if', 'elseif'));
        $this->openTag($compiler, 'else', array($nesting));

        return "} else {\n";
    }

}

/**
* Smarty Internal Plugin Compile ElseIf Class
*
* @package Brainy
* @subpackage Compiler
*/
class Smarty_Internal_Compile_Elseif extends Smarty_Internal_CompileBase
{
    /**
    * Compiles code for the {elseif} tag
    *
    * @param array  $args       array with attributes from parser
    * @param object $compiler   compiler object
    * @param array  $parameter  array with compilation parameter
    * @return string compiled code
    */
    public function compile($args, $compiler, $parameter) {
        // check and get attributes
        $_attr = $this->getAttributes($compiler, $args);

        list($nesting) = $this->closeTag($compiler, array('if', 'elseif'));

        if (!array_key_exists("if condition", $parameter)) {
            $compiler->trigger_template_error("missing elseif condition", $compiler->lex->taglineno);
        }

        if (empty($compiler->prefix_code)) {
            $this->openTag($compiler, 'elseif', array($nesting));
            return "} elseif ({$parameter['if condition']}) {\n";
        }

        $tmp = '';
        foreach ($compiler->prefix_code as $code) $tmp .= $code;
        $compiler->prefix_code = array();
        $this->openTag($compiler, 'elseif', array($nesting + 1));
        return "} else {\n{$tmp}\nif ({$parameter['if condition']}) {\n";
    }

}

/**
* Smarty Internal Plugin Compile Ifclose Class
*
* @package Brainy
* @subpackage Compiler
*/
class Smarty_Internal_Compile_Ifclose extends Smarty_Internal_CompileBase
{
    /**
     * Compiles code for the {/if} tag
     *
     * @param array  $args       array with attributes from parser
     * @param object $compiler   compiler object
     * @param array  $parameter  array with compilation parameter
     * @return string compiled code
     */
    public function compile($args, $compiler, $parameter) {
        list($nesting) = $this->closeTag($compiler, array('if', 'else', 'elseif'));
        $tmp = '';
        for ($i = 0; $i < $nesting; $i++) {
            $tmp .= '}';
        }

        return $tmp . "\n";
    }

}
