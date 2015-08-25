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
    /**
     * test register->compilerFunction method for function
     */
    public function testRegisterCompilerFunction() {
        $this->smarty->registerPlugin(\Box\Brainy\Brainy::PLUGIN_COMPILER,'testcompilerfunction', 'mycompilerfunction');
        $this->assertEquals('mycompilerfunction', $this->smarty->registered_plugins['compiler']['testcompilerfunction'][0]);
        $this->assertEquals('hello world 1', $this->smarty->fetch('eval:{testcompilerfunction var=1}'));
    }

    /**
     * test register->compilerFunction method for blocks
     */
    public function testRegisterCompilerFunctionBlock() {
        $this->smarty->registerPlugin(\Box\Brainy\Brainy::PLUGIN_COMPILER,'foo', 'mycompilerfunctionopen');
        $this->smarty->registerPlugin(\Box\Brainy\Brainy::PLUGIN_COMPILER,'fooclose', 'mycompilerfunctionclose');
        $result = $this->smarty->fetch('eval:{foo} hallo {/foo}');
        $this->assertEquals('open tag hallo close tag', $result);
    }
    /**
     * test unregister->compilerFunction method
     */
    public function testUnregisterCompilerFunction() {
        $this->smarty->registerPlugin(\Box\Brainy\Brainy::PLUGIN_COMPILER,'testcompilerfunction', 'mycompilerfunction');
        $this->smarty->unregisterPlugin(\Box\Brainy\Brainy::PLUGIN_COMPILER,'testcompilerfunction');
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
function mycompilerfunction($params, $smarty) {
    return "echo 'hello world {$params['var']}';\n";
}
function mycompilerfunctionopen($params, $smarty) {
    return "echo 'open tag';\n";
}
function mycompilerfunctionclose($params, $smarty) {
    return "echo 'close tag';\n";
}
