<?php
/**
* Smarty PHPunit tests compiler plugin
*
* @package PHPunit
* @author Uwe Tews
*/

namespace Box\Brainy\Tests;


class CompilerPluginTest extends Smarty_TestCase
{
    /**
    * test compiler plugin
    */
    public function testCompilerPlugin() {
        $this->smarty->addPluginsDir(dirname(__FILE__)."/PHPunitplugins/");
        $this->assertEquals('test output', $this->smarty->fetch('eval:{test data="test output"}{/test}'));
    }

}
