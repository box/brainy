<?php
/**
* Smarty PHPunit tests compilation of {while} tag
*
* @package PHPunit
* @author Uwe Tews
*/

namespace Box\Brainy\Tests;


class CompileWhileTest extends Smarty_TestCase
{
    /**
    * test {while 'condition'} tag
    */
    public function testWhileCondition() {
        $tpl = $this->smarty->createTemplate('eval:{$x=0}{while $x<10}{$x}{$x=$x+1}{/while}');
        $this->assertEquals("0123456789", $this->smarty->fetch($tpl));
    }

    /**
    * test {while 'statement'} tag
    */
    public function testWhileStatement() {
        $tpl = $this->smarty->createTemplate('eval:{$y=4}{while $y}{$y}{$y=$y-1}{/while}');
        $this->assertEquals("4321", $this->smarty->fetch($tpl));
    }
}
