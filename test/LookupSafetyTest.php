<?php
/**
 * Brainy lookup safety test suite
 *
 * @package PHPunit
 * @author Matt Basta
 */

namespace Box\Brainy\Tests;

use RuntimeException;

class LookupSafetyTest extends Smarty_TestCase
{

    public function setup(): void
    {
        parent::setUp();
        error_reporting(E_ALL);
    }

    public function testUnsafeLookupsThrowException()
    {
        if (PHP_VERSION < 80000) {
            $this->expectNotice('Undefined array key');
        } else {
            // As of PHP8, these are warnings
            $this->expectWarning('Undefined array key');
        }
        $this->expectOutputString('');
        $this->smarty->safe_lookups = \Box\Brainy\Brainy::LOOKUP_UNSAFE;
        $this->smarty->display('eval:{$does_not_exist}');
    }

    public function testUnsafeIndexLookupsThrowException() {
        if (PHP_VERSION < 80000) {
            $this->expectNotice('Undefined array key');
        } else {
            // As of PHP8, these are warnings
            $this->expectWarning('Undefined array key');
        }
        $this->expectOutputString('');
        $this->smarty->safe_lookups = \Box\Brainy\Brainy::LOOKUP_UNSAFE;
        $this->smarty->assign('foo', array());
        $this->smarty->display('eval:{$foo[0][0]}');
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

    public function testSafeWarnLookupsThrowWarning()
    {
        $this->expectWarning();
        $this->expectOutputString('');
        $this->smarty->safe_lookups = \Box\Brainy\Brainy::LOOKUP_SAFE_WARN;
        $this->smarty->display('eval:{$does_not_exist}');
    }

    public function testSafeWarnIndexLookupsThrowWarning()
    {
        $this->expectWarning();
        $this->expectOutputString('');
        $this->smarty->safe_lookups = \Box\Brainy\Brainy::LOOKUP_SAFE_WARN;
        $this->smarty->assign('foo', array());
        $this->smarty->display('eval:{$foo[0][0]}');
    }
}
