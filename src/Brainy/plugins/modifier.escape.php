<?php
/**
 * Smarty plugin
 *
 * @package    Brainy
 * @subpackage PluginsModifier
 */

/**
 * Smarty escape modifier plugin
 *
 * Type:     modifier<br>
 * Name:     escape<br>
 * Purpose:  escape string for output
 *
 * @link   http://www.smarty.net/manual/en/language.modifier.count.characters.php count_characters (Smarty online manual)
 * @author Monte Ohrt <monte at ohrt dot com>
 * @param  string  $string        input string
 * @param  string  $esc_type      escape type
 * @param  string  $char_set      character set, used for htmlspecialchars() or htmlentities()
 * @param  boolean $double_encode encode already encoded entitites again, used for htmlspecialchars() or htmlentities()
 * @return string escaped input string
 */
function smarty_modifier_escape($string, $esc_type = 'html', $char_set = 'UTF-8', $double_encode = true)
{
    switch ($esc_type) {
        case 'html':
            return htmlspecialchars($string, ENT_QUOTES, $char_set, $double_encode);

        case 'url':
            return rawurlencode($string);

        case 'urlpathinfo':
            return str_replace('%2F', '/', rawurlencode($string));

        case 'quotes':
            // escape unescaped single quotes
            return preg_replace("%(?<!\\\\)'%", "\\'", $string);

        case 'hex':
            // escape every byte into hex
            // Note that the UTF-8 encoded character ä will be represented as %c3%a4
            $return = '';
            $_length = strlen($string);
            for ($x = 0; $x < $_length; $x++) {
                $return .= '%' . bin2hex($string[$x]);
            }

            return $return;

        case 'javascript':
            // escape quotes and backslashes, newlines, etc.
            return strtr($string, array('\\' => '\\\\', "'" => "\\'", '"' => '\\"', "\r" => '\\r', "\n" => '\\n', '</' => '<\/'));

        default:
            throw new Exception('Unrecognized escape option "' . $esc_type . '"');
    }
}
