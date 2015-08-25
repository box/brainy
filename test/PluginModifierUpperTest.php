<?php
/**
* Smarty PHPunit tests of modifier
*
* @package PHPunit
* @author Rodney Rehm
*/

namespace Box\Brainy\Tests;


class PluginModifierUpperTest extends Smarty_TestCase
{
    public function testDefault() {
        $result = "IF STRIKE ISN'T SETTLED QUICKLY IT MAY LAST A WHILE.";
        $tpl = $this->smarty->createTemplate('eval:{"If Strike isn\'t Settled Quickly it may Last a While."|upper}');
        $this->assertEquals($result, $this->smarty->fetch($tpl));
    }

    public function testUmlauts() {
        $result = "IF STRIKE ISN'T SÄTTLED ÜQUICKLY IT MAY LAST A WHILE.";
        $tpl = $this->smarty->createTemplate('eval:{"If Strike isn\'t Sättled ÜQuickly it may Last a While."|upper}');
        $this->assertEquals($result, $this->smarty->fetch($tpl));
    }

}
