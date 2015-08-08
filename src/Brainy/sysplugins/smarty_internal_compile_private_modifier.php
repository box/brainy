<?php

/**
 * Smarty Internal Plugin Compile Modifier
 *
 * Compiles code for modifier execution
 *
 * @package Brainy
 * @subpackage Compiler
 * @author Uwe Tews
 */

/**
 * Smarty Internal Plugin Compile Modifier Class
 *
 * @package Brainy
 * @subpackage Compiler
 */
class Smarty_Internal_Compile_Private_Modifier extends Smarty_Internal_CompileBase
{
    /**
     * Compiles code for modifier execution
     *
     * @param  array  $args      array with attributes from parser
     * @param  object $compiler  compiler object
     * @param  array  $parameter array with compilation parameter
     * @return string compiled code
     */
    public function compile($args, $compiler, $parameter) {
        // check and get attributes
        $_attr = $this->getAttributes($compiler, $args);
        $output = $parameter['value'];
        // loop over list of modifiers
        foreach ($parameter['modifierlist'] as $single_modifier) {
            $modifier = $single_modifier[0];
            $single_modifier[0] = $output;
            for ($i = 0; $i < count($single_modifier); $i++) {
                if ($single_modifier[$i] instanceof StaticWrapper) {
                    $single_modifier[$i] = (string) $single_modifier[$i];
                }
            }
            $params = implode(',', $single_modifier);
            // check if we know already the type of modifier
            if (isset($compiler->known_modifier_type[$modifier])) {
                $modifier_types = array($compiler->known_modifier_type[$modifier]);
            } else {
                $modifier_types = array(1, 2, 3, 4, 5);
            }
            foreach ($modifier_types as $type) {
                switch ($type) {
                    case 1:
                        // registered modifier
                        if (isset($compiler->smarty->registered_plugins[Brainy::PLUGIN_MODIFIER][$modifier])) {
                            $function = $compiler->smarty->registered_plugins[Brainy::PLUGIN_MODIFIER][$modifier][0];
                            $output = "{$function}({$params})";
                            $compiler->known_modifier_type[$modifier] = $type;
                            break 2;
                        }
                        break;
                    case 2:
                        // registered modifier compiler
                        if (isset($compiler->smarty->registered_plugins[Brainy::PLUGIN_MODIFIERCOMPILER][$modifier][0])) {
                            $output = call_user_func($compiler->smarty->registered_plugins[Brainy::PLUGIN_MODIFIERCOMPILER][$modifier][0], $single_modifier, $compiler->smarty);
                            $compiler->known_modifier_type[$modifier] = $type;
                            break 2;
                        }
                        break;
                    case 3:
                        // modifiercompiler plugin
                        if ($compiler->smarty->loadPlugin('smarty_modifiercompiler_' . $modifier, false)) {
                            // check if modifier allowed
                            if (!is_object($compiler->smarty->security_policy) || $compiler->smarty->security_policy->isTrustedModifier($modifier, $compiler)) {
                                $plugin = 'smarty_modifiercompiler_' . $modifier;
                                $output = $plugin($single_modifier, $compiler);
                            }
                            $compiler->known_modifier_type[$modifier] = $type;
                            break 2;
                        }
                        break;
                    case 4:
                        // modifier plugin
                        if ($function = $compiler->getPlugin($modifier, Brainy::PLUGIN_MODIFIER)) {
                            // check if modifier allowed
                            if (!is_object($compiler->smarty->security_policy) || $compiler->smarty->security_policy->isTrustedModifier($modifier, $compiler)) {
                                $output = "{$function}({$params})";
                            }
                            $compiler->known_modifier_type[$modifier] = $type;
                            break 2;
                        }
                        break;
                    case 5:
                        // PHP function
                        if (is_callable($modifier)) {
                            // check if modifier allowed
                            if (!is_object($compiler->smarty->security_policy) || $compiler->smarty->security_policy->isTrustedPhpModifier($modifier, $compiler)) {
                                $output = "{$modifier}({$params})";
                            }
                            $compiler->known_modifier_type[$modifier] = $type;
                            break 2;
                        }
                        break;
                }
            }
            if (!isset($compiler->known_modifier_type[$modifier])) {
                $compiler->trigger_template_error("unknown modifier \"" . $modifier . "\"", $compiler->lex->taglineno);
            }
        }

        return $output;
    }

}
