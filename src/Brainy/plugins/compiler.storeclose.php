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
function smarty_compiler_storeclose($params, $compiler) {
    return <<<DOC
\$tmp1 = array_pop(\$_smarty_tpl->tpl_vars['smarty']->value['ls_stores']);
\$tmp2 = ob_get_clean();

if (!\$_smarty_tpl->tpl_vars['smarty']->value) {
    \$_smarty_tpl->tpl_vars['smarty']->value = array('ls_loadables' => array());
} elseif (!array_key_exists('ls_loadables', \$_smarty_tpl->tpl_vars['smarty']->value)) {
    \$_smarty_tpl->tpl_vars['smarty']->value['ls_loadables'] = array();
}
\$_smarty_tpl->tpl_vars['smarty']->value['ls_loadables'][\$tmp1] = \$tmp2;
ob_start();
DOC;
}
