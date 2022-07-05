<?php
/**
* Smarty PHPunit tests object variables
*
* @package PHPunit
* @author Uwe Tews
*/

namespace Box\Brainy\Tests;


class ObjectVariableTest extends Smarty_TestCase
{
    public function setup(): void {
        parent::setUp();
        $this->smarty->force_compile = true;
    }

    public function testObjectVariableOutput() {
        $object = new VariableObject;
        $tpl = $this->smarty->createTemplate('eval:{$object->hello}');
        $tpl->assign('object', $object);
        $this->assertEquals('hello_world', $this->smarty->fetch($tpl));
    }
    public function testObjectVariableOutputMethod() {
        $object = new VariableObject;
        $tpl = $this->smarty->createTemplate('eval:{$object->myhello()}');
        $tpl->assign('object', $object);
        $this->assertEquals('hello world', $this->smarty->fetch($tpl));
    }
}

Class VariableObject {
    public $hello = 'hello_world';

    public function myhello() {
        return 'hello world';
    }
}
