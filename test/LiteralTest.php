<?php
/**
 * @package PHPunit
 * @author Uwe Tews
 */

namespace Box\Brainy\Tests;


class LiteralTest extends Smarty_TestCase
{
    public function testLiteralTag() {
        $tpl = $this->smarty->createTemplate("eval:{literal} {\$foo} {/literal}");
        $this->assertEquals(' {$foo} ', $this->smarty->fetch($tpl));
    }

    public function testLiteralTagWithDoubleBackslashes() {
        $tpl = $this->smarty->createTemplate("eval:{literal} \\\\ {/literal}"); // \\\\ -> \\
        $this->assertEquals(' \\\\ ', $this->smarty->fetch($tpl));
    }

    public function testBlockInLiteralTag() {
        $tpl = $this->smarty->createTemplate("eval:{literal} {block \"foo\"}{\$foo}{/block} {/literal}");
        $this->assertEquals(' {block "foo"}{$foo}{/block} ', $this->smarty->fetch($tpl));
    }

    /*
    *  Test auto literal space
    */
    public function testAutoLiteralSpace() {
        $tpl = $this->smarty->createTemplate("eval: { \$foo} ");
        $tpl->assign('foo', 'literal');
        $this->assertEquals(' { $foo} ', $this->smarty->fetch($tpl));
    }

    /*
    *  Test auto literal line break
    */
    public function testAutoLiteralLineBreak() {
        $tpl = $this->smarty->createTemplate("eval: {\n\$foo} ");
        $tpl->assign('foo', 'literal');
        $this->assertEquals(" {\n\$foo} ", $this->smarty->fetch($tpl));
    }

    /*
    *  Test auto literal disabled
    */
    public function testAutoLiteralDisabled() {
        $this->smarty->auto_literal = false;
        $tpl = $this->smarty->createTemplate("eval: { \$foo} ");
        $tpl->assign('foo', 'literal');
        $this->assertEquals(' literal ', $this->smarty->fetch($tpl));
    }
}
