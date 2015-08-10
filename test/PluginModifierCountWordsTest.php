<?php
/**
* Smarty PHPunit tests of modifier
*
* @package PHPunit
* @author Rodney Rehm
*/

namespace Box\Brainy\Tests;


class PluginModifierCountWordsTest extends Smarty_TestCase
{
    public function setUp() {
        $this->smarty = SmartyTests::$smarty;
        SmartyTests::init();
    }

    public function testDefault() {
        $result = "7";
        $tpl = $this->smarty->createTemplate('eval:{"Dealers Will Hear Car Talk at Noon."|count_words}');
        $this->assertEquals($result, $this->smarty->fetch($tpl));
    }

    public function testDashes() {
        $result = "7";
        $tpl = $this->smarty->createTemplate('eval:{"Smalltime-Dealers Will Hear Car Talk at Noon."|count_words}');
        $this->assertEquals($result, $this->smarty->fetch($tpl));
    }

    public function testUmlauts() {
        $result = "7";
        $tpl = $this->smarty->createTemplate('eval:{"Dealers Will Hear Cär Talk at Nöön."|count_words}');
        $this->assertEquals($result, $this->smarty->fetch($tpl));
    }

}
