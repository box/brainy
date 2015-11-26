<?php

/**
 * @author Matt Basta
 * @param string[]           $params   parameters
 * @param \Box\Brainy\Brainy $compiler template object
 * @return string
 */
function smarty_compiler_storeclose($params, $compiler)
{
    if (count($compiler->_tag_stack) === 0) {
        $compiler->trigger_template_error('unexpected closing tag', $compiler->lex->taglineno);
    }

    // get stacked info
    list($openTag, $to) = array_pop($compiler->_tag_stack);

    if ($openTag !== 'store') {
        $compiler->trigger_template_error('Got {/' . $openTag . '}, but expected {/store}', $compiler->lex->taglineno);
    }

    return "\$_smarty_tpl->tpl_vars['smarty']['ls_loadables'][$to] = ob_get_clean();\n";
}
