<?php

namespace Box\Brainy\Tests;


class GenericErrorTest extends Smarty_TestCase
{
    public function testNoneExistingTemplateError()
    {
        $this->expectException(\Box\Brainy\Exceptions\SmartyException::class);
        $this->expectExceptionMessage('Unable to load template');
        $this->smarty->display('eval:{include file=\'no.tpl\'}');
    }

    public function testUnknownTagError()
    {
        $this->expectException(\Box\Brainy\Exceptions\SmartyException::class);
        $this->expectExceptionMessage('Call to undefined function \'unknown\'. Defined functions: <none>');
        $this->smarty->display('eval:{unknown}');
    }

    public function testUnclosedTagError()
    {
        $this->expectException(\Box\Brainy\Exceptions\SmartyCompilerException::class);
        $this->expectExceptionMessage('"{if true}" unclosed {if} tag');
        $this->smarty->display('eval:{if true}');
    }
    public function testSyntaxError()
    {
        $this->expectException(\Box\Brainy\Exceptions\SmartyCompilerException::class);
        $this->expectExceptionMessage('"{assign var=}"  - Unexpected "}"');
        $this->smarty->display('eval:{assign var=}');
    }

    public function testEmptyTemplate()
    {
        $tpl = $this->smarty->createTemplate('eval:');
        $this->assertEmpty($this->smarty->display($tpl));
    }

}
