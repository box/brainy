<?php
/**
* Smarty PHPunit tests of function calls
*
* @package PHPunit
* @author Uwe Tews
*/

/**
* class for function tests
*/
class FunctionTest extends PHPUnit_Framework_TestCase
{
    public function setUp() {
        $this->smarty = SmartyTests::$smarty;
        SmartyTests::init();
        $this->smarty->enableSecurity();
    }

    /**
    * test unknown function error
    */
    public function testUnknownFunction() {
        try {
            $this->smarty->fetch('eval:{unknown()}');
        } catch (Exception $e) {
            $this->assertContains("PHP function 'unknown' not allowed by security setting", $e->getMessage());

            return;
        }
        $this->fail('Exception for unknown function has not been raised.');
    }

    public function testTemplateFunctionDefaultParam() {
        $this->assertEquals(
            $this->smarty->fetch('eval:{function name="foo" def="hello"}{$def}{/function}{foo}'),
            'hello'
        );
    }

    public function testTemplateFunctionDefaultParamOverridden() {
        $this->assertEquals(
            $this->smarty->fetch('eval:{function name="bar" def="hello"}{$def}{/function}{bar def="goodbye"}'),
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
