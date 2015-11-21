<?php
/**
* Smarty PHPunit tests assignGlobal method  and {assignGlobal} tag
*
* @package PHPunit
* @author Uwe Tews
*/

namespace Box\Brainy\Tests;


class AssignGlobalTest extends Smarty_TestCase
{
    public function testAssignGlobalGetGlobal() {
        $this->smarty->assignGlobal('foo', 'bar');
        $this->assertEquals('bar', $this->smarty->getGlobal('foo'));
    }

    public function testAssignGlobalGetGlobalArray() {
        $this->smarty->assignGlobal('foo', array('foo' => 'bar', 'foo2' => 'bar2'));
        $a1 = array('foo' => array('foo' => 'bar', 'foo2' => 'bar2'));
        $a2 = $this->smarty->getGlobal();
        $this->assertTrue($a1 === $a2);
    }

    public function testAssignGlobalTag() {
        $this->smarty->assignGlobal('foo', 'bar');
        $this->assertEquals('bar', $this->smarty->fetch('eval:{$foo}'));
        $this->assertEquals('buh', $this->smarty->fetch('eval:{assign var=foo value=buh scope=global}{$foo}'));
        $this->assertEquals('buh', $this->smarty->fetch('eval:{$foo}'));
        $this->assertEquals('buh', $this->smarty->getGlobal('foo'));
    }

    public function testGlobalVarArrayTag() {
        $this->smarty->assignGlobal('foo', array('foo' => 'bar', 'foo2' => 'bar2'));
        $this->assertEquals('bar2', $this->smarty->fetch('eval:{$foo.foo2}'));
        $this->assertEquals('bar', $this->smarty->fetch('eval:{$foo.foo}'));
    }
}
