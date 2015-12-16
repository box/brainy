<?php
/**
* Smarty PHPunit tests chained loading of dependend pluglind
*
* @package PHPunit
* @author Rodney Rehm
*/

namespace Box\Brainy\Tests;


class PluginChainedLoadTest extends Smarty_TestCase
{
    public function testPluginChainedLoad() {
        $this->smarty->addPluginsDir(dirname(__FILE__) . "/PHPunitplugins/");
        $this->assertContains('from chain3', $this->smarty->fetch('test_plugin_chained_load.tpl'));
    }
    public function testDeferredChainedLoad() {
        $this->smarty->addPluginsDir(dirname(__FILE__) . "/PHPunitplugins/");
        $tpl1 = $this->smarty->createTemplate('string:{chain1}');
        $tpl1->fetch();
        $output = $tpl1->fetch(); // should not throw
        $this->assertContains('from chain3', $output);
    }

}
