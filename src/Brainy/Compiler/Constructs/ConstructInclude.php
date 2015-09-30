<?php

namespace Box\Brainy\Compiler\Constructs;

use \Box\Brainy\Brainy;
use \Box\Brainy\Exceptions\SmartyCompilerException;


class ConstructInclude extends BaseConstruct
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
        try {
            $file = self::getRequiredArg($args, 'file');
        } catch (SmartyCompilerException $e) {
            if (!isset($args[0])) {
                throw $e;
            }
            $file = $args[0];
        }
        $file = (string) $file;
        $assign = self::getOptionalArg($args, 'assign');
        $compileID = self::getOptionalArg($args, 'compile_id', "''");
        $scope = ConstructAssign::getScope($args, Brainy::SCOPE_LOCAL);

        if (!$assign) {
            return self::getDisplayCode($file, $compileID, $scope, $args);
        }

        $output = 'ob_start();';
        $output .= self::getDisplayCode($file, $compileID, $scope, $args);
        $output .= "\$_smarty_tpl->assign($assign, ob_get_clean(), $scope);\n";
        return $output;

    }

    /**
     * Gets the PHP code to execute the included template
     * @param  string $templatePath
     * @param  string|null $compileID
     * @param  int $scope
     * @return string
     */
    protected static function getDisplayCode($templatePath, $compileID, $scope, $data)
    {
        if ($file instanceof \Box\Brainy\Compiler\Helpers\ParseTree) {
            $file = $file->to_smarty_php();
        }

        $data = self::flattenCompiledArray($data);
        unset($data['assign']);
        unset($data['compile_id']);
        unset($data['file']);
        unset($data['inline']);
        unset($data['scope']);

        return '$_smarty_tpl->renderSubTemplate(' .
            $templatePath . ', ' .
            $compileID . ', ' .
            var_export($data, true) . ', ' .
            var_export($scope, true) .
            ");\n";
    }

}
