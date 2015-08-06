<?php
/**
* Smarty PHPunit tests compilation of delimiter tags
*
* @package PHPunit
* @author Uwe Tews
*/

namespace Box\Brainy\Tests;


class CompileDelimiterTest extends Smarty_TestCase
{
    public function setUp() {
        $this->smarty = SmartyTests::$smarty;
        SmartyTests::init();
    }

    /**
    * test delimiter tag test
    */
    public function testLeftDelimiter() {
        $tpl = $this->smarty->createTemplate('eval:x{ldelim}x');
        $this->assertEquals('x{x', $this->smarty->fetch($tpl));
    }
    public function testRightDelimiter() {
        $tpl = $this->smarty->createTemplate('eval:x{rdelim}x');
        $this->assertEquals('x}x', $this->smarty->fetch($tpl));
    }
}
