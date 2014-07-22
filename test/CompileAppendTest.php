<?php
/**
* Smarty PHPunit tests compilation of append tags
*
* @package PHPunit
* @author Uwe Tews
*/

/**
* class for append tags tests
*/
class CompileAppendTest extends PHPUnit_Framework_TestCase
{
    public function setUp() {
        $this->smarty = SmartyTests::$smarty;
        SmartyTests::init();
    }

    /**
    * test aappand tag
    */
    public function testAppend1() {
        $this->assertEquals("12", $this->smarty->fetch('eval:{$foo=1}{append var=foo value=2}{foreach $foo as $bar}{$bar}{/foreach}'));
    }

    public function testAppend2() {
        $this->smarty->assign('foo', 1);
        $this->assertEquals("12", $this->smarty->fetch('eval:{append var=foo value=2}{foreach $foo as $bar}{$bar}{/foreach}'));
    }

    public function testAppendWithIndex() {
        $this->smarty->assign('foo', array(1, 2, 3));
        $this->assertEquals("1two3", $this->smarty->fetch('eval:{append var=foo value="two" index=1}{foreach $foo as $bar}{$bar}{/foreach}'));
    }
}
