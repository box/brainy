<?php
/**
* Smarty PHPunit tests array definitions and access
*
* @package PHPunit
* @author Uwe Tews
*/

namespace Box\Brainy\Tests;


class ArrayTest extends Smarty_TestCase
{
    public function setUp() {
        $this->smarty = SmartyTests::$smarty;
        SmartyTests::init();
    }

    /**
    * test simple array definition
    */
    public function testSimpleArrayDefinition() {
        $this->assertEquals('12345', $this->smarty->fetch('eval:{$foo=[1,2,3,4,5]}{foreach $foo as $bar}{$bar}{/foreach}'));
    }
    /**
    * test smarty2 array access
    */
    public function testSmarty2ArrayAccess() {
        $this->assertEquals('123', $this->smarty->fetch('eval:{$foo=[1,2,3,4, 5]}{$foo.0}{$foo.1}{$foo.2}'));
    }
    /**
    * test smarty3 array access
    */
    public function testSmarty3ArrayAccess() {
        $this->assertEquals('123', $this->smarty->fetch('eval:{$foo=[1,2,3,4, 5]}{$foo[0]}{$foo[1]}{$foo[2]}'));
    }
    /**
    * test indexed array definition
    */
    public function testIndexedArrayDefinition() {
        $this->assertEquals('1234', $this->smarty->fetch('eval:{$x=\'d\'}{$foo=[a=>1,\'b\'=>2,"c"=>3,$x=>4]}{$foo[\'a\']}{$foo[\'b\']}{$foo[\'c\']}{$foo[\'d\']}'));
    }
    /**
    * test nested array
    */
    public function testNestedArray() {
        $this->assertEquals('b', $this->smarty->fetch('eval:{$foo=[1,2,[a,b,c],4, 5]}{$foo[2][1]}'));
    }
    /**
    * test array math
    */
    public function testArrayMath1() {
        $this->assertEquals('9', $this->smarty->fetch('eval:{$foo=[1,2,[7,8,9],4, 5]}{$foo[2][1]+1}'));
    }
    public function testArrayMath2() {
        $this->assertEquals('9', $this->smarty->fetch('eval:{$foo=[1,2,[7,8,9],4, 5]}{$foo.2.1+1}'));
    }
    public function testArrayMath3() {
        $this->assertEquals('10', $this->smarty->fetch('eval:{$foo=[1,2,[7,8,9],4, 5]}{2+$foo[2][1]}'));
    }
    public function testArrayMath4() {
        $this->assertEquals('10', $this->smarty->fetch('eval:{$foo=[1,2,[7,8,9],4, 5]}{2+$foo.2.1}'));
    }
    public function testArrayMath5() {
        $this->assertEquals('15', $this->smarty->fetch('eval:{$foo=[1,2,[7,8,9],4, 5]}{$foo[2][0]+$foo[2][1]}'));
    }
    public function testArrayMath6() {
        $this->assertEquals('15', $this->smarty->fetch('eval:{$foo=[1,2,[7,8,9],4, 5]}{$foo.2.0+$foo.2.1}'));
    }
    public function testArrayVariableIndex1() {
        $this->assertEquals('7', $this->smarty->fetch('eval:{$foo=[1,2,[7,8,9],4, 5]}{$x=2}{$y=0}{$foo.$x.$y}'));
    }
    public function testArrayVariableIndex2() {
        $this->assertEquals('7', $this->smarty->fetch('eval:{$foo=[1,2,[7,8,9],4, 5]}{$x=2}{$foo.$x.0}'));
    }
    public function testArrayVariableIndex3() {
        $this->assertEquals('7', $this->smarty->fetch('eval:{$foo=[1,2,[7,8,9],4, 5]}{$x=0}{$foo.2.$x}'));
    }
    public function testArrayVariableIndex4() {
        $this->assertEquals('7', $this->smarty->fetch('eval:{$foo=[1,2,[7,8,9],4,5]}{$x=[1, 0]}{$foo.2.{$x.1}}'));
    }
}
