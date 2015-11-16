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
    public function setUp()
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
    * test not trusted PHP function
    */
    public function testNotTrustedPHPFunction() {
        $this->smarty->security_policy->php_functions = array('null');
        try {
            $this->smarty->fetch('eval:{assign var=foo value=[1,2,3,4, 5]}{count($foo)}');
        } catch (Exception $e) {
            $this->assertContains(htmlentities("PHP function 'count' not allowed by security setting"), $e->getMessage());

            return;
        }
        $this->fail('Exception for not trusted modifier has not been raised.');
    }

    /**
    * test trusted modifier
    */
    public function testTrustedModifier() {
        $this->assertEquals("5", $this->smarty->fetch('eval:{assign var=foo value=[1,2,3,4, 5]}{$foo|@count}'));
    }

    /**
    * test not trusted modifier
    */
    public function testNotTrustedModifier() {
        $this->smarty->security_policy->php_modifiers = array('null');
        try {
            $this->smarty->fetch('eval:{assign var=foo value=[1,2,3,4, 5]}{$foo|count}');
        } catch (\Exception $e) {
            $this->assertContains(htmlentities("modifier 'count' not allowed by security setting"), $e->getMessage());

            return;
        }
        $this->fail('Exception for not trusted modifier has not been raised.');
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
        $this->smarty->fetch('eval:{counter}{cycle values="1, 2"}');
    }

    /**
    * test disabled tag
    */
    public function testDisabledTags() {
        $this->smarty->security_policy->disabled_tags = array('cycle');
        try {
            $this->smarty->fetch('eval:{counter}{cycle values="1, 2"}');
        } catch (\Exception $e) {
            $this->assertContains(htmlentities("tag 'cycle' disabled by security setting"), $e->getMessage());

            return;
        }
        $this->fail('Exception for disabled tag has not been raised.');
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
    * test not allowed modifier
    */
    public function testNotAllowedModifier() {
        $this->smarty->security_policy->allowed_modifiers = array('upper');
        try {
            $this->smarty->fetch('eval:{"hello"|upper}{"world"|lower}');
        } catch (\Exception $e) {
            $this->assertContains(htmlentities("modifier 'lower' not allowed by security setting"), $e->getMessage());

            return;
        }
        $this->fail('Exception for not allowed tag has not been raised.');
    }

    /**
    * test disabled modifier
    */
    public function testDisabledModifier() {
        $this->smarty->security_policy->disabled_modifiers = array('lower');
        try {
            $this->smarty->fetch('eval:{"hello"|upper}{"world"|lower}');
        } catch (\Exception $e) {
            $this->assertContains(htmlentities("modifier 'lower' disabled by security setting"), $e->getMessage());

            return;
        }
        $this->fail('Exception for disabled tag has not been raised.');
    }

}
