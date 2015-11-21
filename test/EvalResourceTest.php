<?php
/**
* Smarty PHPunit tests for eval resources
*
* @package PHPunit
* @author Uwe Tews
*/

namespace Box\Brainy\Tests;


class EvalResourceTest extends Smarty_TestCase
{
    /**
    * test template eval exits
    */
    public function testTemplateEvalExists1() {
        $tpl = $this->smarty->createTemplate('eval:{$foo}');
        $this->assertTrue($tpl->source->exists);
    }
    public function testTemplateEvalExists2() {
        $this->assertTrue($this->smarty->templateExists('eval:{$foo}'));
    }
    /**
    * test getTemplateFilepath
    */
    public function testGetTemplateFilepath() {
        $tpl = $this->smarty->createTemplate('eval:hello world');
        $this->assertEquals('2aae6c35c94fcfb415dbe95f408b9ce91ee846ed', $tpl->source->filepath);
    }
    /**
    * test getTemplateTimestamp
    */
    public function testGetTemplateTimestamp() {
        $tpl = $this->smarty->createTemplate('eval:hello world');
        $this->assertFalse($tpl->source->timestamp);
    }
    /**
    * test getTemplateSource
    */
    public function testGetTemplateSource() {
        $tpl = $this->smarty->createTemplate('eval:hello world{$foo}');
        $this->assertEquals('hello world{$foo}', $tpl->source->content);
    }
    /**
    * test isEvaluated
    */
    public function testIsEvaluated() {
        $tpl = $this->smarty->createTemplate('eval:hello world');
        $this->assertTrue($tpl->source->recompiled);
    }
    /**
    * test mustCompile
    */
    public function testMustCompile() {
        $tpl = $this->smarty->createTemplate('eval:hello world');
        $this->assertTrue($tpl->mustCompile());
    }
    /**
    * test getCompiledFilepath
    */
    public function testGetCompiledFilepath() {
        $tpl = $this->smarty->createTemplate('eval:hello world');
        $this->assertFalse($tpl->compiled->filepath);
    }
    /**
    * test getCompiledTimestamp
    */
    public function testGetCompiledTimestamp() {
        $tpl = $this->smarty->createTemplate('eval:hello world');
        $this->assertFalse($tpl->compiled->timestamp);
    }
    /**
    * test getRenderedTemplate
    */
    public function testGetRenderedTemplate() {
        $tpl = $this->smarty->createTemplate('eval:hello world');
        $this->assertEquals('hello world', $tpl->fetch());
    }
    /**
    * test that no complied template and cache file was produced
    */
    public function testNoFiles() {
        $this->smarty->caching = true;
        $this->smarty->clearCompiledTemplate();
        $tpl = $this->smarty->createTemplate('eval:hello world');
        $this->assertEquals('hello world', $this->smarty->fetch($tpl));
        $this->assertEquals(0, $this->smarty->clearCompiledTemplate());
    }
}
