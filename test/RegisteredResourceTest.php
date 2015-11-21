<?php
/**
 * Smarty PHPunit tests register->resource
 *
 * @package PHPunit
 * @author Uwe Tews
 */

namespace Box\Brainy\Tests;


class RegisteredResourceTest extends Smarty_TestCase
{
    public function setUp() {
        parent::setUp();
        $this->smarty->registerResource("rr", array(
            "\Box\Brainy\Tests\RegisteredResourceTest::rr_get_template",
            "\Box\Brainy\Tests\RegisteredResourceTest::rr_get_timestamp",
            "\Box\Brainy\Tests\RegisteredResourceTest::rr_get_secure",
            "\Box\Brainy\Tests\RegisteredResourceTest::rr_get_trusted"
        ));
    }

    /**
     * resource functions
     */
    public static function rr_get_template($tpl_name, &$tpl_source, $smarty_obj) {
        // populating $tpl_source
        $tpl_source = '{$x="hello world"}{$x}';

        return true;
    }

    public static function rr_get_timestamp($tpl_name, &$tpl_timestamp, $smarty_obj) {
        // $tpl_timestamp.
        $tpl_timestamp = (int) floor(time() / 100) * 100;

        return true;
    }

    public static function rr_get_secure($tpl_name, $smarty_obj) {
        // assume all templates are secure
        return true;
    }

    public static function rr_get_trusted($tpl_name, $smarty_obj) {
        // not used for templates
    }

    /**
     * test resource plugin rendering
     */
    public function testResourcePlugin() {
        $this->assertEquals('hello world', $this->smarty->fetch('rr:test'));
    }
    /**
     * test resource plugin timesatmp
     */
    public function testResourcePluginTimestamp() {
        $tpl = $this->smarty->createTemplate('rr:test');
        $this->assertTrue(is_integer($tpl->source->timestamp));
        $this->assertEquals(10, strlen($tpl->source->timestamp));
    }
    /**
     * test compile_id change
     */
    public function testResourceCompileIdChange() {
        $this->smarty->registerResource('myresource', array(
            '\Box\Brainy\Tests\RegisteredResourceTest::getSource',
            '\Box\Brainy\Tests\RegisteredResourceTest::getTimestamp',
            '\Box\Brainy\Tests\RegisteredResourceTest::getSecure',
            '\Box\Brainy\Tests\RegisteredResourceTest::getTrusted',
        ));
        $this->smarty->compile_id = 'a';
        $this->assertEquals('this is template 1', $this->smarty->fetch('myresource:some'));
        $this->assertEquals('this is template 1', $this->smarty->fetch('myresource:some'));
        $this->smarty->compile_id = 'b';
        $this->assertEquals('this is template 2', $this->smarty->fetch('myresource:some'));
        $this->assertEquals('this is template 2', $this->smarty->fetch('myresource:some'));

    }

    // resource functions for compile_id change test

    public static function getSecure($name, $smarty) {
        return true;
    }
    public static function getTrusted($name, $smarty) {
    }
    public static function getSource($name, &$source, $smarty) {
        // we update a counter, so that we return a new source for every call
        static $counter = 0;
        $counter++;

        // construct a new source
        $source = "this is template $counter";

        return true;
    }
    public static function getTimestamp($name, &$timestamp, $smarty) {
        // always pretend the template is brand new
        $timestamp = time();

        return true;
    }
}


