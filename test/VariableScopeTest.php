<?php
/**
* Smarty PHPunit tests variable scope
*
* @package PHPunit
* @author Uwe Tews
*/

namespace Box\Brainy\Tests;


class VariableScopeTest extends Smarty_TestCase
{
    public function setUp() {
        parent::setUp();
        $this->smarty->safe_lookups = \Box\Brainy\Brainy::LOOKUP_SAFE;
        $this->smarty->assign('foo', 'bar');
    }

    /**
    * test root variable
    */
    public function testVariableScope1() {
        $tpl = $this->smarty->createTemplate("eval:{\$foo}", null, $this->smarty);
        $this->assertEquals("bar", $this->smarty->fetch($tpl));
    }
    public function testVariableScope12() {
        $tpl = $this->smarty->createTemplate("eval:{\$foo}", null, $this->smarty);
        $this->assertEquals("bar", $this->smarty->fetch($tpl));
    }
    public function testVariableScope13() {
        $tpl = $this->smarty->createTemplate("eval:{\$foo}", null, $this->smarty);
        $this->assertEquals("bar", $tpl->fetch());
    }

    /**
    * test root variable with data object chain
    */
    public function testVariableScope2() {
        $data1 = new Helpers\Data($this->smarty);
        $data2 = new Helpers\Data($data1);
        $tpl = $this->smarty->createTemplate("eval:{\$foo}", null, $data2);
        $this->assertEquals("bar", $this->smarty->fetch($tpl));
    }
    public function testVariableScope22() {
        $data1 = new Helpers\Data($this->smarty);
        $data2 = new Helpers\Data($data1);
        $tpl = $this->smarty->createTemplate("eval:{\$foo}", null, $data2);
        $this->assertEquals("bar", $this->smarty->fetch($tpl));
    }
    public function testVariableScope23() {
        $data1 = new Helpers\Data($this->smarty);
        $data2 = new Helpers\Data($data1);
        $tpl = $this->smarty->createTemplate("eval:{\$foo}", null, $data2);
        $this->assertEquals("bar", $tpl->fetch());
    }

    /**
    * test local variable not seen global
    */
    public function testVariableScope4() {
         $this->smarty->error_reporting  = error_reporting() & ~(E_NOTICE|E_USER_NOTICE);
        $tpl = $this->smarty->createTemplate("eval:{\$foo2='localvar'}{\$foo2}", null, $this->smarty);
        // must see local value
        $this->assertEquals("localvar", $this->smarty->fetch($tpl));
        // must see $foo2
        $tpl2 = $this->smarty->createTemplate("eval:{\$foo2}", null, $this->smarty);
        $this->assertEquals("", $this->smarty->fetch($tpl2));
    }
    public function testVariableScope42() {
         $this->smarty->error_reporting  = error_reporting() & ~(E_NOTICE|E_USER_NOTICE);
        $tpl = $this->smarty->createTemplate("eval:{\$foo2='localvar'}{\$foo2}", null, $this->smarty);
        // must see local value
        $this->assertEquals("localvar", $this->smarty->fetch($tpl));
        // must see $foo2
        $tpl2 = $this->smarty->createTemplate("eval:{\$foo2}", null, $this->smarty);
        $this->assertEquals("", $this->smarty->fetch($tpl2));
    }

    /**
    * test overwriting by global variable
    */
    public function testVariableScope5() {
        // create variable $foo2
        $this->smarty->assign('foo2','oldvalue');
        $tpl = $this->smarty->createTemplate("eval:{assign var=foo2 value='newvalue' scope=parent}{\$foo2}", null, $this->smarty);
        // must see the new value
        $this->assertEquals("newvalue", $this->smarty->fetch($tpl));
        $tpl2 = $this->smarty->createTemplate("eval:{\$foo2}", null, $this->smarty);
        // must see the new value at root
        $this->assertEquals("newvalue", $this->smarty->fetch($tpl2));
    }
    public function testVariableScope52() {
        // create variable $foo2
        $this->smarty->assign('foo2','oldvalue');
        $tpl = $this->smarty->createTemplate("eval:{assign var=foo2 value='newvalue' scope=parent}{\$foo2}", null, $this->smarty);
        // must see the new value
        $this->assertEquals("newvalue", $this->smarty->fetch($tpl));
        $tpl2 = $this->smarty->createTemplate("eval:{\$foo2}", null, $this->smarty);
        // must see the new value at root
        $this->assertEquals("newvalue", $this->smarty->fetch($tpl2));
    }

    /**
    * test creation of global variable in outerscope
    */
    public function testVariableScope6() {
        // create global variable $foo2 in template
        $tpl = $this->smarty->createTemplate("eval:{assign var=foo2 value='newvalue' scope=parent}{\$foo2}", null, $this->smarty);
        // must see the new value
        $this->assertEquals("newvalue", $this->smarty->fetch($tpl));
        $tpl2 = $this->smarty->createTemplate("eval:{\$foo2}", null, $this->smarty);
        // must see the new value at root
        $this->assertEquals("newvalue", $this->smarty->fetch($tpl2));
    }
    public function testVariableScope62() {
        // create global variable $foo2 in template
        $tpl = $this->smarty->createTemplate("eval:{assign var=foo2 value='newvalue' scope=parent}{\$foo2}", null, $this->smarty);
        // must see the new value
        $this->assertEquals("newvalue", $this->smarty->fetch($tpl));
        $tpl2 = $this->smarty->createTemplate("eval:{\$foo2}", null, $this->smarty);
        // must see the new value at root
        $this->assertEquals("newvalue", $this->smarty->fetch($tpl2));
    }
    public function testDataArray() {
        // create global variable $foo2 in template
        $tpl = $this->smarty->createTemplate("eval:{\$foo} {\$foo2}", null, array('foo'=>'bar','foo2'=>'bar2'));
        $this->assertEquals("bar bar2", $this->smarty->fetch($tpl));
    }

    public function testAssigns() {
        $expected = " local  local  local  parent root global parent root global parent root global";
        $result = $this->smarty->fetch('assign.tpl');
        $this->assertEquals($expected, $result);
    }
}
