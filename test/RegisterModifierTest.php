<?php
/**
 * Smarty PHPunit tests register->modifier / unregister->modifier methods
 *
 * @package PHPunit
 * @author Uwe Tews
 */

namespace Box\Brainy\Tests;


class RegisterModifierTest extends Smarty_TestCase
{

    public static function mymodifier($a, $b, $c)
    {
        return "$a function $b $c";
    }

    /**
     * test register->modifier method for function
     */
    public function testRegisterModifier() {
        $this->smarty->registerPlugin(\Box\Brainy\Brainy::PLUGIN_MODIFIER, 'testmodifier', '\Box\Brainy\Tests\RegisterModifierTest::mymodifier');
        $this->assertEquals('\Box\Brainy\Tests\RegisterModifierTest::mymodifier', $this->smarty->registered_plugins[\Box\Brainy\Brainy::PLUGIN_MODIFIER]['testmodifier']);
        $this->smarty->assign('foo', 'foo');
        $this->smarty->assign('bar', 'bar');
        $this->assertEquals('foo function blar bar', $this->smarty->fetch('eval:{$foo|testmodifier:blar:$bar}'));
    }
    /**
     * test unregister->modifier method
     */
    public function testUnregisterModifier() {
        $this->smarty->registerPlugin(\Box\Brainy\Brainy::PLUGIN_MODIFIER,'testmodifier', '\Box\Brainy\Tests\RegisterModifierTest::mymodifier');
        $this->smarty->unregisterPlugin(\Box\Brainy\Brainy::PLUGIN_MODIFIER,'testmodifier');
        $this->assertFalse(isset($this->smarty->registered_plugins[\Box\Brainy\Brainy::PLUGIN_MODIFIER]['testmodifier']));
    }
    /**
     * test unregister->modifier method not registered
     */
    public function testUnregisterModifierNotRegistered() {
        $this->smarty->unregisterPlugin(\Box\Brainy\Brainy::PLUGIN_MODIFIER,'testmodifier');
        $this->assertFalse(isset($this->smarty->registered_plugins[\Box\Brainy\Brainy::PLUGIN_MODIFIER]['testmodifier']));
    }
}
