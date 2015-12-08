<?php
/**
 * Smarty shared plugin
 *
 * @package    Brainy
 * @subpackage PluginsShared
 */

/**
 * escape_special_chars common function
 *
 * Function: smarty_function_escape_special_chars<br>
 * Purpose:  used by other smarty functions to escape
 *           special chars except for already escaped ones
 *
 * @author Monte Ohrt <monte at ohrt dot com>
 * @param  string $string text that should by escaped
 * @return string
 */
function smarty_function_escape_special_chars($string)
{
    return htmlspecialchars($string, ENT_COMPAT, 'UTF-8', false);
}
