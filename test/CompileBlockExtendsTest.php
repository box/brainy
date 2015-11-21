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
    public function setUp() {
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
        $this->assertContains('Default Title', $result);
    }
    /**
    * test  child/parent template chain
    */
    public function testCompileBlockChild_002() {
        $result = $this->smarty->fetch('002_child.tpl');
        $this->assertContains('Page Title', $result);
    }
    /**
    * test  child/parent template chain with {$this->smarty.block.child)
    */
    public function testCompileBlockChildSmartyChild_006() {
        $result = $this->smarty->fetch('006_child_smartychild.tpl');
        $this->assertContains('here is >child text< included', $result);
    }
    /**
    * test  child/parent template chain loading plugin
    */
    public function testCompileBlockChildPlugin_008() {
        $result = $this->smarty->fetch('008_child_plugin.tpl');
        $this->assertContains('escaped &lt;text&gt;', $result);
    }
    /**
    * test parent template with nested blocks
    */
    public function testCompileBlockParentNested_009() {
        $result = $this->smarty->fetch('009_parent_nested.tpl');
        $this->assertContains('Title with -default- here', $result);
    }
    /**
    * test  child/parent template chain with nested block
    */
    public function testCompileBlockChildNested_010() {
        $result = $this->smarty->fetch('010_child_nested.tpl');
        $this->assertContains('Title with -content from child- here', $result);
    }
    /**
    * test  child/parent template chain with nested block and include
    */
    public function testCompileBlockChildNestedInclude_011() {
        $result = $this->smarty->fetch('011_grandchild_nested_include.tpl');
        $this->assertContains('some content', $result);
    }
    /**
    * test  grandchild/child/parent template chain
    */
    public function testCompileBlockGrandChild_012() {
        $result = $this->smarty->fetch('012_grandchild.tpl');
        $this->assertContains('Grandchild Page Title', $result);
    }
    /**
    * test  grandchild/child/parent template chain with {$this->smarty.block.child}
    */
    public function testCompileBlockGrandChildSmartyChild_014() {
        $result = $this->smarty->fetch('014_grandchild_smartychild.tpl');
        $this->assertContains('child title with - grandchild content - here', $result);
    }
    /**
    * test  grandchild/child/parent template chain with nested block
    */
    public function testCompileBlockGrandChildNested_016() {
        $result = $this->smarty->fetch('016_grandchild_nested.tpl');
        $this->assertContains('child title with -grandchild content- here', $result);
    }
    /**
    * test  grandchild/child/parent template chain with nested {$this->smarty.block.child}
    */
    public function testCompileBlockGrandChildNested_017() {
        $result = $this->smarty->fetch('017_grandchild_nested.tpl');
        $this->assertContains('child pre -grandchild content- child post', $result);
    }
    /**
    * test  nested child block with hide and auto_literal = false
    */
    public function testCompileBlockChildNestedHideAutoLiteralFalse_019() {
        $this->smarty->auto_literal = false;
        $result = $this->smarty->fetch('019_child_nested_hide_autoliteral.tpl');
        $this->assertContains('nested block', $result);
    }
    /**
    * test  child/parent template chain starting in subtempates
    */
    public function testCompileBlockStartSubTemplates_020() {
        $result = $this->smarty->fetch('020_include_root.tpl');
        $this->assertContains('page 1', $result);
        $this->assertContains('page 2', $result);
        $this->assertContains('page 3', $result);
        $this->assertContains('block 1', $result);
        $this->assertContains('block 2', $result);
        $this->assertContains('block 3', $result);
   }
    /**
    * test  grandchild/child/parent dependency test1
    */
    public function testCompileBlockGrandChildMustCompile_021_1() {
        $this->smarty->caching = true;
        $tpl = $this->smarty->createTemplate('021_grandchild.tpl');
        $result = $this->smarty->fetch($tpl);
        $this->assertContains('Grandchild Page Title', $result);
        $tpl2 = $this->smarty->createTemplate('021_grandchild.tpl');
        $result = $this->smarty->fetch($tpl2);
        $this->assertContains('Grandchild Page Title', $result);
    }
    /**
    * test  grandchild/child/parent dependency test2
    */
    public function testCompileBlockGrandChildMustCompile_021_2() {
        touch($this->smarty->getTemplateDir(0) . '021_grandchild.tpl');
        $this->smarty->caching = true;
        $tpl = $this->smarty->createTemplate('021_grandchild.tpl');
        $result = $this->smarty->fetch($tpl);
        $this->assertContains('Grandchild Page Title', $result);
        $tpl2 = $this->smarty->createTemplate('021_grandchild.tpl');
        $result = $this->smarty->fetch($tpl2);
        $this->assertContains('Grandchild Page Title', $result);
    }
    /**
    * test  grandchild/child/parent dependency test3
    */
    public function testCompileBlockGrandChildMustCompile_021_3() {
        touch($this->smarty->getTemplateDir(0) . '021_child.tpl');
        $this->smarty->caching = true;
        $tpl = $this->smarty->createTemplate('021_grandchild.tpl');
        $result = $this->smarty->fetch($tpl);
        $this->assertContains('Grandchild Page Title', $result);
        $tpl2 = $this->smarty->createTemplate('021_grandchild.tpl');
        $result = $this->smarty->fetch($tpl2);
        $this->assertContains('Grandchild Page Title', $result);
    }
    /**
    * test  grandchild/child/parent dependency test4
    */
    public function testCompileBlockGrandChildMustCompile_021_4() {
        touch($this->smarty->getTemplateDir(0) . '021_parent.tpl');
        $this->smarty->caching = true;
        $tpl = $this->smarty->createTemplate('021_grandchild.tpl');
        $result = $this->smarty->fetch($tpl);
        $this->assertContains('Grandchild Page Title', $result);
        $tpl2 = $this->smarty->createTemplate('021_grandchild.tpl');
        $result = $this->smarty->fetch($tpl2);
        $this->assertContains('Grandchild Page Title', $result);
    }

    /**
     * @expectedException \Box\Brainy\Exceptions\SmartyCompilerException
     */
    public function testDirt_022() {
        $this->smarty->display('eval:foo{extends file="hello"}');
    }

    /**
     * test {strip} outside {block]
     */
    public function testChildStrip_023() {
        $result = $this->smarty->fetch('023_child.tpl');
        $this->assertContains('<div id="header"> <div>Demo</div></div>', $result);
    }

    /**
     * test {$this->smarty.block.child} for not existing child {block]
     */
    public function testNotExistingChildBlock_024() {
        $result = $this->smarty->fetch("eval:{block 'b1'}no >{\$smarty.block.child}< child{/block}");
        $this->assertContains('no >< child', $result);
    }

    /**
     * @expectedException \Box\Brainy\Exceptions\SmartyCompilerException
     * @expectedExceptionMessage Expected to be inside {block}, but was not
     */
    public function testSmartyBlockChildOutsideBlock_025() {
        $this->smarty->display('025_parent.tpl');
    }

}
