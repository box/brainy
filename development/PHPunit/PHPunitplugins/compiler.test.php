<?php
// compiler.test.php
class smarty_compiler_test extends Smarty_Internal_CompileBase
{
    public function compile($args, $compiler)
    {
        $this->required_attributes = array('data');

        $_attr = $this->getAttributes($compiler, $args);

        $this->openTag($compiler, 'test');

        return "echo 'test output';\n";
    }

}

// compiler.testclose.php
class smarty_compiler_testclose extends Smarty_Internal_CompileBase
{
    public function compile($args, $compiler)
    {

        $this->closeTag($compiler, 'test');

        return '';
    }

}
