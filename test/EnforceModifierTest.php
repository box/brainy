<?php
/**
 * Brainy modifier enforcement tests
 *
 * @package PHPunit
 * @author Matt Basta
 */

class EnforceModifierTest extends PHPUnit_Framework_TestCase
{
    public function setUp() {
        $this->smarty = SmartyTests::$smarty;
        SmartyTests::init();
        // We want to show all errors for this test suite.
        error_reporting(E_ALL);
    }
    public function tearDown() {
        Smarty::$enforce_expression_modifiers = array();
    }

    public function testNormalExpressionsPass() {
        $this->expectOutputString('foo');
        $this->smarty->display('eval:{"foo"}');
    }

    public function testModifiedExpressionsPass() {
        $this->expectOutputString('foo');
        $this->smarty->display('eval:{"foo"|escape}');
    }

    public function testDeeplyModifiedExpressionsPass() {
        $this->expectOutputString('Foo');
        $this->smarty->display('eval:{"foo"|escape|capitalize}');
    }

    public function testModifiedExpressionsWithAttributesPass() {
        $this->expectOutputString('%66%6f%6f');
        $this->smarty->display('eval:{"foo"|escape:"hex"}');
    }

    /**
     * @expectedException SmartyCompilerException
     */
    public function testNormalExpressionsThrow() {
        Smarty::$enforce_expression_modifiers = array('foo');
        $this->smarty->display('eval:{"foo"}');
    }

    /**
     * @expectedException SmartyCompilerException
     */
    public function testModifiedExpressionsThrow() {
        Smarty::$enforce_expression_modifiers = array('foo');
        $this->smarty->display('eval:{"foo"|escape}');
    }

    /**
     * @expectedException SmartyCompilerException
     */
    public function testDeeplyModifiedExpressionsThrow() {
        Smarty::$enforce_expression_modifiers = array('foo');
        $this->smarty->display('eval:{"foo"|escape|capitalize}');
    }

    /**
     * @expectedException SmartyCompilerException
     */
    public function testModifiedExpressionsWithAttributesThrow() {
        Smarty::$enforce_expression_modifiers = array('foo');
        $this->smarty->display('eval:{"foo"|escape:"hex"}');
    }


    public function testModifiedExpressionsDoNotThrow() {
        $this->expectOutputString('foo');
        Smarty::$enforce_expression_modifiers = array('escape');
        $this->smarty->display('eval:{"foo"|escape}');
    }

    public function testModifiedExpressionsWithAttributesDoNotThrow() {
        $this->expectOutputString('%66%6f%6f');
        Smarty::$enforce_expression_modifiers = array('escape');
        $this->smarty->display('eval:{"foo"|escape:"hex"}');
    }

    /**
     * @expectedException SmartyCompilerException
     */
    public function testExpressionsThatDoNotEndWithEnforcedModifiersThrow() {
        Smarty::$enforce_expression_modifiers = array('escape');
        $this->smarty->display('eval:{"foo"|escape|capitalize}');
    }

    /**
     * @expectedException SmartyCompilerException
     */
    public function testBareSmartyVariablesThrow() {
        Smarty::$enforce_expression_modifiers = array('escape');
        $this->smarty->display('eval:{$smarty.request.foo}');
    }

    public function testProtectedSmartyVariablesThrow() {
        $_REQUEST['foo'] = 'bar';
        $this->expectOutputString('bar');
        Smarty::$enforce_expression_modifiers = array('escape');
        $this->smarty->display('eval:{$smarty.request.foo|escape}');
    }

}
