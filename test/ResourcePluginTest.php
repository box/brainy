<?php
/**
 * Smarty PHPunit tests resource plugins
 *
 * @package PHPunit
 * @author Uwe Tews
 */

namespace Box\Brainy\Tests;


class ResourcePluginTest extends Smarty_TestCase
{
    public function setUp() {
        parent::setUp();
        \Box\Brainy\Resources\Resource::$resources = array();
    }

    public function testResourcePluginRegisteredInstance() {
        $this->smarty->registerResource('db2', new ResourcePlugins\ResourceDB2());
        $this->assertEquals('hello world', $this->smarty->fetch('db2:test'));
    }
    /**
     * test resource plugin non-existent compiled cache of a recompiling resource
     */
    public function testResourcePluginRecompiledCompiledFilepath() {
        $this->smarty->registerResource('db2', new ResourcePlugins\ResourceDB2());
        $tpl = $this->smarty->createTemplate('db2:test.tpl');
        $expected = realpath('test/compiled/'.sha1('db2:test.tpl').'.db2.test.tpl.php');
        $this->assertFalse((bool) $expected);
        $this->assertFalse($tpl->compiled->filepath);
    }
    /**
     * test resource plugin timesatmp
     */
    public function testResourcePluginTimestamp() {
        $this->smarty->registerResource('db2', new ResourcePlugins\ResourceDB2());
        $tpl = $this->smarty->createTemplate('db2:test');
        $this->assertTrue(is_integer($tpl->source->timestamp));
        $this->assertEquals(10, strlen($tpl->source->timestamp));
    }

    public function testResourcePluginExtendsall() {
        $this->smarty->registerResource('extendsall', new ResourcePlugins\ResourceExtendsAll());
        $this->smarty->setTemplateDir( array(
            'root' => 'test/templates',
            'test/templates_2',
            'test/templates_3',
            'test/templates_4',
        ));

        $expected = "templates\n\ntemplates_3\ntemplates\n\ntemplates_4";
        $this->assertEquals($expected, $this->smarty->fetch('extendsall:extendsall.tpl'));
    }

    public function testResourcePluginExtendsallOne() {
        $this->smarty->registerResource('extendsall', new ResourcePlugins\ResourceExtendsAll());
        $this->smarty->setTemplateDir( array(
            'root' => 'test/templates',
            'test/templates_2',
            'test/templates_3',
            'test/templates_4',
        ));

        $expected = "templates\ntemplates";
        $this->assertEquals($expected, $this->smarty->fetch('extendsall:extendsall2.tpl'));
    }

    public function testSharing() {
        $smarty = new \Box\Brainy\Brainy();
        $smarty->_resource_handlers = array();
        $_smarty = clone $smarty;
        $smarty->fetch('eval:foo');
        $_smarty->fetch('eval:foo');

        $this->assertTrue($smarty->_resource_handlers['eval'] === $_smarty->_resource_handlers['eval']);
    }

    public function testExplicit() {
        $smarty = new \Box\Brainy\Brainy();
        $smarty->_resource_handlers = array();
        $_smarty = clone $smarty;
        $smarty->fetch('eval:foo');
        $_smarty->registerResource('eval', new \Box\Brainy\Resources\ResourcesEval());
        $_smarty->fetch('eval:foo');

        $this->assertFalse($smarty->_resource_handlers['eval'] === $_smarty->_resource_handlers['eval']);
    }
}
