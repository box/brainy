<?php
/**
 * Brainy lookup safety test suite
 *
 * @package PHPunit
 * @author Matt Basta
 */

namespace Box\Brainy\Tests;


class LookupSafetyTest extends Smarty_TestCase
{

    public function setUp()
    {
        parent::setUp();
        error_reporting(E_ALL);
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Notice
     */
    public function testUnsafeLookupsThrowException() {
        $this->expectOutputString('');
        $this->smarty->safe_lookups = \Box\Brainy\Brainy::LOOKUP_UNSAFE;
        $this->smarty->display('eval:{$does_not_exist}');
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Notice
     */
    public function testUnsafeIndexLookupsThrowException() {
        $this->expectOutputString('');
        $this->smarty->safe_lookups = \Box\Brainy\Brainy::LOOKUP_UNSAFE;
        $this->smarty->assign('foo', array());
        $this->smarty->display('eval:{$foo[0]}');
    }

    /*
    The tests below test the LOOKUP_SAFE behavior.
    */

    public function testSafeLookupsDoNotThrowException() {
        $this->expectOutputString('');
        $this->smarty->safe_lookups = \Box\Brainy\Brainy::LOOKUP_SAFE;
        $this->smarty->display('eval:{$does_not_exist}');
    }

    public function testSafeIndexLookupsDoNotThrowException() {
        $this->expectOutputString('');
        $this->smarty->safe_lookups = \Box\Brainy\Brainy::LOOKUP_SAFE;
        $this->smarty->assign('foo', array());
        $this->smarty->display('eval:{$foo[0]}');
    }

    public function testSafeMemberLookupsInLangConstructsPasses() {
        $this->expectOutputString('it is unset');
        $this->smarty->safe_lookups = \Box\Brainy\Brainy::LOOKUP_SAFE;
        $this->smarty->assign('foo', array());
        $this->smarty->display('eval:{if !isset($foo.bar)}it is unset{/if}');
    }

    public function testSafeIndexLookupsInLangConstructsPasses() {
        $this->expectOutputString('it is empty');
        $this->smarty->safe_lookups = \Box\Brainy\Brainy::LOOKUP_SAFE;
        $this->smarty->assign('foo', '');
        $this->smarty->display('eval:{if empty($foo)}it is empty{/if}');
    }

    public function testSafeSuperglobalIndexLookupsPasses() {
        $this->smarty->safe_lookups = \Box\Brainy\Brainy::LOOKUP_SAFE;
        $out = $this->smarty->fetch('eval:{$smarty.now}');
        $this->assertEquals($out, time());
    }

    /*
    The tests below test the LOOKUP_SAFE_WARN behavior.
    */

    /**
     * @expectedException PHPUnit_Framework_Error_Warning
     */
    public function testSafeWarnLookupsThrowWarning() {
        $this->expectOutputString('');
        $this->smarty->safe_lookups = \Box\Brainy\Brainy::LOOKUP_SAFE_WARN;
        $this->smarty->display('eval:{$does_not_exist}');
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Warning
     */
    public function testSafeWarnIndexLookupsThrowWarning() {
        $this->expectOutputString('');
        $this->smarty->safe_lookups = \Box\Brainy\Brainy::LOOKUP_SAFE_WARN;
        $this->smarty->assign('foo', array());
        $this->smarty->display('eval:{$foo[0]}');
    }
}
