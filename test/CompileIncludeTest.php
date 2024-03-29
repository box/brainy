<?php
/**
* Smarty PHPunit tests compilation of the {include} tag
*
* @package PHPunit
* @author Uwe Tews
*/

namespace Box\Brainy\Tests;


class CompileIncludeTest extends Smarty_TestCase
{
    public function setup(): void {
        parent::setUp();
        $this->smarty->force_compile = true;
        $this->smarty->safe_lookups = \Box\Brainy\Brainy::LOOKUP_SAFE;
    }

    public function testIncludeStandard() {
        $tpl = $this->smarty->createTemplate('eval:{include file="helloworld.tpl"}');
        $content = $this->smarty->fetch($tpl);
        $this->assertEquals("hello world", $content);
    }

    public function testIncludeAssign1() {
        $tpl = $this->smarty->createTemplate('eval:{include file="helloworld.tpl" assign=foo}');
        $this->assertEquals("", $this->smarty->fetch($tpl));
    }

    public function testIncludeAssign2() {
        $tpl = $this->smarty->createTemplate('eval:{assign var=foo value=bar}{include file="helloworld.tpl" assign=foo}{$foo}');
        $this->assertEquals("hello world", $this->smarty->fetch($tpl));
    }

    public function testIncludePassVars() {
        $tpl = $this->smarty->createTemplate("eval:{include file='eval:{\$myvar1}{\$myvar2}' myvar1=1 myvar2=2}");
        $this->assertEquals("12", $this->smarty->fetch($tpl));
    }

    public function testIncludeLocalScope() {
        $this->smarty->assign('foo', 1);
        $tpl = $this->smarty->createTemplate('eval: befor include {$foo} {include file=\'eval:{$foo=2} in include {$foo}\'} after include {$foo}', null, null, $this->smarty);
        $content = $this->smarty->fetch($tpl);
        $this->assertStringContainsString('befor include 1', $content);
        $this->assertStringContainsString('in include 2', $content);
        $this->assertStringContainsString('after include 1', $content);
    }

    public function testIncludeParentScope() {
        $this->smarty->assign('foo', 1);
        $tpl = $this->smarty->createTemplate(
            'eval: befor include {$foo} {include file=\'eval:{$foo=2} in include {$foo}\' scope = parent} after include {$foo}',
            null,
            null,
            $this->smarty
        );
        $content = $this->smarty->fetch($tpl);
        $content2 = $this->smarty->fetch('eval: root value {$foo}');
        $this->assertStringContainsString('befor include 1', $content);
        $this->assertStringContainsString('in include 2', $content);
        $this->assertStringContainsString('after include 2', $content);
        $this->assertStringContainsString('root value 1', $content2);
    }

    public function testIncludeRootScope() {
        $tpl = $this->smarty->createTemplate('eval: befor include {$foo} {include file=\'eval:{$foo=2} in include {$foo}\' scope = root} after include {$foo}');
        $this->smarty->assign('foo', 1);
        $content = $this->smarty->fetch($tpl);
        $content2 = $this->smarty->fetch('eval: smarty value {$foo}');
        $this->assertStringNotContainsString('befor include 1', $content);
        $this->assertStringContainsString('in include 2', $content);
        $this->assertStringContainsString('after include 2', $content);
        $this->assertStringContainsString('smarty value 1', $content2);
    }

    public function testIncludeRootScope2() {
        $this->smarty->assign('foo', 1);
        $tpl = $this->smarty->createTemplate('eval: befor include {$foo} {include file=\'eval:{$foo=2} in include {$foo}\' scope = root} after include {$foo}', null, $this->smarty);
        $content = $this->smarty->fetch($tpl);
        $content2 = $this->smarty->fetch('eval: smarty value {$foo}');
        $this->assertStringContainsString('befor include 1', $content);
        $this->assertStringContainsString('in include 2', $content);
        $this->assertStringContainsString('after include 1', $content);
        $this->assertStringContainsString('smarty value 2', $content2);
    }

    public function testRecursiveIncludes1() {
        $this->smarty->assign('foo', 1);
        $this->smarty->assign('bar','bar');
        $content = $this->smarty->fetch('test_recursive_includes.tpl');
        $this->assertStringContainsString("before 1 bar<br>\nbefore 2 bar<br>\nbefore 3 bar<br>\n\nafter 3 bar<br>\n\nafter 2 bar<br>\n\nafter 1 bar<br>", $content);
    }

    public function testRecursiveIncludes2() {
        $this->smarty->assign('foo', 1);
        $this->smarty->assign('bar','bar');
        $content = $this->smarty->fetch('test_recursive_includes2.tpl');
        $this->assertStringContainsString("before 1 bar<br>\nbefore 3 bar<br>\nbefore 5 bar<br>\n\nafter 5 bar<br>\n\nafter 3 bar<br>\n\nafter 1 bar<br>", $content);
    }

}
