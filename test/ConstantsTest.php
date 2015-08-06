<?php
/**
* Smarty PHPunit tests of constants
*
* @package PHPunit
* @author Uwe Tews
*/

namespace Box\Brainy\Tests;


class ConstantsTest extends Smarty_TestCase
{
    public function setUp() {
        $this->smarty = SmartyTests::$smarty;
        SmartyTests::init();
    }

    /**
    * test constants
    */
    public function testConstants() {
        define('MYCONSTANTS','hello world');
        $tpl = $this->smarty->createTemplate('eval:{$smarty.const.MYCONSTANTS}');
        $this->assertEquals("hello world", $this->smarty->fetch($tpl));
    }

}
