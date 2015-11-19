<?php
/**
 * Smarty PHPunit tests register->templateFunction / unregister->templateFunction methods
 *
 * @package PHPunit
 * @author Uwe Tews
 */

namespace Box\Brainy\Tests;


class RegisterFunctionTest extends Smarty_TestCase
{
    /**
     * test register->templateFunction method for function
     */
    public function testRegisterFunction() {
        $this->smarty->registerPlugin(\Box\Brainy\Brainy::PLUGIN_FUNCTION,'testfunction', '\Box\Brainy\Tests\RegisterFunctionTest::myfunction');
        $this->assertEquals('\Box\Brainy\Tests\RegisterFunctionTest::myfunction', $this->smarty->registered_plugins[\Box\Brainy\Brainy::PLUGIN_FUNCTION]['testfunction'][0]);
        $this->assertEquals('hello world 1', $this->smarty->fetch('eval:{testfunction value=1}'));
    }
    /**
     * test wrapper rfor egister_function method for function
     */
    public function testRegisterFunctionWrapper() {
        $this->smarty->registerPlugin(\Box\Brainy\Brainy::PLUGIN_FUNCTION,'testfunction', '\Box\Brainy\Tests\RegisterFunctionTest::myfunction');
        $this->assertEquals('\Box\Brainy\Tests\RegisterFunctionTest::myfunction', $this->smarty->registered_plugins[\Box\Brainy\Brainy::PLUGIN_FUNCTION]['testfunction'][0]);
        $this->assertEquals('hello world 1', $this->smarty->fetch('eval:{testfunction value=1}'));
    }
    public function testRegisterFunctionCaching1() {
        $this->smarty->caching = 1;
        $this->smarty->force_compile = true;
        $this->smarty->assign('x', 0);
        $this->smarty->assign('y', 10);
        $this->smarty->registerPlugin(\Box\Brainy\Brainy::PLUGIN_FUNCTION,'testfunction', '\Box\Brainy\Tests\RegisterFunctionTest::myfunction');
        $this->assertEquals('hello world 0 10', $this->smarty->fetch('test_register_function.tpl'));
    }
    public function testRegisterFunctionCaching3() {
        $this->smarty->caching = 1;
        $this->smarty->force_compile = true;
        $this->smarty->assign('x', 2);
        $this->smarty->assign('y', 30);
        $this->smarty->registerPlugin(\Box\Brainy\Brainy::PLUGIN_FUNCTION,'testfunction', '\Box\Brainy\Tests\RegisterFunctionTest::myfunction', false);
        $this->assertEquals('hello world 2 30', $this->smarty->fetch('test_register_function.tpl'));
    }
    /**
     * test unregister->templateFunction method
     */
    public function testUnregisterFunction() {
        $this->smarty->registerPlugin(\Box\Brainy\Brainy::PLUGIN_FUNCTION,'testfunction', '\Box\Brainy\Tests\RegisterFunctionTest::myfunction');
        $this->smarty->unregisterPlugin(\Box\Brainy\Brainy::PLUGIN_FUNCTION,'testfunction');
        $this->assertFalse(isset($this->smarty->registered_plugins[\Box\Brainy\Brainy::PLUGIN_FUNCTION]['testfunction']));
    }
    /**
     * test unregister->templateFunction method not registered
     */
    public function testUnregisterFunctionNotRegistered() {
        $this->smarty->unregisterPlugin(\Box\Brainy\Brainy::PLUGIN_FUNCTION,'testfunction');
        $this->assertFalse(isset($this->smarty->registered_plugins[\Box\Brainy\Brainy::PLUGIN_FUNCTION]['testfunction']));
    }


    public static function myfunction($params, &$smarty)
    {
        return "hello world $params[value]";
    }
}
