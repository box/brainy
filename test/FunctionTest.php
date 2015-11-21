<?php
/**
* Smarty PHPunit tests of function calls
*
* @package PHPunit
* @author Uwe Tews
*/

namespace Box\Brainy\Tests;


class FunctionTest extends Smarty_TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->smarty->enableSecurity();
    }

    /**
     * @expectedExceptionMessage PHP function 'unknown' not allowed by security setting
     * @expectedException \Box\Brainy\Exceptions\SmartyCompilerException
     */
    public function testUnknownFunction()
    {
        $this->smarty->display('eval:{unknown()}');
    }

    public function testTemplateFunctionDefaultParam() {
        $this->assertEquals(
            $this->smarty->fetch('eval:{function name="foo" def="hello"}{$def}{/function}{foo}'),
            'hello'
        );
    }

    public function testTemplateFunctionDefaultParamOverridden() {
        $this->assertEquals(
            $this->smarty->fetch('string:{function name="bar" def="hello"}{$def}{/function}{bar def="goodbye"}'),
            'goodbye'
        );
    }

    public function testTemplateFunctionParam() {
        $this->assertEquals(
            $this->smarty->fetch('eval:{function name="zip"}{$var}{/function}{zip var="this is not a default param"}'),
            'this is not a default param'
        );
    }

    public function testTemplateFunctionWithPlugin() {
        $this->assertEquals(
            $this->smarty->fetch('eval:{function name="zap"}{$var|escape}{/function}{zap var="foo&bar"}'),
            'foo&amp;bar'
        );
    }

    public function testTemplateFunctionWithTrailingLineBreak() {
        $this->assertEquals(
            $this->smarty->fetch("eval:{function name=\"hello\"}{\$var}{/function}{hello var=\"notrailingwhitespace\"}<"),
            'notrailingwhitespace<'
        );
    }
}
