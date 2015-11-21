<?php

function smarty_compiler_testclose($args, $compiler) {
    array_pop($compiler->_tag_stack);
    return "";
}
