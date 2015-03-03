<?php
/**
* Smarty PHPunit tests assign method
*
* @package PHPunit
* @author Uwe Tews
*/

/**
* class for assign tests
*/
class AssignTest extends PHPUnit_Framework_TestCase
{
    public function setUp() {
        $this->smarty = SmartyTests::$smarty;
        SmartyTests::init();
    }

    /**
    * test simple assign
    */
    public function testSimpleAssign() {
            $this->smarty->assign('foo','bar');
        $this->assertEquals('bar', $this->smarty->fetch('eval:{$foo}'));
    }
    /**
    * test assign array of variables
    */
    public function testArrayAssign() {
            $this->smarty->assign(array('foo'=>'bar','foo2'=>'bar2'));
        $this->assertEquals('bar bar2', $this->smarty->fetch('eval:{$foo} {$foo2}'));
    }
}
