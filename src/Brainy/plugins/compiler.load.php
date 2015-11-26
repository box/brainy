<?php
/**
 * Brainy plugin
 * @package Brainy
 * @subpackage PluginsBlockCompiler
 */

/**
 * @author Matt Basta
 * @param string[]           $params   parameters
 * @param \Box\Brainy\Brainy $compiler template object
 * @return string
 */
function smarty_compiler_load($params, $compiler)
{
    return <<<OUT
echo \$_smarty_tpl->tpl_vars['smarty']['ls_loadables'][{$params['from']}];
OUT;
}
