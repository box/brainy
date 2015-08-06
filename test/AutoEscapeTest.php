<?php
/**
* Smarty PHPunit tests for escape_html property
*
* @package PHPunit
* @author Uwe Tews
*/

namespace Box\Brainy\Tests;


class AutoEscapeTest extends Smarty_TestCase
{
    public function setUp() {
        parent::setUp();
        $this->smarty->escape_html = true;
    }

    public function testAutoEscape() {
        $tpl = $this->smarty->createTemplate('eval:{$foo}');
        $tpl->assign('foo','<a@b.c>');
        $this->assertEquals("&lt;a@b.c&gt;", $this->smarty->fetch($tpl));
    }
}
