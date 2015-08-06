<?php
/**
* Smarty PHPunit tests of modifier
*
* @package PHPunit
* @author Rodney Rehm
*/

namespace Box\Brainy\Tests;


class PluginModifierLowerTest extends Smarty_TestCase
{
    public function setUp() {
        $this->smarty = SmartyTests::$smarty;
        SmartyTests::init();
    }

    public function testDefault() {
        $result = "two convicts evade noose, jury hung.";
        $tpl = $this->smarty->createTemplate('eval:{"Two Convicts Evade Noose, Jury Hung."|lower}');
        $this->assertEquals($result, $this->smarty->fetch($tpl));
    }

    public function testDefaultWithoutMbstring() {
        \Box\Brainy\Brainy::$_MBSTRING = false;
        $result = "two convicts evade noose, jury hung.";
        $tpl = $this->smarty->createTemplate('eval:{"Two Convicts Evade Noose, Jury Hung."|lower}');
        $this->assertEquals($result, $this->smarty->fetch($tpl));
        \Box\Brainy\Brainy::$_MBSTRING = true;
    }

    public function testUmlauts() {
        $result = "two convicts eväde nööse, jury hung.";
        $tpl = $this->smarty->createTemplate('eval:{"Two Convicts Eväde NöÖse, Jury Hung."|lower}');
        $this->assertEquals($result, $this->smarty->fetch($tpl));
    }

    public function testUmlautsWithoutMbstring() {
        \Box\Brainy\Brainy::$_MBSTRING = false;
        $result = "two convicts eväde nööse, jury hung.";
        $tpl = $this->smarty->createTemplate('eval:{"Two Convicts Eväde NöÖse, Jury Hung."|lower}');
        $this->assertNotEquals($result, $this->smarty->fetch($tpl));
        \Box\Brainy\Brainy::$_MBSTRING = true;
    }

}
