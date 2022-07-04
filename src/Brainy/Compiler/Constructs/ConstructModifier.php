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
        } else {
            // At this point, we know that the modifier being constructed is either a runtime modifier
            // (i.e., not a compiler modifier), or it doesn't exist (this will error, so we treat it
            // as a runtime modifier for simplicity).

            $params = implode(', ', $parameters);

            // If a runtime modifier is used in safe mode, we need to check that none of the params
            // are safe lookups. If they are, we need to move those to temporary variables inside a
            // self-executing closure. `array_pop(foo($bar))` will fail; we need
            // `$tmp=foo($bar);array_pop($tmp)` instead.

            $should_wrap_for_safe_mode = false;
            if ($compiler->smarty->safe_lookups !== \Box\Brainy\Brainy::LOOKUP_UNSAFE) {
                foreach ($parameters as $param) {
                    $should_wrap_for_safe_mode = $should_wrap_for_safe_mode || $param instanceof \Box\Brainy\Compiler\Wrappers\SafeLookupWrapper;
                }
            }

            // Wrapping as a self-executing closure works by replacing this (hypothetical) generated
            // code:
            //
            //   echo array_pop(\Box\Brainy\Runtime\Lookups::safeArrayLookup($_smarty_tpl->tpl_vars, 'foo'));
            //
            // with this code:
            //
            //   echo (function($p1) use ($_smarty_tpl) {return array_pop($p1);})(\Box\Brainy\Runtime\Lookups::safeArrayLookup($_smarty_tpl->tpl_vars, 'foo'));
            //
            // By wrapping in a self-executing closure, we have a few nice benefits:
            //   1. We don't need to worry about emitting generated code from the compiler, which can lead to
            //      out-of-order execution, resulting in incorrect code (e.g., function parameters evaluating
            //      in the wrong order).
            //   2. The output of this compiler directive remains an expression. Self-executing closures are
            //      expressions.
            //   3. `$params` contains the would-be params as they are to be evaluated. These simply become
            //      arguments for the self-executing closure.
            //   4. Since function parameters are "variables" in the eyes of PHP, a replacement set of
            //      arguments can be created (see the `implode` call below) that is both the self-executing
            //      closure's parameter list _and_ the argument list for the modifier. This is cheaply
            //      constructed, since the parameter count is definitively known at compile-time.
            if ($should_wrap_for_safe_mode) {
                $orig_params = $params;

                // Replace $params with `$p1, $p2, ... $pN`
                $params = implode(
                    ', ',
                    array_map(
                        function ($i) {
                            return "\$p$i";
                        },
                        array_keys($parameters)
                    )
                );

                $output = '(function(' . $params . ') { return ';
            }

            $output .= self::compileSingleRuntimeModifier($compiler, $modifier, $params);

            if ($should_wrap_for_safe_mode) {
                $output .= ';})(' . $orig_params . ')';
            }
        }

        if ($modifierIsStatic || in_array($modifier, Brainy::$enforce_expression_modifiers ?: array())) {
            $output = new StaticWrapper($output);
        }
        return $output;
    }

    /**
     * @param  \Box\Brainy\Compiler\TemplateCompiler $compiler A compiler reference
     * @param @modifier string
     * @param $params string
     * @return string|StaticWrapper
     */
    public static function compileSingleRuntimeModifier(\Box\Brainy\Compiler\TemplateCompiler $compiler, $modifier, $params)
    {
        if (isset($compiler->smarty->registered_plugins[Brainy::PLUGIN_MODIFIER][$modifier])) {
            $function = $compiler->smarty->registered_plugins[Brainy::PLUGIN_MODIFIER][$modifier];
            return "{$function}({$params})";
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
            return $output;
        } elseif (is_callable($modifier)) {

            if (
                is_object($compiler->smarty->security_policy)
                && !$compiler->smarty->security_policy->isTrustedPhpModifier($modifier, $compiler)
            ) {
                $compiler->trigger_template_error('Could not use modifier "' . $modifier . '" in template due to security policy');
                // unreachable;
            }

            return "{$modifier}({$params})";
        } else {
            $compiler->trigger_template_error('Unknown modifier: "' . $modifier . '"');
            // unreachable
        }
    }
}
