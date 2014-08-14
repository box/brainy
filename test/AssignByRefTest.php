<?php
/**
* Smarty PHPunit tests assignByRef methode
*
* @package PHPunit
* @author Uwe Tews
*/

/**
* class for assignByRef tests
*/
class AssignByRefTest extends PHPUnit_Framework_TestCase
{
    public function setUp() {
        $this->smarty = SmartyTests::$smarty;
        $this->smartyBC = SmartyTests::$smartyBC;
        SmartyTests::init();
        Smarty::$assignment_compat = Smarty::ASSIGN_COMPAT;
    }

    /**
    * test simple assignByRef
    */
    public function testSimpleAssignByRef() {
        $bar = 'bar';
        $this->smarty->assignByRef('foo', $bar);
        $bar = 'newbar';
        $this->assertEquals('newbar', $this->smarty->fetch('eval:{$foo}'));
    }
    /**
    * test Smarty2 assign_By_Ref
    */
    public function testSmarty2AssignByRef() {
        $bar = 'bar';
        $this->smartyBC->assign_by_ref('foo', $bar);
        $bar = 'newbar';
        $this->assertEquals('newbar', $this->smartyBC->fetch('eval:{$foo}'));
    }
    /**
    * test Smarty2's behaviour of assign_By_Ref (Issue 88)
    */
    public function testSmarty2AssignByRef2() {
        $bar = 'bar';
        $this->smartyBC->assign_by_ref('foo', $bar);
        $this->smartyBC->fetch('eval:{$foo = "newbar"}');
        $this->assertEquals('newbar', $bar);
    }

    public function testSmarty2AssignByRefCancelled() {
        Smarty::$assignment_compat = Smarty::ASSIGN_NO_COMPAT;
        $bar = 'bar';
        $this->smartyBC->assign_by_ref('foo', $bar);
        $this->smartyBC->fetch('eval:{$foo = "newbar"}');
        $this->assertEquals('bar', $bar);
    }
}
