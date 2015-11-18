<?php
/**
 * Smarty PHPunit tests register->compilerFunction / unregister->compilerFunction methods
 *
 * @package PHPunit
 * @author Uwe Tews
 */

namespace Box\Brainy\Tests;


class RegisterCompilerFunctionTest extends Smarty_TestCase
{
    public static function myCompilerFunction($params, $smarty)
    {
        return "echo 'hello world {$params['var']}';\n";
    }
    public static function myCompilerFunctionOpen($params, $smarty)
    {
        return "echo 'open tag';\n";
    }
    public static function myCompilerFunctionClose($params, $smarty)
    {
        return "echo 'close tag';\n";
    }

    /**
     * test register->compilerFunction method for function
     */
    public function testRegisterCompilerFunction() {
        $this->smarty->registerPlugin(
            \Box\Brainy\Brainy::PLUGIN_COMPILER,
            'testcompilerfunction',
            '\Box\Brainy\Tests\RegisterCompilerFunctionTest::myCompilerFunction'
        );
        $this->assertEquals(
            '\Box\Brainy\Tests\RegisterCompilerFunctionTest::myCompilerFunction',
            $this->smarty->registered_plugins['compiler']['testcompilerfunction']
        );
        $this->assertEquals('hello world 1', $this->smarty->fetch('eval:{testcompilerfunction var=1}'));
    }

    /**
     * test register->compilerFunction method for blocks
     */
    public function testRegisterCompilerFunctionBlock() {
        $this->smarty->registerPlugin(\Box\Brainy\Brainy::PLUGIN_COMPILER, 'foo', '\Box\Brainy\Tests\RegisterCompilerFunctionTest::myCompilerFunctionOpen');
        $this->smarty->registerPlugin(\Box\Brainy\Brainy::PLUGIN_COMPILER, 'fooclose', '\Box\Brainy\Tests\RegisterCompilerFunctionTest::myCompilerFunctionClose');
        $result = $this->smarty->fetch('eval:{foo} hallo {/foo}');
        $this->assertEquals('open tag hallo close tag', $result);
    }
    /**
     * test unregister->compilerFunction method
     */
    public function testUnregisterCompilerFunction() {
        $this->smarty->registerPlugin(\Box\Brainy\Brainy::PLUGIN_COMPILER, 'testcompilerfunction', '\Box\Brainy\Tests\RegisterCompilerFunctionTest::myCompilerFunction');
        $this->smarty->unregisterPlugin(\Box\Brainy\Brainy::PLUGIN_COMPILER, 'testcompilerfunction');
        $this->assertFalse(isset($this->smarty->registered_plugins[\Box\Brainy\Brainy::PLUGIN_COMPILER]['testcompilerfunction']));
    }
    /**
     * test unregister->compilerFunction method not registered
     */
    public function testUnregisterCompilerFunctionNotRegistered() {
        $this->smarty->unregisterPlugin(\Box\Brainy\Brainy::PLUGIN_COMPILER,'testcompilerfunction');
        $this->assertFalse(isset($this->smarty->registered_plugins[\Box\Brainy\Brainy::PLUGIN_COMPILER]['testcompilerfunction']));
    }
}
