<?php
/**
 * @package Brainy
 * @subpackage PluginsModifierCompiler
 */

/**
 * Smarty escape modifier plugin
 *
 * Type:     modifier<br>
 * Name:     escape<br>
 * Purpose:  escape string for output
 *
 * @link   http://www.smarty.net/docsv2/en/language.modifier.escape
 * @author Rodney Rehm
 * @param  array $params parameters
 * @return string with compiled code
 */
function smarty_modifiercompiler_escape($params, $compiler)
{
    try {
        $esc_type = isset($params[1]) ? \Box\Brainy\Compiler\Decompile::decompileString($params[1]) : 'html';
        $double_encode = isset($params[3]) ? $params[3] : 'true';

        switch ($esc_type) {
            case 'html':
                return 'htmlspecialchars(' . $params[0] .', ENT_QUOTES, \'UTF-8\', ' . $double_encode . ')';

            case 'url':
                return 'rawurlencode(' . $params[0] . ')';

            case 'urlpathinfo':
                return 'str_replace("%2F", "/", rawurlencode(' . $params[0] . '))';

            case 'quotes':
                // escape unescaped single quotes
                return 'preg_replace("%(?<!\\\\\\\\)\'%", "\\\'",' . $params[0] . ')';

            case 'javascript':
                // escape quotes and backslashes, newlines, etc.
                return 'strtr(' . $params[0] . ', array("\\\\" => "\\\\\\\\", "\'" => "\\\\\'", "\"" => "\\\\\"", "\\r" => "\\\\r", "\\n" => "\\\n", "</" => "<\/" ))';

        }
    } catch (\Box\Brainy\Exceptions\SmartyException $e) {
        // pass through to regular plugin fallback
    }

    // could not optimize |escape call, so fallback to regular plugin
    $compiler->template->required_plugins['compiled']['escape']['modifier']['file'] = BRAINY_PLUGINS_DIR .'modifier.escape.php';
    $compiler->template->required_plugins['compiled']['escape']['modifier']['function'] = 'smarty_modifier_escape';

    return 'smarty_modifier_escape(' . join(', ', $params) . ')';
}
