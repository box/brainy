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
}
