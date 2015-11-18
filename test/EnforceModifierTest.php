<?php

namespace Box\Brainy\Tests;


class EnforceModifierTest extends Smarty_TestCase
{
    public function setUp()
    {
        parent::setUp();
        // We want to show all errors for this test suite.
        error_reporting(E_ALL);
    }

    public function testNormalExpressionsPass()
    {
        $this->expectOutputString('foo');
        // Static expressions should not require escaping by default.
        \Box\Brainy\Brainy::$enforce_expression_modifiers = array('foo');
        $this->smarty->display('eval:{"foo"}');
    }

    public function testModifiedExpressionsPass()
    {
        $this->expectOutputString('foo');
        // Static expressions should not require escaping by default.
        \Box\Brainy\Brainy::$enforce_expression_modifiers = array('foo');
        $this->smarty->display('eval:{"foo"|escape}');
    }

    public function testDeeplyModifiedExpressionsPass()
    {
        $this->expectOutputString('Foo');
        // Static expressions should not require escaping by default.
        \Box\Brainy\Brainy::$enforce_expression_modifiers = array('foo');
        $this->smarty->display('eval:{"foo"|escape|capitalize}');
    }

    public function testModifiedExpressionsWithAttributesPass()
    {
        $this->expectOutputString('%66%6f%6f');
        // Static expressions should not require escaping by default.
        \Box\Brainy\Brainy::$enforce_expression_modifiers = array('foo');
        $this->smarty->display('eval:{"foo"|escape:"hex"}');
    }


    /**
     * @expectedException \Box\Brainy\Exceptions\BrainyModifierEnforcementException
     */
    public function testDeeplyModifiedExpressionsThrow()
    {
        \Box\Brainy\Brainy::$enforce_expression_modifiers = array('foo');
        $this->smarty->assign('foo', 'bar');
        $this->smarty->display('eval:{$foo|escape|capitalize}');
    }

    /**
     * @expectedException \Box\Brainy\Exceptions\BrainyModifierEnforcementException
     */
    public function testModifiedExpressionsWithAttributesThrow()
    {
        \Box\Brainy\Brainy::$enforce_expression_modifiers = array('foo');
        $this->smarty->display('eval:{$foo|escape:"hex"}');
    }


    public function testModifiedExpressionsDoNotThrow()
    {
        $this->expectOutputString('foo');
        \Box\Brainy\Brainy::$enforce_expression_modifiers = array('escape');
        $this->smarty->display('eval:{"foo"|escape}');
    }

    public function testModifiedExpressionsWithAttributesDoNotThrow()
    {
        $this->expectOutputString('%66%6f%6f');
        \Box\Brainy\Brainy::$enforce_expression_modifiers = array('escape');
        $this->smarty->display('eval:{"foo"|escape:"hex"}');
    }

    /**
     * @expectedException \Box\Brainy\Exceptions\BrainyModifierEnforcementException
     */
    public function testBareSmartyVariablesThrow()
    {
        \Box\Brainy\Brainy::$enforce_expression_modifiers = array('escape');
        $this->smarty->fetch('eval:{foreach [] as $foo name="foo"}{/foreach}{$smarty.foreach.foo.first}');
    }

    public function testProtectedSmartyVariablesThrow()
    {
        \Box\Brainy\Brainy::$enforce_expression_modifiers = array('escape');
        $this->smarty->fetch('string:{foreach [] as $foo name="foo"}{$smarty.foreach.foo.last|escape}{/foreach}');
    }

    /**
     * @expectedException \Box\Brainy\Exceptions\BrainyModifierEnforcementException
     */
    public function testNonStaticModifiersThrow()
    {
        \Box\Brainy\Brainy::$enforce_expression_modifiers = array('foo');
        $this->smarty->assign('escapetype', 'html');
        $this->smarty->display('eval:{"foo"|escape:$escapetype}');
    }

    /**
     * @expectedException \Box\Brainy\Exceptions\BrainyModifierEnforcementException
     */
    public function testNestedNonStaticModifiersThrow()
    {
        \Box\Brainy\Brainy::$enforce_expression_modifiers = array('foo');
        $this->smarty->assign('escapetype', 'html');
        $this->smarty->display('eval:{"foo"|escape:$escapetype|capitalize}');
    }


    public function passingExampleProvider()
    {
        return array(
            array('{($integer+1)|escape:"html"}', array('escape'), '124'),
            array('{$integer+1|escape:"html"}', array('escape'), '124'),
        );
    }

    /**
     * @dataProvider passingExampleProvider
     */
    public function testPassingExamples($example, $modifiers, $expected)
    {
        $this->expectOutputString($expected);
        $this->smarty->assign('integer', 123);
        \Box\Brainy\Brainy::$enforce_expression_modifiers = $modifiers;
        $this->smarty->display('eval:' . $example);
    }


    /**
     * @expectedException \Box\Brainy\Exceptions\BrainyModifierEnforcementException
     */
    public function testTernaryOperator()
    {
        \Box\Brainy\Brainy::$enforce_expression_modifiers = array('foo');
        $this->smarty->assign('foo', 'x');
        $this->smarty->display('eval:{(true)?$foo:$bar}');
    }

    public function testTernaryOperatorEscaped()
    {
        $this->expectOutputString('x');
        \Box\Brainy\Brainy::$enforce_expression_modifiers = array('escape');
        $this->smarty->assign('foo', 'x');
        $this->smarty->display('eval:{((true)?$foo:$bar)|escape}');
    }

    public function testTernaryStatic()
    {
        $this->expectOutputString('x');
        \Box\Brainy\Brainy::$enforce_expression_modifiers = array('escape');
        $this->smarty->display('eval:{(true)?"x":"y"}');
    }

}
