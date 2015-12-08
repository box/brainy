<?php

namespace Box\Brainy\Compiler\Constructs;

use \Box\Brainy\Brainy;

class ConstructPrintExpression extends BaseConstruct
{
    /**
     * Compiles the opening tag for a function
     * @param  \Box\Brainy\Compiler\TemplateCompiler $compiler A compiler reference
     * @param  array|null                            $args     Arguments
     * @return mixed
     */
    public static function compileOpen(\Box\Brainy\Compiler\TemplateCompiler $compiler, $args)
    {
        $output = self::getRequiredArg($args, 'value');

        // default modifier
        if (!empty($compiler->smarty->default_modifiers)) {
            if (empty($compiler->default_modifier_list)) {
                self::populateCompilerDefaultMethods($compiler);
            }
            $output = ConstructModifier::compileOpen(
                $compiler,
                array(
                'value' => $output,
                'modifierlist' => $compiler->default_modifier_list,
                ),
                null
            );
        }

        // autoescape html
        if ($compiler->template->smarty->escape_html) {
            $output = "htmlspecialchars({$output}, ENT_QUOTES, 'UTF-8')";
        }

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
            for ($i = 0, $count = count($mod_array[0]); $i < $count; $i++) {
                if ($mod_array[0][$i] !== ':') {
                    $modifierlist[$key][] = $mod_array[0][$i];
                }
            }
        }
        $compiler->default_modifier_list = $modifierlist;
    }
}
