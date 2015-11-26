<?php

namespace Box\Brainy\Compiler\Constructs;

class ConstructBlockTerminal extends ConstructBlockNonterminal
{
    /**
     * @param  \Box\Brainy\Compiler\TemplateCompiler $compiler A compiler reference
     * @param  array|null                            $args     Arguments
     * @return mixed
     */
    public static function compileOpen(\Box\Brainy\Compiler\TemplateCompiler $compiler, $args)
    {
        $name = self::getName($compiler, $args);
        $forced = self::getOptionalArg($args, 'force');

        $childBlockVar = '$' . $compiler->getUniqueVarName();

        self::openTag(
            $compiler,
            'block',
            array(
            'name' => $name,
            'childVar' => $childBlockVar,
            'forced' => $forced,
            )
        );

        $nameVar = '$' . $compiler->getUniqueVarName();
        $output = "$nameVar = $name;\n"; // Guaranteed once execution

        if ($forced) {
            $output .= "if (array_key_exists($nameVar, \$_smarty_tpl->tpl_vars['smarty']['blocks'])) {\n";
            $output .= "  $childBlockVar = \$_smarty_tpl->tpl_vars['smarty']['blocks'][$nameVar];\n";
            $output .= "} else {\n";
            $output .= "  $childBlockVar = null;\n";
            $output .= "}\n";
            return $output;
        }


        $output .= "if (array_key_exists($nameVar, \$_smarty_tpl->tpl_vars['smarty']['blocks'])) {\n";
        $output .= "  \$_smarty_tpl->tpl_vars['smarty']['blocks'][$nameVar](\$_smarty_tpl);\n";
        $output .= "} else {\n";
        $output .= "  $childBlockVar = null;\n";
        return $output;
    }

    public static function compileClose(\Box\Brainy\Compiler\TemplateCompiler $compiler, $args)
    {
        $data = self::closeTag($compiler, 'block');
        if ($data['forced']) {
            return '';
        }
        return "}\n";
    }
}
