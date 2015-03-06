<?php
/**
 * Tests for the {store} and {load} blocks
 *
 * @package PHPunit
 * @author Matt Basta
 */

class PluginStoreLoadBlocksTest extends Smarty_TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->smarty->assign('varname', 'poison');
    }

    public function testStoreLoad()
    {
        $this->expectOutputString('foo bar');
        $this->smarty->display('string:{store to="varname"}foo bar{/store}{load from="varname"}');
    }

    public function testStoreLoadDoesNotOutput()
    {
        $this->expectOutputString('');
        $this->smarty->display('string:{store to="varname"}foo bar{/store}');
    }

    public function testStoreLoadScope()
    {
        $this->expectOutputString('foo bar');
        $this->smarty->display('string:{store to="varname"}foo bar{/store}{assign var="varname" value="poop"}{load from="varname"}');
        $this->assertEquals('poison', $this->smarty->getTemplateVars('varname'));
    }

}
