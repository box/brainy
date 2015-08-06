<?php
/**
 * Smarty plugin
 *
 * @package Brainy
 * @subpackage PluginsFilter
 */

/**
 * Smarty htmlspecialchars variablefilter plugin
 *
 * @param string                   $source input string
 * @param Template $smarty Smarty object
 * @return string filtered output
 */
function smarty_variablefilter_htmlspecialchars($source, $smarty) {
    return htmlspecialchars($source, ENT_QUOTES, Brainy::$_CHARSET);
}
