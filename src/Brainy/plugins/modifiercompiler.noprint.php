<?php
/**
 * Smarty plugin
 *
 * @package    Brainy
 * @subpackage PluginsModifierCompiler
 */

/**
 * Smarty noprint modifier plugin
 *
 * Type:     modifier<br>
 * Name:     noprint<br>
 * Purpose:  return an empty string
 *
 * @author Uwe Tews
 * @param  array $params parameters
 * @return string with compiled code
 */
function smarty_modifiercompiler_noprint($params, $compiler)
{
    $compiler->assert_is_not_strict('noprint is not allowed in strict mode');
    return "''";
}
