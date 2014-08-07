<?php
/**
* Brainy default_assign_scope test suite
*
* @package PHPunit
* @author Matt Basta
*/

class DefaultAssignScopeTest extends PHPUnit_Framework_TestCase
{
    public function setUp() {
        $this->smarty = SmartyTests::$smarty;
        SmartyTests::init();
    }
    public function tearDown() {
        Smarty::$default_assign_scope = Smarty::SCOPE_LOCAL;
    }

    public function testDefaultIsLocalScope() {
        $this->smarty->fetch('eval:{assign var="foo" value="bar"}');
        $this->assertEquals(
            $this->smarty->getTemplateVars('foo'),
            null,
            'Should not have access to local vars'
        );
    }

    public function testRootScopeGivesAccessToVars() {
        Smarty::$default_assign_scope = Smarty::SCOPE_ROOT;
        $this->smarty->fetch('eval:{assign var="foo" value="bar"}');
        $this->assertEquals(
            $this->smarty->getTemplateVars('foo'),
            'bar',
            'Should have access to vars'
        );
    }

    public function testGlobalScopeGivesAccessToVars() {
        Smarty::$default_assign_scope = Smarty::SCOPE_GLOBAL;
        $this->smarty->fetch('eval:{assign var="foo" value="bar"}');
        $this->assertEquals(
            $this->smarty->getTemplateVars('foo'),
            'bar',
            'Should have access to vars'
        );
    }

    public function testDefaultScopeCanBeOverridden() {
        Smarty::$default_assign_scope = Smarty::SCOPE_GLOBAL;
        $this->smarty->fetch('eval:{assign var="foo" value="bar" scope="local"}');
        $this->assertEquals(
            $this->smarty->getTemplateVars('foo'),
            null,
            'Should not have access to local vars'
        );
    }

    /* Generic test for functions that use the template `assign()` method */

    public function testDefaultIsLocalScopeForCapture() {
        $this->smarty->fetch('eval:{capture assign="foo"}captured{/capture}');
        $this->assertEquals(
            $this->smarty->getTemplateVars('foo'),
            null,
            'Should not have access to local vars'
        );
    }

    public function testRootScopeGivesAccessToVarsForCapture() {
        Smarty::$default_assign_scope = Smarty::SCOPE_ROOT;
        $this->smarty->fetch('eval:{capture assign="foo"}captured{/capture}');
        $this->assertEquals(
            $this->smarty->getTemplateVars('foo'),
            'captured',
            'Should have access to vars'
        );
    }
}
