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
        return $this->smarty->fetch('eval:{$does_not_exist}');
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Notice
     */
    public function testUnsafeIndexLookupsThrowException() {
        $this->expectOutputString('');
        $this->smarty->safe_lookups = Smarty::LOOKUP_UNSAFE;
        $this->smarty->assign('foo', array());
        return $this->smarty->fetch('eval:{$foo[0]}');
    }

    /*
    The tests below test the LOOKUP_SAFE behavior.
    */

    public function testSafeLookupsDoNotThrowException() {
        $this->expectOutputString('');
        $this->smarty->safe_lookups = Smarty::LOOKUP_SAFE;
        return $this->smarty->fetch('eval:{$does_not_exist}');
    }

    public function testSafeIndexLookupsDoNotThrowException() {
        $this->expectOutputString('');
        $this->smarty->safe_lookups = Smarty::LOOKUP_SAFE;
        $this->smarty->assign('foo', array());
        return $this->smarty->fetch('eval:{$foo[0]}');
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
        return $this->smarty->fetch('eval:{$does_not_exist}');
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Warning
     */
    public function testSafeWarnIndexLookupsThrowWarning() {
        $this->expectOutputString('');
        $this->smarty->safe_lookups = Smarty::LOOKUP_SAFE_WARN;
        $this->smarty->assign('foo', array());
        return $this->smarty->fetch('eval:{$foo[0]}');
    }
}
