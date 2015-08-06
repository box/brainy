<?php
/**
* Brainy default_assign_scope test suite
*
* @package PHPunit
* @author Matt Basta
*/

namespace Box\Brainy\Tests;


class DefaultAssignScopeTest extends Smarty_TestCase
{
    public function setUp() {
        $this->smarty = SmartyTests::$smarty;
        SmartyTests::init();
    }
    public function tearDown() {
        \Box\Brainy\Brainy::$default_assign_scope = \Box\Brainy\Brainy::SCOPE_LOCAL;
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
        \Box\Brainy\Brainy::$default_assign_scope = \Box\Brainy\Brainy::SCOPE_ROOT;
        $this->smarty->fetch('eval:{assign var="foo" value="bar"}');
        $this->assertEquals(
            $this->smarty->getTemplateVars('foo'),
            'bar',
            'Should have access to vars'
        );
    }

    public function testGlobalScopeGivesAccessToVars() {
        \Box\Brainy\Brainy::$default_assign_scope = \Box\Brainy\Brainy::SCOPE_GLOBAL;
        $this->smarty->fetch('eval:{assign var="foo" value="bar"}');
        $this->assertEquals(
            $this->smarty->getTemplateVars('foo'),
            'bar',
            'Should have access to vars'
        );
    }

    public function testDefaultScopeCanBeOverridden() {
        \Box\Brainy\Brainy::$default_assign_scope = \Box\Brainy\Brainy::SCOPE_GLOBAL;
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
        \Box\Brainy\Brainy::$default_assign_scope = \Box\Brainy\Brainy::SCOPE_ROOT;
        $this->smarty->fetch('eval:{capture assign="foo"}captured{/capture}');
        $this->assertEquals(
            $this->smarty->getTemplateVars('foo'),
            'captured',
            'Should have access to vars'
        );
    }
}
