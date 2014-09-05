<?php
/**
 * Smarty PHPunit basic core function tests
 *
 * @package PHPunit
 * @author Uwe Tews
 */


class CoreTest extends PHPUnit_Framework_TestCase
{
    public function setUp() {
        $this->smarty = SmartyTests::$smarty;
        SmartyTests::init();
    }

    /**
    * loadPlugin test unkown plugin
    */
    public function testLoadPluginErrorReturn() {
        $this->assertFalse($this->smarty->loadPlugin('Smarty_Not_Known'));
    }
    /**
    * loadPlugin test $template_class exists
    */
    public function testLoadPluginSmartyTemplateClass() {
        $this->assertTrue($this->smarty->loadPlugin($this->smarty->template_class) == true);
    }
    /**
    * loadPlugin test loaging from plugins_dir
    */
    public function testLoadPluginSmartyPluginCounter() {
        $this->assertTrue($this->smarty->loadPlugin('smarty_function_counter') == true);
    }
}
