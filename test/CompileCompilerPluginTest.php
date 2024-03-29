<?php
/**
* Smarty PHPunit tests compilation of compiler plugins
*
* @package PHPunit
* @author Uwe Tews
*/

namespace Box\Brainy\Tests;


class CompileCompilerPluginTest extends Smarty_TestCase
{
    public function setup(): void {
        parent::setUp();
        $this->smarty->force_compile = true;
    }

    /**
    * test compiler plugin tag in template file
    */
    public function testCompilerPluginFromTemplateFile() {
        $this->smarty->registerPlugin(\Box\Brainy\Brainy::PLUGIN_COMPILER,'compilerplugin', '\Box\Brainy\Tests\CompileCompilerPluginTest::mycompilerplugin');
        $tpl = $this->smarty->createTemplate('compilerplugintest.tpl');
        $this->assertEquals("Hello World", trim($this->smarty->fetch($tpl)));
    }
    /**
    * test compiler plugin tag in compiled template file
    */
    public function testCompilerPluginFromCompiledTemplateFile() {
        $this->smarty->force_compile = false;
        $this->smarty->registerPlugin(\Box\Brainy\Brainy::PLUGIN_COMPILER, 'compilerplugin', '\Box\Brainy\Tests\CompileCompilerPluginTest::mycompilerplugin');
        $tpl = $this->smarty->createTemplate('compilerplugintest.tpl');
        $this->assertEquals("Hello World", trim($this->smarty->fetch($tpl)));
    }


    public static function mycompilerplugin($params, $compiler) {
        return 'echo \'Hello World\';';
    }
}
