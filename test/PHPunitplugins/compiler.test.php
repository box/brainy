<?php

function smarty_compiler_test($args, $compiler) {
    array_push($compiler->_tag_stack, array('test', null));
    return "echo 'test output';\n";
}
