<?php

namespace Box\Brainy\Compiler\Constructs;

use \Box\Brainy\Brainy;
use \Box\Brainy\Compiler\Wrappers\StaticWrapper;
use \Box\Brainy\Runtime\PluginLoader;

class ConstructModifier extends BaseConstruct
{
    /**
     * @param  \Box\Brainy\Compiler\TemplateCompiler $compiler A compiler reference
     * @param  array|null                            $args     Arguments
     * @return mixed
     */
    public static function compileOpen(\Box\Brainy\Compiler\TemplateCompiler $compiler, $args)
    {
        $output = self::getRequiredArg($args, 'value');
        $modifierlist = self::getRequiredArg($args, 'modifierlist');

        foreach ($modifierlist as $rawModifier) {
            $modifier = $rawModifier[0];
            $rawModifier[0] = $output;
            $output = self::compileSingleModifier($compiler, $modifier, $rawModifier);
        }

        return $output;
    }

    /**
     * @param  \Box\Brainy\Compiler\TemplateCompiler $compiler A compiler reference
     * @param @modifier string
     * @param $parameters array
     * @return string|StaticWrapper
     */
    public static function compileSingleModifier(\Box\Brainy\Compiler\TemplateCompiler $compiler, $modifier, $parameters)
    {
        // The output of the modifier is static if the input is static and all of the params are
        // static. We convert the parameters ($rawModifier[n>0]) to strings because we separately
        // wrap the modifier output in a StaticWrapper if we know the modifier is static; we just
        // need the generated code for the purposes of calling the modifier function.
        $modifierIsStatic = $parameters[0] instanceof StaticWrapper;
        for ($i = 0; $i < count($parameters); $i++) {
            if ($parameters[$i] instanceof StaticWrapper) {
                $parameters[$i] = (string) $parameters[$i];
            } elseif ($i > 0) {
                $modifierIsStatic = false;
            }
        }

        $params = implode(', ', $parameters);
        $output = '';

        if (isset($compiler->smarty->registered_plugins[Brainy::PLUGIN_MODIFIERCOMPILER][$modifier])) {
            // This gets a copy of `$output` because $rawModifier[0] is set to $output above.
            $output = call_user_func(
                $compiler->smarty->registered_plugins[Brainy::PLUGIN_MODIFIERCOMPILER][$modifier],
                $parameters,
                $compiler->smarty
            );
        } elseif (PluginLoader::loadPlugin(Brainy::PLUGIN_MODIFIERCOMPILER, $modifier, $compiler->smarty)) {

            if (
                is_object($compiler->smarty->security_policy)
                && !$compiler->smarty->security_policy->isTrustedModifier($modifier, $compiler)
            ) {
                $compiler->trigger_template_error('Could not use modifier "' . $modifier . '" in template due to security policy');
                // unreachable;
            }

            $func = PluginLoader::getPluginFunction(Brainy::PLUGIN_MODIFIERCOMPILER, $modifier);
            $output = call_user_func($func, $parameters, $compiler);
        } elseif (isset($compiler->smarty->registered_plugins[Brainy::PLUGIN_MODIFIER][$modifier])) {
            $function = $compiler->smarty->registered_plugins[Brainy::PLUGIN_MODIFIER][$modifier];
            $output = "{$function}({$params})";
        } elseif (PluginLoader::loadPlugin(Brainy::PLUGIN_MODIFIER, $modifier, $compiler->smarty)) {

            if (
                is_object($compiler->smarty->security_policy)
                && !$compiler->smarty->security_policy->isTrustedModifier($modifier, $compiler)
            ) {
                $compiler->trigger_template_error('Could not use modifier "' . $modifier . '" in template due to security policy');
                // unreachable;
            }

            $output = '(\Box\Brainy\Runtime\PluginLoader::loadPlugin(\Box\Brainy\Brainy::PLUGIN_MODIFIER, ' . var_export($modifier, true) . ', $_smarty_tpl->smarty) ?';

            $func = PluginLoader::getPluginFunction(Brainy::PLUGIN_MODIFIER, $modifier);
            $output .= "{$func}({$params})";

            $output .= ' : null)';
        } elseif (is_callable($modifier)) {

            if (
                is_object($compiler->smarty->security_policy)
                && !$compiler->smarty->security_policy->isTrustedPhpModifier($modifier, $compiler)
            ) {
                $compiler->trigger_template_error('Could not use modifier "' . $modifier . '" in template due to security policy');
                // unreachable;
            }

            $output = "{$modifier}({$params})";
        } else {
            $compiler->trigger_template_error('Unknown modifier: "' . $modifier . '"');
            // unreachable
        }

        if ($modifierIsStatic || in_array($modifier, Brainy::$enforce_expression_modifiers ?: array())) {
            $output = new StaticWrapper($output);
        }
        return $output;
    }
}
