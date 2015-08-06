<?php
/**
* Smarty PHPunit tests of modifier
*
* @package PHPunit
* @author Rodney Rehm
*/

namespace Box\Brainy\Tests;


class PluginModifierCountCharactersTest extends Smarty_TestCase
{
    public function setUp() {
        $this->smarty = SmartyTests::$smarty;
        SmartyTests::init();
    }

    public function testDefault() {
        $result = "29";
        $tpl = $this->smarty->createTemplate('eval:{"Cold Wave Linked to Temperatures."|count_characters}');
        $this->assertEquals($result, $this->smarty->fetch($tpl));
    }

    public function testDefaultWithoutMbstring() {
        \Box\Brainy\Brainy::$_MBSTRING = false;
        $result = "29";
        $tpl = $this->smarty->createTemplate('eval:{"Cold Wave Linked to Temperatures."|count_characters}');
        $this->assertEquals($result, $this->smarty->fetch($tpl));
        \Box\Brainy\Brainy::$_MBSTRING = true;
    }

    public function testSpaces() {
        $result = "33";
        $tpl = $this->smarty->createTemplate('eval:{"Cold Wave Linked to Temperatures."|count_characters:true}');
        $this->assertEquals($result, $this->smarty->fetch($tpl));
    }

    public function testSpacesWithoutMbstring() {
        \Box\Brainy\Brainy::$_MBSTRING = false;
        $result = "33";
        $tpl = $this->smarty->createTemplate('eval:{"Cold Wave Linked to Temperatures."|count_characters:true}');
        $this->assertEquals($result, $this->smarty->fetch($tpl));
        \Box\Brainy\Brainy::$_MBSTRING = true;
    }

    public function testUmlauts() {
        $result = "29";
        $tpl = $this->smarty->createTemplate('eval:{"Cold Wäve Linked tö Temperatures."|count_characters}');
        $this->assertEquals($result, $this->smarty->fetch($tpl));
    }

    public function testUmlautsWithoutMbstring() {
        \Box\Brainy\Brainy::$_MBSTRING = false;
        $result = "29";
        $tpl = $this->smarty->createTemplate('eval:{"Cold Wäve Linked tö Temperatures."|count_characters}');
        $this->assertEquals($result, $this->smarty->fetch($tpl));
        \Box\Brainy\Brainy::$_MBSTRING = true;
    }

    public function testUmlautsSpaces() {
        $result = "33";
        $tpl = $this->smarty->createTemplate('eval:{"Cold Wäve Linked tö Temperatures."|count_characters:true}');
        $this->assertEquals($result, $this->smarty->fetch($tpl));
    }

    public function testUmlautsSpacesWithoutMbstring() {
        \Box\Brainy\Brainy::$_MBSTRING = false;
        $result = "33";
        $tpl = $this->smarty->createTemplate('eval:{"Cold Wäve Linked tö Temperatures."|count_characters:true}');
        $this->assertNotEquals($result, $this->smarty->fetch($tpl));
        \Box\Brainy\Brainy::$_MBSTRING = true;
    }
}
