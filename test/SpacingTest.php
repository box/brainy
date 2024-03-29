<?php
/**
* Smarty PHPunit tests spacing in template output
*
* @package PHPunit
* @author Uwe Tews
*/

namespace Box\Brainy\Tests;


class SpacingTest extends Smarty_TestCase
{
    public function setup(): void {
        parent::setUp();
        $this->smarty->assign('foo','bar');
    }

    /**
     * test variable output
     */
    public function testVariableSpacing1() {
        $tpl = $this->smarty->createTemplate("eval:{\$foo}", null, null, $this->smarty);
        $this->assertEquals("bar", $this->smarty->fetch($tpl));
    }
    public function testVariableSpacing2() {
        $tpl = $this->smarty->createTemplate("eval:{\$foo}{\$foo}", null, null, $this->smarty);
        $this->assertEquals("barbar", $this->smarty->fetch($tpl));
    }
    public function testVariableSpacing3() {
        $tpl = $this->smarty->createTemplate("eval:{\$foo} {\$foo}", null, null, $this->smarty);
        $this->assertEquals("bar bar", $this->smarty->fetch($tpl));
    }

    /**
     * test variable text combinations
     */
    public function testVariableText1() {
        $tpl = $this->smarty->createTemplate("eval:A{\$foo}B", null, null, $this->smarty);
        $this->assertEquals("AbarB", $this->smarty->fetch($tpl));
    }
    public function testVariableText2() {
        $tpl = $this->smarty->createTemplate("eval:A {\$foo}B", null, null, $this->smarty);
        $this->assertEquals("A barB", $this->smarty->fetch($tpl));
    }
    public function testVariableText3() {
        $tpl = $this->smarty->createTemplate("eval:A{\$foo} B", null, null, $this->smarty);
        $this->assertEquals("Abar B", $this->smarty->fetch($tpl));
    }
    public function testVariableText4() {
        $tpl = $this->smarty->createTemplate("eval:A{\$foo}\nB", null, null, $this->smarty);
        $this->assertEquals("Abar\nB", $this->smarty->fetch($tpl));
    }
    public function testVariableText5() {
        $tpl = $this->smarty->createTemplate("eval:A{\$foo}B\nC", null, null, $this->smarty);
        $this->assertEquals("AbarB\nC", $this->smarty->fetch($tpl));
    }

    /**
     * test tag text combinations
     */
    public function testTagText1() {
        $tpl = $this->smarty->createTemplate("eval:A{assign var=zoo value='blah'}B");
        $this->assertEquals("AB", $this->smarty->fetch($tpl));
    }
    public function testTagText2() {
        $tpl = $this->smarty->createTemplate("eval:A\n{assign var=zoo value='blah'}\nB");
        $this->assertEquals("A\n\nB", $this->smarty->fetch($tpl));
    }
    public function testTagText3() {
        $tpl = $this->smarty->createTemplate("eval:E{assign var=zoo value='blah'}\nF");
        $this->assertEquals("E\nF", $this->smarty->fetch($tpl));
    }
    public function testTagText4() {
        $tpl = $this->smarty->createTemplate("eval:G\n{assign var=zoo value='blah'}H");
        $this->assertEquals("G\nH", $this->smarty->fetch($tpl));
    }

}
