<?php

namespace Box\Brainy\Tests;


class GenericErrorTest extends Smarty_TestCase
{
    /**
     * @expectedException \Box\Brainy\Exceptions\SmartyException
     * @expectedExceptionMessage Unable to load template
     */
    public function testNoneExistingTemplateError()
    {
        $this->smarty->display('eval:{include file=\'no.tpl\'}');
    }

    /**
     * @expectedException \Box\Brainy\Exceptions\SmartyException
     * @expectedExceptionMessage Call to undefined function 'unknown'. Defined functions: <none>
     */
    public function testUnknownTagError()
    {
        $this->smarty->display('eval:{unknown}');
    }

    /**
     * @expectedException \Box\Brainy\Exceptions\SmartyCompilerException
     * @expectedExceptionMessage "{if true}" unclosed {if} tag
     */
    public function testUnclosedTagError()
    {
        $this->smarty->display('eval:{if true}');
    }
    /**
     * @expectedException \Box\Brainy\Exceptions\SmartyCompilerException
     * @expectedExceptionMessage "{assign var=}"  - Unexpected "}"
     */
    public function testSyntaxError()
    {
        $this->smarty->display('eval:{assign var=}');
    }

    public function testEmptyTemplate()
    {
        $tpl = $this->smarty->createTemplate('eval:');
        $this->assertEquals('', $this->smarty->display($tpl));
    }

}
