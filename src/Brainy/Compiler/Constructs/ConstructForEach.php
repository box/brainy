<?php

namespace Box\Brainy\Compiler\Constructs;

use \Box\Brainy\Brainy;


class ConstructForEach extends ClosedBaseConstruct
{
    /**
     * @param  \Box\Brainy\Compiler\TemplateCompiler $compiler A compiler reference
     * @param  array|null                            $args     Arguments
     * @return mixed
     */
    public static function compileOpen(\Box\Brainy\Compiler\TemplateCompiler $compiler, $args)
    {
        $from = self::getRequiredArg($args, 'from');
        $item = self::getRequiredArg($args, 'item');
        $key = self::getOptionalArg($args, 'key');

        $name = self::getOptionalArg($args, 'name');

        $usages = self::getUsages($compiler, $name);

        $innerVarVar = '$' . $compiler->getUniqueVarName();
        $output = "$innerVarVar = array('source' => $from);\n";
        if ($name) {
            $output .= "\$_smarty_tpl->tpl_vars['smarty']->value['foreach'][$name] = &$innerVarVar;\n";
        }

        $output .= "if (!empty({$innerVarVar}['source'])) {\n";

        $output .= "\$_smarty_tpl->tpl_vars[$item] = new \\Box\\Brainy\\Templates\\Variable();\n";
        $itemVar = "\$_smarty_tpl->tpl_vars[$item]->value";

        if ($key) {
            $output .= "\$_smarty_tpl->tpl_vars[$key] = new \\Box\\Brainy\\Templates\\Variable();\n";
            $keyVar = "\$_smarty_tpl->tpl_vars[$key]->value";
        }

        if ($usages['total'] || $usages['last'] || $usages['show']) {
            $output .= "{$innerVarVar}['total'] = \\Box\\Brainy\\Runtime\\Loops::getCount({$innerVarVar}['source']);\n";
        }
        if ($usages['iteration']) {
            $output .= "{$innerVarVar}['iteration'] = 0;\n";
        }
        if ($usages['index'] || $usages['first'] || $usages['last']) {
            $output .= "{$innerVarVar}['index'] = -1;\n";
        }
        if ($usages['show']) {
            $output .= "{$innerVarVar}['show'] = {$innerVarVar}['total'] > 0;\n";
        }

        if ($key) {
            $output .= "foreach ({$innerVarVar}['source'] as $keyVar => $itemVar) {\n";
        } else {
            $output .= "foreach ({$innerVarVar}['source'] as $itemVar) {\n";
        }

        if ($usages['iteration']) {
            $output .= "{$innerVarVar}['iteration']++;\n";
        }
        if ($usages['index'] || $usages['first'] || $usages['last']) {
            $output .= "{$innerVarVar}['index']++;\n";
        }
        if ($usages['first']) {
            $output .= "{$innerVarVar}['first'] = {$innerVarVar}['index'] === 0;\n";
        }
        if ($usages['last']) {
            $output .= "{$innerVarVar}['last'] = {$innerVarVar}['index'] + 1 === {$innerVarVar}['total'];\n";
        }

        self::openTag($compiler, 'foreach');

        return $output;
    }

    /**
     * Compiles the closing tag for a function
     * @param  \Box\Brainy\Compiler\TemplateCompiler $compiler A compiler reference
     * @param  array|null                            $args     Arguments
     * @return mixed
     */
    public static function compileClose(\Box\Brainy\Compiler\TemplateCompiler $compiler, $args)
    {
        list($openTag) = self::closeTag($compiler, array('foreach', 'foreachelse'));

        if ($openTag == 'foreachelse') {
            return "}\n";
        } else {
            return "}\n}\n";
        }
    }

    /**
     * @param  \Box\Brainy\Compiler\TemplateCompiler $compiler
     * @param  string                                $name
     * @return array
     */
    private static function getUsages($compiler, $name)
    {
        $data = $compiler->lex->data;

        return array(
            'first' => strpos($data, '.first') !== false,
            'last' => strpos($data, '.last') !== false,
            'index' => strpos($data, '.index') !== false,
            'iteration' => strpos($data, '.iteration') !== false,
            'show' => strpos($data, '.show') !== false,
            'total' => strpos($data, '.total') !== false,
        );
    }
}
