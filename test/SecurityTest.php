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

    /**
     * @expectedException \Box\Brainy\Exceptions\SmartyCompilerException
     * @expectedExceptionMessage PHP function 'count' not allowed by security setting
     */
    public function testNotTrustedPHPFunction() {
        $this->smarty->security_policy->php_functions = array('null');
        $this->smarty->display('eval:{assign var=foo value=[1,2,3,4, 5]}{count($foo)}');
    }

    /**
    * test trusted modifier
    */
    public function testTrustedModifier() {
        $this->assertEquals("5", $this->smarty->fetch('eval:{assign var=foo value=[1,2,3,4, 5]}{$foo|count}'));
    }

    /**
     * @expectedException \Box\Brainy\Exceptions\SmartyCompilerException
     * @expectedExceptionMessage modifier 'count' not allowed by security setting
     */
    public function testNotTrustedModifier() {
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
     * @expectedException \Exception
     * @expectedExceptionMessage 'cycle' not allowed
     */
    public function testNotAllowedTags2() {
        $this->smarty->security_policy->allowed_tags = array('counter');
        $this->smarty->display('eval:{counter}{cycle values="1, 2"}');
    }

    /**
     * @expectedException \Box\Brainy\Exceptions\SmartyCompilerException
     * @expectedExceptionMessage tag 'cycle' disabled by security setting
     */
    public function testDisabledTags() {
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

    /**
     * @expectedException \Box\Brainy\Exceptions\SmartyCompilerException
     * @expectedExceptionMessage modifier 'lower' not allowed by security setting
     */
    public function testNotAllowedModifier() {
        $this->smarty->security_policy->allowed_modifiers = array('upper');
        $this->smarty->display('eval:{"hello"|upper}{"world"|lower}');
    }

    /**
     * @expectedException \Box\Brainy\Exceptions\SmartyCompilerException
     * @expectedExceptionMessage modifier 'lower' disabled by security setting
     */
    public function testDisabledModifier() {
        $this->smarty->security_policy->disabled_modifiers = array('lower');
        $this->smarty->display('eval:{"hello"|upper}{"world"|lower}');
    }

}
