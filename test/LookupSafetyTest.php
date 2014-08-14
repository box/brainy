<?php
/**
* Brainy lookup safety test suite
*
* @package PHPunit
* @author Matt Basta
*/

class LookupSafetyTest extends PHPUnit_Framework_TestCase
{
    public function setUp() {
        $this->smarty = SmartyTests::$smarty;
        SmartyTests::init();
        // We want to show all errors for this test suite.
        error_reporting(E_ALL);
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Notice
     */
    public function testUnsafeLookupsThrowException() {
        $this->expectOutputString('');
        $this->smarty->safe_lookups = Smarty::LOOKUP_UNSAFE;
        $this->smarty->display('eval:{$does_not_exist}');
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Notice
     */
    public function testUnsafeIndexLookupsThrowException() {
        $this->expectOutputString('');
        $this->smarty->safe_lookups = Smarty::LOOKUP_UNSAFE;
        $this->smarty->assign('foo', array());
        $this->smarty->display('eval:{$foo[0]}');
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Notice
     */
    public function testUnsafeSuperglobalIndexLookupsThrowException() {
        $this->expectOutputString('');
        $this->smarty->safe_lookups = Smarty::LOOKUP_UNSAFE;
        $this->smarty->display('eval:{$smarty.request.this_should_never_exist}');
    }

    /*
    The tests below test the LOOKUP_SAFE behavior.
    */

    public function testSafeLookupsDoNotThrowException() {
        $this->expectOutputString('');
        $this->smarty->safe_lookups = Smarty::LOOKUP_SAFE;
        $this->smarty->display('eval:{$does_not_exist}');
    }

    public function testSafeIndexLookupsDoNotThrowException() {
        $this->expectOutputString('');
        $this->smarty->safe_lookups = Smarty::LOOKUP_SAFE;
        $this->smarty->assign('foo', array());
        $this->smarty->display('eval:{$foo[0]}');
    }

    public function testSafeMemberLookupsInLangConstructsPasses() {
        $this->expectOutputString('it is unset');
        $this->smarty->safe_lookups = Smarty::LOOKUP_SAFE;
        $this->smarty->assign('foo', array());
        $this->smarty->display('string:{if !isset($foo.bar)}it is unset{/if}');
    }

    public function testSafeIndexLookupsInLangConstructsPasses() {
        $this->expectOutputString('it is empty');
        $this->smarty->safe_lookups = Smarty::LOOKUP_SAFE;
        $this->smarty->assign('foo', '');
        $this->smarty->display('string:{if empty($foo)}it is empty{/if}');
    }

    public function testSafeSuperglobalIndexLookupsPasses() {
        $this->expectOutputString('');
        $this->smarty->safe_lookups = Smarty::LOOKUP_SAFE;
        $this->smarty->display('eval:{$smarty.request.this_should_never_exist}');
    }

    /*
    The tests below test the LOOKUP_SAFE_WARN behavior.
    */

    /**
     * @expectedException PHPUnit_Framework_Error_Warning
     */
    public function testSafeWarnLookupsThrowWarning() {
        $this->expectOutputString('');
        $this->smarty->safe_lookups = Smarty::LOOKUP_SAFE_WARN;
        $this->smarty->display('eval:{$does_not_exist}');
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Warning
     */
    public function testSafeWarnIndexLookupsThrowWarning() {
        $this->expectOutputString('');
        $this->smarty->safe_lookups = Smarty::LOOKUP_SAFE_WARN;
        $this->smarty->assign('foo', array());
        $this->smarty->display('eval:{$foo[0]}');
    }
}
