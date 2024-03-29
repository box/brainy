<?php
/**
* Smarty PHPunit tests clearing assigned variables
*
* @package PHPunit
* @author Uwe Tews
*/

namespace Box\Brainy\Tests;


class ClearAssignTest extends Smarty_TestCase
{
    public function setup(): void {
        parent::setUp();
        $this->smarty->safe_lookups = \Box\Brainy\Brainy::LOOKUP_SAFE;
        $this->smartyBC->safe_lookups = \Box\Brainy\Brainy::LOOKUP_SAFE;

        $this->smarty->assign('foo','foo');
        $this->smarty->assign('bar','bar');
        $this->smarty->assign('blar','blar');

        $this->smartyBC->assign('foo','foo');
        $this->smartyBC->assign('bar','bar');
        $this->smartyBC->assign('blar','blar');
    }

    /**
    * test all variables accessable
    */
    public function testAllVariablesAccessable() {
        $this->assertEquals('foobarblar', $this->smarty->fetch('eval:{$foo}{$bar}{$blar}'));
    }

    /**
    * test simple clear assign
    */
    public function testClearAssign() {
        $this->smarty->clearAssign('blar');
        $this->assertEquals('foobar', $this->smarty->fetch('eval:{$foo}{$bar}{$blar}'));
    }
    public function testSmarty2ClearAssign() {
        $this->smartyBC->clear_assign('blar');
        $this->assertEquals('foobar', $this->smartyBC->fetch('eval:{$foo}{$bar}{$blar}'));
    }
    /**
    * test clear assign array of variables
    */
    public function testArrayClearAssign() {
        $this->smarty->clearAssign(array('blar','foo'));
        $this->assertEquals('bar', $this->smarty->fetch('eval:{$foo}{$bar}{$blar}'));
    }
    public function testSmarty2ArrayClearAssign() {
        $this->smartyBC->clear_assign(array('blar','foo'));
        $this->assertEquals('bar', $this->smartyBC->fetch('eval:{$foo}{$bar}{$blar}'));
    }
}
