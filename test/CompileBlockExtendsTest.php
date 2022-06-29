<?php
/**
* Smarty PHPunit tests for Block Extends
*
* @package PHPunit
* @author Uwe Tews
*/

namespace Box\Brainy\Tests;


class CompileBlockExtendsTest extends Smarty_TestCase
{
    public function setup(): void {
        parent::setUp();
        $this->smarty->setTemplateDir(array('test/templates/compileblockextends/', 'test/templates/'));
    }

    /**
     * test block default outout
     */
    public function testBlockDefault_000_1() {
        $result = $this->smarty->fetch('eval:{block name=test}-- block default --{/block}');
        $this->assertEquals('-- block default --', $result);
    }

    public function testBlockDefault_000_2() {
        $this->smarty->assign('foo', 'another');
        $result = $this->smarty->fetch('eval:{block name=test}-- {$foo} block default --{/block}');
        $this->assertEquals('-- another block default --', $result);
    }
    /**
    * test just call of  parent template, no blocks predefined
    */
    public function testCompileBlockParent_001() {
        $result = $this->smarty->fetch('001_parent.tpl');
        $this->assertStringContainsString('Default Title', $result);
    }
    /**
    * test  child/parent template chain
    */
    public function testCompileBlockChild_002() {
        $result = $this->smarty->fetch('002_child.tpl');
        $this->assertStringContainsString('Page Title', $result);
    }
    /**
    * test  child/parent template chain with {$this->smarty.block.child)
    */
    public function testCompileBlockChildSmartyChild_006() {
        $result = $this->smarty->fetch('006_child_smartychild.tpl');
        $this->assertStringContainsString('here is >child text< included', $result);
    }
    /**
    * test  child/parent template chain loading plugin
    */
    public function testCompileBlockChildPlugin_008() {
        $result = $this->smarty->fetch('008_child_plugin.tpl');
        $this->assertStringContainsString('escaped &lt;text&gt;', $result);
    }
    /**
    * test parent template with nested blocks
    */
    public function testCompileBlockParentNested_009() {
        $result = $this->smarty->fetch('009_parent_nested.tpl');
        $this->assertStringContainsString('Title with -default- here', $result);
    }
    /**
    * test  child/parent template chain with nested block
    */
    public function testCompileBlockChildNested_010() {
        $result = $this->smarty->fetch('010_child_nested.tpl');
        $this->assertStringContainsString('Title with -content from child- here', $result);
    }
    /**
    * test  child/parent template chain with nested block and include
    */
    public function testCompileBlockChildNestedInclude_011() {
        $result = $this->smarty->fetch('011_grandchild_nested_include.tpl');
        $this->assertStringContainsString('some content', $result);
    }
    /**
    * test  grandchild/child/parent template chain
    */
    public function testCompileBlockGrandChild_012() {
        $result = $this->smarty->fetch('012_grandchild.tpl');
        $this->assertStringContainsString('Grandchild Page Title', $result);
    }
    /**
    * test  grandchild/child/parent template chain with {$this->smarty.block.child}
    */
    public function testCompileBlockGrandChildSmartyChild_014() {
        $result = $this->smarty->fetch('014_grandchild_smartychild.tpl');
        $this->assertStringContainsString('child title with - grandchild content - here', $result);
    }
    /**
    * test  grandchild/child/parent template chain with nested block
    */
    public function testCompileBlockGrandChildNested_016() {
        $result = $this->smarty->fetch('016_grandchild_nested.tpl');
        $this->assertStringContainsString('child title with -grandchild content- here', $result);
    }
    /**
    * test  grandchild/child/parent template chain with nested {$this->smarty.block.child}
    */
    public function testCompileBlockGrandChildNested_017() {
        $result = $this->smarty->fetch('017_grandchild_nested.tpl');
        $this->assertStringContainsString('child pre -grandchild content- child post', $result);
    }
    /**
    * test  nested child block with hide and auto_literal = false
    */
    public function testCompileBlockChildNestedHideAutoLiteralFalse_019() {
        $this->smarty->auto_literal = false;
        $result = $this->smarty->fetch('019_child_nested_hide_autoliteral.tpl');
        $this->assertStringContainsString('nested block', $result);
    }
    /**
    * test  child/parent template chain starting in subtempates
    */
    public function testCompileBlockStartSubTemplates_020() {
        $result = $this->smarty->fetch('020_include_root.tpl');
        $this->assertStringContainsString('page 1', $result);
        $this->assertStringContainsString('page 2', $result);
        $this->assertStringContainsString('page 3', $result);
        $this->assertStringContainsString('block 1', $result);
        $this->assertStringContainsString('block 2', $result);
        $this->assertStringContainsString('block 3', $result);
   }
    /**
    * test  grandchild/child/parent dependency test1
    */
    public function testCompileBlockGrandChildMustCompile_021_1() {
        $this->smarty->caching = true;
        $tpl = $this->smarty->createTemplate('021_grandchild.tpl');
        $result = $this->smarty->fetch($tpl);
        $this->assertStringContainsString('Grandchild Page Title', $result);
        $tpl2 = $this->smarty->createTemplate('021_grandchild.tpl');
        $result = $this->smarty->fetch($tpl2);
        $this->assertStringContainsString('Grandchild Page Title', $result);
    }
    /**
    * test  grandchild/child/parent dependency test2
    */
    public function testCompileBlockGrandChildMustCompile_021_2() {
        touch($this->smarty->getTemplateDir(0) . '021_grandchild.tpl');
        $this->smarty->caching = true;
        $tpl = $this->smarty->createTemplate('021_grandchild.tpl');
        $result = $this->smarty->fetch($tpl);
        $this->assertStringContainsString('Grandchild Page Title', $result);
        $tpl2 = $this->smarty->createTemplate('021_grandchild.tpl');
        $result = $this->smarty->fetch($tpl2);
        $this->assertStringContainsString('Grandchild Page Title', $result);
    }
    /**
    * test  grandchild/child/parent dependency test3
    */
    public function testCompileBlockGrandChildMustCompile_021_3() {
        touch($this->smarty->getTemplateDir(0) . '021_child.tpl');
        $this->smarty->caching = true;
        $tpl = $this->smarty->createTemplate('021_grandchild.tpl');
        $result = $this->smarty->fetch($tpl);
        $this->assertStringContainsString('Grandchild Page Title', $result);
        $tpl2 = $this->smarty->createTemplate('021_grandchild.tpl');
        $result = $this->smarty->fetch($tpl2);
        $this->assertStringContainsString('Grandchild Page Title', $result);
    }
    /**
    * test  grandchild/child/parent dependency test4
    */
    public function testCompileBlockGrandChildMustCompile_021_4() {
        touch($this->smarty->getTemplateDir(0) . '021_parent.tpl');
        $this->smarty->caching = true;
        $tpl = $this->smarty->createTemplate('021_grandchild.tpl');
        $result = $this->smarty->fetch($tpl);
        $this->assertStringContainsString('Grandchild Page Title', $result);
        $tpl2 = $this->smarty->createTemplate('021_grandchild.tpl');
        $result = $this->smarty->fetch($tpl2);
        $this->assertStringContainsString('Grandchild Page Title', $result);
    }

    public function testDirt_022()
    {
        $this->expectException(\Box\Brainy\Exceptions\SmartyCompilerException::class);
        $this->smarty->display('eval:foo{extends file="hello"}');
    }

    /**
     * test {strip} outside {block]
     */
    public function testChildStrip_023() {
        $result = $this->smarty->fetch('023_child.tpl');
        $this->assertStringContainsString('<div id="header"> <div>Demo</div></div>', $result);
    }

    /**
     * test {$this->smarty.block.child} for not existing child {block]
     */
    public function testNotExistingChildBlock_024() {
        $result = $this->smarty->fetch("eval:{block 'b1'}no >{\$smarty.block.child}< child{/block}");
        $this->assertStringContainsString('no >< child', $result);
    }

    public function testSmartyBlockChildOutsideBlock_025() {
        $this->expectException(\Box\Brainy\Exceptions\SmartyCompilerException::class);
        $this->expectExceptionMessage('Expected to be inside {block}, but was not');
        $this->smarty->display('025_parent.tpl');
    }


    public function testIncludesInBlock()
    {
        $out = $this->smarty->fetch('eval:{block name="foo"}{include file="eval:temp"}{/block}');
        $this->assertEquals('temp', $out);
    }

}
