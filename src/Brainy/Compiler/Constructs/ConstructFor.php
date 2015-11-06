<?php

namespace Box\Brainy\Compiler\Constructs;

use \Box\Brainy\Brainy;


class ConstructFor extends BaseConstruct
{
    /**
     * Compiles the opening tag for a function
     * @param  \Box\Brainy\Compiler\TemplateCompiler $compiler A compiler reference
     * @param  array|null  $args     Arguments
     * @param  array|null  $params   Parameters
     * @return mixed
     */
    public static function compileOpen(\Box\Brainy\Compiler\TemplateCompiler $compiler, $args, $params)
    {
        if (isset($args['ifexp'])) {
            return self::compileOpenCStyle($compiler, $args, $params);
        } else {
            return self::compileOpenShorthand($compiler, $args, $params);
        }
    }

    /**
     * @param  \Box\Brainy\Compiler\TemplateCompiler $compiler A compiler reference
     * @param  array|null  $args     Arguments
     * @param  array|null  $params   Parameters
     * @return mixed
     */
    public static function compileOpenCStyle(\Box\Brainy\Compiler\TemplateCompiler $compiler, $args, $params)
    {
        $start = self::getRequiredArg($args, 'start');
        $ifexp = self::getRequiredArg($args, 'ifexp');
        $var = self::getRequiredArg($args, 'var');
        $step = self::getRequiredArg($args, 'step');

        $output = '';
        foreach ($start as $stmt) {
            $output .= "\$_smarty_tpl->tpl_vars[{$stmt['var']}] = new \\Box\\Brainy\\Templates\\Variable({$stmt['value']});\n";
        }
        $output .= "if ($ifexp) {\n";
        $output .= "  for (\$_foo=true; {$ifexp}; \$_smarty_tpl->tpl_vars[{$var}]->value{$step}) {\n";

        self::openTag($compiler, 'for');

        return $output;
    }

    /**
     * @param  \Box\Brainy\Compiler\TemplateCompiler $compiler A compiler reference
     * @param  array|null  $args     Arguments
     * @param  array|null  $params   Parameters
     * @return mixed
     */
    public static function compileOpenShorthand(\Box\Brainy\Compiler\TemplateCompiler $compiler, $args, $params)
    {
        $start = self::getRequiredArg($args, 'start');
        $to = self::getRequiredArg($args, 'to');
        $step = self::getOptionalArg($args, 'step', 1);
        $max = self::getOptionalArg($args, 'max', INF);

        $var = $start['var'];
        $value = $start['value'];

        $total = "ceil(($step > 0 ? $to + 1 - ($value) : $value - ($to) + 1) / abs($step))";
        if ($max !== INF) {
            $total = "min($total, $max)";
        }

        $output = "\$_smarty_tpl->tpl_vars[$var] = new \\Box\\Brainy\\Templates\\LoopVariable($step, (int) $total);\n";
        $output .= "if (\$_smarty_tpl->tpl_vars[$var]->total > 0) {\n";
        $output .= "  for (\$_smarty_tpl->tpl_vars[$var]->value = $value, \$_smarty_tpl->tpl_vars[$var]->iteration = 1; \$_smarty_tpl->tpl_vars[$var]->iteration <= \$_smarty_tpl->tpl_vars[$var]->total;\$_smarty_tpl->tpl_vars[$var]->value += \$_smarty_tpl->tpl_vars[$var]->step, \$_smarty_tpl->tpl_vars[$var]->iteration++) {\n";
        $output .= "\$_smarty_tpl->tpl_vars[$var]->first = \$_smarty_tpl->tpl_vars[$var]->iteration == 1;\n";
        $output .= "\$_smarty_tpl->tpl_vars[$var]->last = \$_smarty_tpl->tpl_vars[$var]->iteration == \$_smarty_tpl->tpl_vars[$var]->total;\n";

        self::openTag($compiler, 'for', array('for'));

        return $output;
    }

    /**
     * Compiles the closing tag for a function
     * @param  \Box\Brainy\Compiler\TemplateCompiler $compiler A compiler reference
     * @param  array|null  $args     Arguments
     * @param  array|null  $params   Parameters
     * @return mixed
     */
    public static function compileClose(\Box\Brainy\Compiler\TemplateCompiler $compiler, $args, $params)
    {
        list($openTag) = self::closeTag($compiler, array('for', 'forelse'));

        if ($openTag == 'forelse') {
            return "}\n";
        } else {
            return "}\n}\n";
        }
    }
}
