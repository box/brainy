<?php
/**
* Smarty PHPunit tests for security
*
* @package PHPunit
* @author Uwe Tews
*/

namespace Box\Brainy\Tests;


class SecurityTest extends Smarty_TestCase
{
    public function setup(): void
    {
        parent::setUp();
        $this->smarty->force_compile = true;
        $this->smartyBC->force_compile = true;
    }

    /**
    * test that security is loaded
    */
    public function testSecurityLoaded() {
        $this->assertTrue(is_object($this->smarty->security_policy));
    }

    /**
    * test trusted PHP function
    */
    public function testTrustedPHPFunction() {
        $this->assertEquals("5", $this->smarty->fetch('eval:{assign var=foo value=[1,2,3,4, 5]}{count($foo)}'));
    }

    public function testNotTrustedPHPFunction()
    {
        $this->expectException(\Box\Brainy\Exceptions\SmartyCompilerException::class);
        $this->expectExceptionMessage("PHP function 'count' not allowed by security setting");
        $this->smarty->security_policy->php_functions = array('null');
        $this->smarty->display('eval:{assign var=foo value=[1,2,3,4, 5]}{count($foo)}');
    }

    /**
    * test trusted modifier
    */
    public function testTrustedModifier() {
        $this->assertEquals("5", $this->smarty->fetch('eval:{assign var=foo value=[1,2,3,4, 5]}{$foo|count}'));
    }

    public function testNotTrustedModifier()
    {
        $this->expectException(\Box\Brainy\Exceptions\SmartyCompilerException::class);
        $this->expectExceptionMessage("modifier 'count' not allowed by security setting");
        $this->smarty->security_policy->php_modifiers = array('null');
        $this->smarty->display('eval:{assign var=foo value=[1,2,3,4, 5]}{$foo|count}');
    }

    /**
    * test allowed tags
    */
    public function testAllowedTags1() {
        $this->smarty->security_policy->allowed_tags = array('counter');
        $this->assertEquals("1", $this->smarty->fetch('eval:{counter start=1}'));
    }

    /**
     * test not allowed tag
     */
    public function testNotAllowedTags2()
    {
        $this->expectException(\Box\Brainy\Exceptions\SmartyCompilerException::class);
        $this->expectExceptionMessage("'cycle' not allowed");
        $this->smarty->security_policy->allowed_tags = array('counter');
        $this->smarty->display('eval:{counter}{cycle values="1, 2"}');
    }

    public function testDisabledTags()
    {
        $this->expectException(\Box\Brainy\Exceptions\SmartyCompilerException::class);
        $this->expectExceptionMessage("tag 'cycle' disabled by security setting");
        $this->smarty->security_policy->disabled_tags = array('cycle');
        $this->smarty->display('eval:{counter}{cycle values="1, 2"}');
    }

    /**
    * test allowed modifier
    */
    public function testAllowedModifier1() {
        $this->smarty->security_policy->allowed_modifiers = array('capitalize');
        $this->assertEquals("Hello World", $this->smarty->fetch('eval:{"hello world"|capitalize}'));
    }
    public function testAllowedModifier2() {
        $this->smarty->security_policy->allowed_modifiers = array('upper');
        $this->assertEquals("HELLO WORLD", $this->smarty->fetch('eval:{"hello world"|upper}'));
    }

    public function testNotAllowedModifier()
    {
        $this->expectException(\Box\Brainy\Exceptions\SmartyCompilerException::class);
        $this->expectExceptionMessage("modifier 'lower' not allowed by security setting");
        $this->smarty->security_policy->allowed_modifiers = array('upper');
        $this->smarty->display('eval:{"hello"|upper}{"world"|lower}');
    }

    public function testDisabledModifier()
    {
        $this->expectException(\Box\Brainy\Exceptions\SmartyCompilerException::class);
        $this->expectExceptionMessage("modifier 'lower' disabled by security setting");
        $this->smarty->security_policy->disabled_modifiers = array('lower');
        $this->smarty->display('eval:{"hello"|upper}{"world"|lower}');
    }

}
