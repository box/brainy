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
    public function testDefault() {
        $result = "two convicts evade noose, jury hung.";
        $tpl = $this->smarty->createTemplate('eval:{"Two Convicts Evade Noose, Jury Hung."|lower}');
        $this->assertEquals($result, $this->smarty->fetch($tpl));
    }

    public function testUmlauts() {
        $result = "two convicts eväde nööse, jury hung.";
        $tpl = $this->smarty->createTemplate('eval:{"Two Convicts Eväde NöÖse, Jury Hung."|lower}');
        $this->assertEquals($result, $this->smarty->fetch($tpl));
    }

}
