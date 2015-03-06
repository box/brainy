<?php
/**
 * Brainy plugin
 * @package Brainy
 * @subpackage PluginsBlockCompiler
 */

/**
 * @author Matt Basta
 * @param string[] $params parameters
 * @param Smarty $compiler template object
 * @return string
 */
function smarty_compiler_store($params, $compiler) {
    return <<<DOC
if (!\$_smarty_tpl->tpl_vars['smarty']->value) {
    \$_smarty_tpl->tpl_vars['smarty']->value = array('ls_stores' => array());
} elseif (!array_key_exists('ls_stores', \$_smarty_tpl->tpl_vars['smarty']->value)) {
    \$_smarty_tpl->tpl_vars['smarty']->value['ls_stores'] = array();
}
\$_smarty_tpl->tpl_vars['smarty']->value['ls_stores'][] = {$params['to']};
ob_start();
DOC;
}
