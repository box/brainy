<?php
/**
* Smarty PHPunit tests compilation of {setfilter} tag
*
* @package PHPunit
* @author Uwe Tews
*/

namespace Box\Brainy\Tests;


class CompileSetfilterTest extends Smarty_TestCase
{
    public function setUp() {
        $this->smarty = SmartyTests::$smarty;
        SmartyTests::init();
    }

    /**
    * test nested setfilter
    */
    public function testNestedSetfilter() {
        $tpl = $this->smarty->createTemplate('eval:{$foo}{setfilter htmlspecialchars} {$foo}{setfilter escape:"mail"} {$foo}{/setfilter} {$foo}{/setfilter} {$foo}');
        $tpl->assign('foo','<a@b.c>');
        $this->assertEquals("<a@b.c> &lt;a@b.c&gt; <a [AT] b [DOT] c> &lt;a@b.c&gt; <a@b.c>", $this->smarty->fetch($tpl));
    }
}
