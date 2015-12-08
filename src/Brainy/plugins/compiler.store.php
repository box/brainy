<?php

/**
 * @author Matt Basta
 * @param string[]           $params   parameters
 * @param \Box\Brainy\Brainy $compiler template object
 * @return string
 */
function smarty_compiler_store($params, $compiler)
{
    array_push($compiler->_tag_stack, array('store', $params['to']));
    return "ob_start();\n";
}
