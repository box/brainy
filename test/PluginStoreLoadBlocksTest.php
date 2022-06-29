<?php

namespace Box\Brainy\Tests;


class PluginStoreLoadBlocksTest extends Smarty_TestCase
{
    public function setup(): void
    {
        parent::setUp();
        $this->smarty->assign('varname', 'poison');
    }

    public function testStoreLoad()
    {
        $this->expectOutputString('foo bar');
        $this->smarty->display('eval:{store to="varname"}foo bar{/store}{load from="varname"}');
    }

    public function testStoreLoadDoesNotOutput()
    {
        $this->expectOutputString('');
        $this->smarty->display('eval:{store to="varname"}foo bar{/store}');
    }

    public function testStoreLoadScope()
    {
        $this->expectOutputString('foo bar');
        $this->smarty->display('eval:{store to="varname"}foo bar{/store}{assign var="varname" value="poop"}{load from="varname"}');
        $this->assertEquals('poison', $this->smarty->getTemplateVars('varname'));
    }

}
