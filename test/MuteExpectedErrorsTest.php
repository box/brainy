<?php
/**
 * Smarty PHPunit tests of filter
 *
 * @package PHPunit
 * @author Rodney Rehm
 */

/**
 * class for filter tests
 */
class MuteExpectedErrorsTest extends PHPUnit_Framework_TestCase
{

    private $_errors;

    public function setUp() {
        $this->smarty = SmartyTests::$smarty;
        $this->smartyBC = SmartyTests::$smartyBC;
        SmartyTests::init();
        $this->smarty->safe_lookups = Smarty::LOOKUP_SAFE;
        $this->smartyBC->safe_lookups = Smarty::LOOKUP_SAFE;

        $this->_errors = array();
        set_error_handler(array($this, 'error_handler'));

    }
    protected function tearDown() {
        restore_error_handler();

        $this->smarty->clearAllCache();
        $this->smartyBC->clearAllCache();
    }

    public function error_handler($errno, $errstr, $errfile, $errline, $errcontext) {
        $this->_errors[] = $errfile .' line ' . $errline;
    }

    public function testMuted() {
        Smarty::muteExpectedErrors();

        $this->smarty->clearCache('default.tpl');
        $this->smarty->clearCompiledTemplate('default.tpl');
        $this->smarty->fetch('default.tpl');

        $this->assertLessThanOrEqual(2, count($this->_errors));

        Smarty::unmuteExpectedErrors();
    }

    public function testUnmuted() {
        $this->smarty->clearCache('default.tpl');
        $this->smarty->clearCompiledTemplate('default.tpl');
        $this->smarty->fetch('default.tpl');

        $this->assertLessThanOrEqual(Smarty::$_IS_WINDOWS ? 5 : 2, count($this->_errors));

        @filemtime('ckxladanwijicajscaslyxck');
        $error = array( __FILE__ . ' line ' . (__LINE__ -1));
        $this->assertLessThanOrEqual(Smarty::$_IS_WINDOWS ? 6 : 3, count($this->_errors));
    }

    public function testMutedCaching() {
        Smarty::muteExpectedErrors();

        $this->smarty->caching = true;
        $this->smarty->clearCache('default.tpl');
        $this->smarty->clearCompiledTemplate('default.tpl');
        $this->smarty->fetch('default.tpl');

        $this->assertLessThanOrEqual(1, count($this->_errors));
        Smarty::unmuteExpectedErrors();
    }

    public function testUnmutedCaching() {
        $this->smarty->caching = true;
        $this->smarty->clearCache('default.tpl');
        $this->smarty->clearCompiledTemplate('default.tpl');
        $this->smarty->fetch('default.tpl');

        $this->assertLessThanOrEqual(Smarty::$_IS_WINDOWS ? 7 : 4, count($this->_errors));

        @filemtime('ckxladanwijicajscaslyxck');
        $error = array( __FILE__ . ' line ' . (__LINE__ -1));
        $this->assertLessThanOrEqual(Smarty::$_IS_WINDOWS ? 8 : 5, count($this->_errors));
    }
}
