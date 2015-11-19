<?php
/**
* Smarty PHPunit tests compilation of function plugins
*
* @package PHPunit
* @author Uwe Tews
*/

namespace Box\Brainy\Tests;


class CompileFunctionPluginTest extends Smarty_TestCase
{
    public function testFunctionPluginFromTemplateFile() {
        $tpl = $this->smarty->createTemplate('functionplugintest.tpl', $this->smarty);
        $this->assertEquals("10", $this->smarty->fetch($tpl));
    }
    public function testFunctionPluginFromCompiledTemplateFile() {
        $this->smarty->force_compile = false;
        $tpl = $this->smarty->createTemplate('functionplugintest.tpl', $this->smarty);
        $this->assertEquals("10", $this->smarty->fetch($tpl));
    }
    public function testFunctionPluginRegisteredFunction() {
        $this->smarty->registerPlugin(\Box\Brainy\Brainy::PLUGIN_FUNCTION, 'plugintest', '\Box\Brainy\Tests\CompileFunctionPluginTest::myplugintest');
        $tpl = $this->smarty->createTemplate('eval:{plugintest foo=bar}', $this->smarty);
        $this->assertEquals("plugin test called bar", $this->smarty->fetch($tpl));
    }

    public function testMultiLineTags() {
        $this->assertEquals("10", $this->smarty->fetch("eval:{counter\n\tstart=10}"));
    }


    public static function myplugintest($params, &$smarty)
    {
        return "plugin test called $params[foo]";
    }
}

