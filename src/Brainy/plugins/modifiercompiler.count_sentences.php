<?php
/**
 * Smarty plugin
 *
 * @package    Brainy
 * @subpackage PluginsModifierCompiler
 */

/**
 * Smarty count_sentences modifier plugin
 *
 * Type:     modifier<br>
 * Name:     count_sentences
 * Purpose:  count the number of sentences in a text
 *
 * @link   http://www.smarty.net/manual/en/language.modifier.count.sentences.php
 *          count_sentences (Smarty online manual)
 * @author Uwe Tews
 * @param  array $params parameters
 * @return string with compiled code
 */
function smarty_modifiercompiler_count_sentences($params, $compiler)
{
    // find periods, question marks, exclamation marks with a word before but not after.
    return 'preg_match_all("#\S[\.\?\!](\W|$)#Su' . '", ' . $params[0] . ', $tmp)';
}
