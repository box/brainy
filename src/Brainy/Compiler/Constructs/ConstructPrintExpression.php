<?php

namespace Box\Brainy\Compiler\Constructs;

use \Box\Brainy\Brainy;


class ConstructPrintExpression extends BaseConstruct
{
    /**
     * Compiles the opening tag for a function
     * @param  \Box\Brainy\Compiler\TemplateCompiler $compiler A compiler reference
     * @param  array|null  $args     Arguments
     * @param  array|null  $params   Parameters
     * @return mixed
     */
    public function compileOpen(\Box\Brainy\Compiler\TemplateCompiler $compiler, array $args, array $params)
    {
        $output = $this->getRequiredArg($args, 'value');
        $modifiers = $this->getOptionalArg($args, 'modifierlist', array());

        $attributes = $this->getAttributes($compiler, $params);

        if (!empty($modifiers)) {
            $output = ConstructModifier::compileOpen($compiler, array(
                'value' => $output,
                'modifierlist' => $modifiers,
            ));
        }

        // default modifier
        if (!empty($compiler->smarty->default_modifiers)) {
            if (empty($compiler->default_modifier_list)) {
                self::populateCompilerDefaultMethods($compiler);
            }
            $output = ConstructModifier::compileOpen($compiler, array(
                'value' => $output,
                'modifierlist' => $compiler->default_modifier_list,
            ));
        }

        if (!$attributes['nofilter']) {
            $output = self::applyRegisteredFilters($output, $compiler->template->smarty->registered_filters[Brainy::FILTER_VARIABLE]);
            $output = self::applyAutoloadFilters($output, (array) $compiler->template->smarty->autoload_filters[Brainy::FILTER_VARIABLE], $compiler);
            $output = self::applyVariableFilters($output, $compiler->template->variable_filters, $compiler);
        }

        // autoescape html
        if ($compiler->template->smarty->escape_html) {
            $output = "htmlspecialchars({$output}, ENT_QUOTES, 'UTF-8')";
        }

        $compiler->has_output = true;

        return "echo $output;\n";
    }

    /**
     * Populates the default_modifier_list member of the compiler instance
     * @param  \Box\Brainy\Compiler\TemplateCompiler $compiler
     * @return void
     */
    private static function populateCompilerDefaultMethods($compiler)
    {
        $modifierlist = array();
        foreach ($compiler->smarty->default_modifiers as $key => $single_default_modifier) {
            preg_match_all('/(\'[^\'\\\\]*(?:\\\\.[^\'\\\\]*)*\'|"[^"\\\\]*(?:\\\\.[^"\\\\]*)*"|:|[^:]+)/', $single_default_modifier, $mod_array);
            for ($i = 0, $count = count($mod_array[0]);$i < $count;$i++) {
                if ($mod_array[0][$i] !== ':') {
                    $modifierlist[$key][] = $mod_array[0][$i];
                }
            }
        }
        $compiler->default_modifier_list = $modifierlist;
    }

    /**
     * Applies an array of registered filters to the output
     * @param  string $output
     * @param  array $filters The array of filters
     * @return string
     */
    private static function applyRegisteredFilters($output, $filters)
    {
        foreach ($filters as $key => $func) {
            $output = "$function($output, \$_smarty_tpl)";
        }
        return $output;
    }

    /**
     * Applies an array of autoload filters to the output
     * @param  string $output
     * @param  array $filters The array of filters
     * @param  \Box\Brainy\Compiler\TemplateCompiler $compiler
     * @return string
     */
    private static function applyAutoloadFilters($output, $filters, $compiler)
    {
        foreach ($filters as $name) {
            $funcName = "smarty_variablefilter_{$name}";
            $path = $compiler->smarty->loadPlugin($funcName, false);
            if (!$path) {
                throw new \Box\Brainy\Exceptions\SmartyCompilerException('Could not find filter "' . $name . '"');
            }
            $compiler->template->required_plugins['compiled'][$name][Brainy::FILTER_VARIABLE]['file'] = $path;
            $compiler->template->required_plugins['compiled'][$name][Brainy::FILTER_VARIABLE]['function'] = $funcName;

            $output = "{$funcName}({$output}, \$_smarty_tpl)";
        }
        return $output;
    }

    /**
     * Applies an array of registered filters to the output
     * @param  string $output
     * @param  array $filters The array of filters
     * @param  \Box\Brainy\Compiler\TemplateCompiler $compiler
     * @return string
     */
    private static function applyVariableFilters($output, $filters, $compiler)
    {
        foreach ($filters as $filter) {
            if (count($filter) === 1) {
                $funcName = "smarty_variablefilter_{$name}";
                $path = $compiler->smarty->loadPlugin($funcName, false);
                if (!$path) {
                    throw new \Box\Brainy\Exceptions\SmartyCompilerException('Could not find filter "' . $name . '"');
                }
                $compiler->template->required_plugins['compiled'][$name][Brainy::FILTER_VARIABLE]['file'] = $path;
                $compiler->template->required_plugins['compiled'][$name][Brainy::FILTER_VARIABLE]['function'] = $funcName;

                $output = "{$funcName}({$output}, \$_smarty_tpl)";
            } else {
                $output = ConstructModifier::compileOpen($compiler, array(
                    'value' => $output,
                    'modifierlist' => array($filter),
                ));
            }
        }
        return $output;
    }

}
