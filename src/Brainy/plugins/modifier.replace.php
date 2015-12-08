<?php
/**
 * Smarty plugin
 * @package Brainy
 * @subpackage PluginsModifier
 */

/**
 * Smarty replace modifier plugin
 *
 * Type:     modifier<br>
 * Name:     replace<br>
 * Purpose:  simple search/replace
 *
 * @link   http://smarty.php.net/manual/en/language.modifier.replace.php replace (Smarty online manual)
 * @author Monte Ohrt <monte at ohrt dot com>
 * @author Uwe Tews
 * @param  string $string  input string
 * @param  string $search  text to search for
 * @param  string $replace replacement text
 * @return string
 */
function smarty_modifier_replace($subject, $search, $replace) 
{
    if (!is_array($search) && is_array($replace)) {
        return false;
    }
    if (is_array($subject)) {
        // call mb_replace for each single string in $subject
        foreach ($subject as &$string) {
            $string = &smarty_modifier_replace($string, $search, $replace);
        }
    } elseif (is_array($search)) {
        if (!is_array($replace)) {
            foreach ($search as &$string) {
                $subject = smarty_modifier_replace($subject, $string, $replace);
            }
        } else {
            $n = max(count($search), count($replace));
            while ($n--) {
                $subject = smarty_modifier_replace($subject, current($search), current($replace));
                next($search);
                next($replace);
            }
        }
    } else {
        $parts = mb_split(preg_quote($search), $subject);
        $subject = implode($replace, $parts);
    }

    return $subject;
}
