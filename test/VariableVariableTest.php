<?php
/**
* Smarty PHPunit tests variable variables
*
* @package PHPunit
* @author Uwe Tews
*/

namespace Box\Brainy\Tests;


class VariableVariableTest extends Smarty_TestCase
{
    public function testVariableVariableBrainyStyle() {
        $tpl = $this->smarty->createTemplate('eval:{$foo=\'bar\'}{$bar=123}{${$foo}}');
        $this->assertEquals('123', $this->smarty->fetch($tpl));
    }
}
