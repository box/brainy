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

}
