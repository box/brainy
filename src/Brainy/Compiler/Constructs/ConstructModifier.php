<?php

namespace Box\Brainy\Compiler\Constructs;

use \Box\Brainy\Brainy;


class ConstructModifier extends BaseConstruct
{
    /**
     * @param  \Box\Brainy\Compiler\TemplateCompiler $compiler A compiler reference
     * @param  array|null  $args     Arguments
     * @param  array|null  $params   Parameters
     * @return mixed
     */
    public static function compileOpen(\Box\Brainy\Compiler\TemplateCompiler $compiler, array $args, array $params)
    {
        $output = self::getRequiredArg($args, 'value');
        $modifierlist = self::getRequiredArg($args, 'modifierlist');

        foreach ($modifierlist as $rawModifier) {
            $modifier = $rawModifier[0];
            $rawModifier[0] = $output;
            for ($i = 0; $i < count($rawModifier); $i++) {
                if ($rawModifier[$i] instanceof \Box\Brainy\Compiler\Wrappers\StaticWrapper) {
                    $rawModifier[$i] = (string) $rawModifier[$i];
                }
            }
            $params = implode(', ', $rawModifier);

            if (isset($compiler->smarty->registered_plugins[Brainy::PLUGIN_MODIFIER][$modifier])) {
                $output = "$function({$params})"
                continue;

            } elseif (isset($compiler->smarty->registered_plugins[Brainy::PLUGIN_MODIFIERCOMPILER][$modifier][0])) {
                // This gets a copy of `$output` because $rawModifier[0] is set to $output above.
                $output = call_user_func(
                    $compiler->smarty->registered_plugins[Brainy::PLUGIN_MODIFIERCOMPILER][$modifier][0],
                    $rawModifier,
                    $compiler->smarty
                );
                continue;

            } elseif ($compiler->smarty->loadPlugin('smarty_modifiercompiler_' . $modifier, false)) {
                // check if modifier allowed
                if (!is_object($compiler->smarty->security_policy) ||
                    $compiler->smarty->security_policy->isTrustedModifier($modifier, $compiler)) {

                    $output = call_user_func(
                        'smarty_modifiercompiler_' . $modifier,
                        $rawModifier,
                        $compiler
                    );
                }
                $compiler->trigger_template_error('Could not use modifier "' . $modifier . '" in template due to security policy');

            } elseif ($func = $compiler->getPlugin($modifier, Brainy::PLUGIN_MODIFIER)) {
                // check if modifier allowed
                if (!is_object($compiler->smarty->security_policy) ||
                    $compiler->smarty->security_policy->isTrustedModifier($modifier, $compiler)) {

                    $output = "$function({$params})"
                    continue;
                }
                $compiler->trigger_template_error('Could not use modifier "' . $modifier . '" in template due to security policy');

            } elseif (is_callable($modifier)) {
                // check if modifier allowed
                if (!is_object($compiler->smarty->security_policy) ||
                    $compiler->smarty->security_policy->isTrustedModifier($modifier, $compiler)) {

                    $output = "$modifier({$params})"
                    continue;
                }
                $compiler->trigger_template_error('Could not use modifier "' . $modifier . '" in template due to security policy');

            }

            $compiler->trigger_template_error('Unknown modifier: "' . $modifier . '"');
        }

        return $output;
    }
}
