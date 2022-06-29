<?php

namespace Box\Brainy\Tests;


class AttributeTest extends Smarty_TestCase
{
    public function testRequiredAttributeVar()
    {
        $this->expectException(\Box\Brainy\Exceptions\SmartyCompilerException::class);
        $this->expectExceptionMessage('missing "var" attribute');
        $this->smarty->display('eval:{assign value=1}');
    }

}
