<?php
/**
 * @package PHPunit
 * @author Uwe Tews
 */

/**
* class for appendByRef tests
*/
class LiteralTest extends PHPUnit_Framework_TestCase
{
    public function setUp() {
        $this->smarty = SmartyTests::$smarty;
        SmartyTests::init();
    }

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

    public function testBlockInLiteralTagInInheritedTemplate() {
        $this->smarty->clearCompiledTemplate();
        $this->smarty->setTemplateDir(array('test/templates/extendsresource/', 'test/templates/'));
        $result = $this->smarty->fetch('extends:eval:{literal} {block "title"}{$foo}{/block} {/literal}|004_parent.tpl');
        $this->assertEquals(' {block "title"}{$foo}{/block} ', $result);
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
