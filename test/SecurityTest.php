<?php
/**
* Smarty PHPunit tests for security
*
* @package PHPunit
* @author Uwe Tews
*/

/**
* class for security test
*/
class SecurityTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->smarty = SmartyTests::$smarty;
        $this->smartyBC = SmartyTests::$smartyBC;
        SmartyTests::init();
        $this->smarty->force_compile = true;
        $this->smartyBC->force_compile = true;
        $this->smarty->clearCompiledTemplate();
        $this->smarty->clearAllCache();
    }

    static function isRunnable()
    {
        return true;
    }

    /**
    * test that security is loaded
    */
    public function testSecurityLoaded()
    {
        $this->assertTrue(is_object($this->smarty->security_policy));
    }

    /**
    * test trusted PHP function
    */
    public function testTrustedPHPFunction()
    {
        $this->assertEquals("5", $this->smarty->fetch('eval:{assign var=foo value=[1,2,3,4,5]}{count($foo)}'));
    }

    /**
    * test not trusted PHP function
    */
    public function testNotTrustedPHPFunction()
    {
        $this->smarty->security_policy->php_functions = array('null');
        try {
            $this->smarty->fetch('eval:{assign var=foo value=[1,2,3,4,5]}{count($foo)}');
        } catch (Exception $e) {
            $this->assertContains(htmlentities("PHP function 'count' not allowed by security setting"), $e->getMessage());

            return;
        }
        $this->fail('Exception for not trusted modifier has not been raised.');
    }

    /**
    * test not trusted PHP function at disabled security
    */
    public function testDisabledTrustedPHPFunction()
    {
        $this->smarty->security_policy->php_functions = array('null');
        $this->smarty->disableSecurity();
        $this->assertEquals("5", $this->smarty->fetch('eval:{assign var=foo value=[1,2,3,4,5]}{count($foo)}'));
    }

    /**
    * test trusted modifier
    */
    public function testTrustedModifier()
    {
        $this->assertEquals("5", $this->smarty->fetch('eval:{assign var=foo value=[1,2,3,4,5]}{$foo|@count}'));
    }

    /**
    * test not trusted modifier
    */
    public function testNotTrustedModifier()
    {
        $this->smarty->security_policy->php_modifiers = array('null');
        try {
            $this->smarty->fetch('eval:{assign var=foo value=[1,2,3,4,5]}{$foo|@count}');
        } catch (Exception $e) {
            $this->assertContains(htmlentities("modifier 'count' not allowed by security setting"), $e->getMessage());

            return;
        }
        $this->fail('Exception for not trusted modifier has not been raised.');
    }

    /**
    * test not trusted modifier at disabled security
    */
    public function testDisabledTrustedModifier()
    {
        $this->smarty->security_policy->php_modifiers = array('null');
        $this->smarty->disableSecurity();
        $this->assertEquals("5", $this->smarty->fetch('eval:{assign var=foo value=[1,2,3,4,5]}{$foo|@count}'));
    }

    /**
    * test allowed tags
    */
    public function testAllowedTags1()
    {
        $this->smarty->security_policy->allowed_tags = array('counter');
        $this->assertEquals("1", $this->smarty->fetch('eval:{counter start=1}'));
    }

    /**
    * test not allowed tag
    */
    public function testNotAllowedTags2()
    {
        $this->smarty->security_policy->allowed_tags = array('counter');
        try {
            $this->smarty->fetch('eval:{counter}{cycle values="1,2"}');
        } catch (Exception $e) {
            $this->assertContains(htmlentities("tag 'cycle' not allowed by security setting"), $e->getMessage());

            return;
        }
        $this->fail('Exception for not allowed tag has not been raised.');
    }

    /**
    * test disabled tag
    */
    public function testDisabledTags()
    {
        $this->smarty->security_policy->disabled_tags = array('cycle');
        try {
            $this->smarty->fetch('eval:{counter}{cycle values="1,2"}');
        } catch (Exception $e) {
            $this->assertContains(htmlentities("tag 'cycle' disabled by security setting"), $e->getMessage());

            return;
        }
        $this->fail('Exception for disabled tag has not been raised.');
    }

    /**
    * test allowed modifier
    */
    public function testAllowedModifier1()
    {
        $this->smarty->security_policy->allowed_modifiers = array('capitalize');
        $this->assertEquals("Hello World", $this->smarty->fetch('eval:{"hello world"|capitalize}'));
    }
    public function testAllowedModifier2()
    {
        $this->smarty->security_policy->allowed_modifiers = array('upper');
        $this->assertEquals("HELLO WORLD", $this->smarty->fetch('eval:{"hello world"|upper}'));
    }

    /**
    * test not allowed modifier
    */
    public function testNotAllowedModifier()
    {
        $this->smarty->security_policy->allowed_modifiers = array('upper');
        try {
            $this->smarty->fetch('eval:{"hello"|upper}{"world"|lower}');
        } catch (Exception $e) {
            $this->assertContains(htmlentities("modifier 'lower' not allowed by security setting"), $e->getMessage());

            return;
        }
        $this->fail('Exception for not allowed tag has not been raised.');
    }

    /**
    * test disabled modifier
    */
    public function testDisabledModifier()
    {
        $this->smarty->security_policy->disabled_modifiers = array('lower');
        try {
            $this->smarty->fetch('eval:{"hello"|upper}{"world"|lower}');
        } catch (Exception $e) {
            $this->assertContains(htmlentities("modifier 'lower' disabled by security setting"), $e->getMessage());

            return;
        }
        $this->fail('Exception for disabled tag has not been raised.');
    }

    /**
    * test standard directory
    */
    public function testStandardDirectory()
    {
        $content = $this->smarty->fetch('eval:{include file="helloworld.tpl"}');
        $this->assertEquals("hello world", $content);
    }

    /**
    * test trusted directory
    */
    public function testTrustedDirectory()
    {
        $this->smarty->security_policy->secure_dir = array('test' . DS . 'templates_2' . DS);
        $this->assertEquals("hello world", $this->smarty->fetch('eval:{include file="test/templates_2/hello.tpl"}'));
    }

    /**
    * test not trusted directory
    */
    public function testNotTrustedDirectory()
    {
        $this->smarty->security_policy->secure_dir = array('test' . DS . 'templates_3' . DS);
        try {
            $this->smarty->fetch('eval:{include file="test/templates_2/hello.tpl"}');
        } catch (Exception $e) {
            $this->assertContains("/test/templates_2/hello.tpl' not allowed by security setting", str_replace('\\','/',$e->getMessage()));

            return;
        }
        $this->fail('Exception for not trusted directory has not been raised.');
    }

    /**
    * test disabled security for not trusted dir
    */
    public function testDisabledTrustedDirectory()
    {
        $this->smarty->disableSecurity();
        $this->assertEquals("hello world", $this->smarty->fetch('eval:{include file="test/templates_2/hello.tpl"}'));
    }

        /**
    * test trusted static class
    */
    public function testTrustedStaticClass()
    {
        $this->smarty->security_policy->static_classes = array('mysecuritystaticclass');
        $tpl = $this->smarty->createTemplate('eval:{mysecuritystaticclass::square(5)}');
        $this->assertEquals('25', $this->smarty->fetch($tpl));
    }

    /**
    * test not trusted PHP function
    */
    public function testNotTrustedStaticClass()
    {
        $this->smarty->security_policy->static_classes = array('null');
        try {
            $this->smarty->fetch('eval:{mysecuritystaticclass::square(5)}');
        } catch (Exception $e) {
            $this->assertContains(htmlentities("access to static class 'mysecuritystaticclass' not allowed by security setting"), $e->getMessage());

            return;
        }
        $this->fail('Exception for not trusted static class has not been raised.');
    }

    public function testChangedTrustedDirectory()
    {
        $this->smarty->security_policy->secure_dir = array(
            'test' . DS . 'templates_2' . DS,
        );
        $this->assertEquals("hello world", $this->smarty->fetch('eval:{include file="test/templates_2/hello.tpl"}'));

        $this->smarty->security_policy->secure_dir = array(
            'test' . DS . 'templates_2' . DS,
            'test' . DS . 'templates_3' . DS,
        );
        $this->assertEquals("templates_3", $this->smarty->fetch('eval:{include file="test/templates_3/dirname.tpl"}'));
    }

}

class mysecuritystaticclass
{
    const STATIC_CONSTANT_VALUE = 3;
    static $static_var = 5;

    static function square($i)
    {
        return $i*$i;
    }
}
