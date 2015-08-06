<?php
/**
* Smarty PHPunit tests of modifier
*
* @package PHPunit
* @author Rodney Rehm
*/

namespace Box\Brainy\Tests;


class PluginModifierCapitalizeTest extends Smarty_TestCase
{
    public function setUp() {
        $this->smarty = SmartyTests::$smarty;
        SmartyTests::init();
    }

    public function testDefault() {
        $tpl = $this->smarty->createTemplate('eval:{"next x-men fiLm, x3, delayed. ümlauts äre cööl."|capitalize}');
        $this->assertEquals("Next X-Men FiLm, x3, Delayed. Ümlauts Äre Cööl.", $this->smarty->fetch($tpl));
    }

    public function testDigits() {
        $tpl = $this->smarty->createTemplate('eval:{"next x-men fiLm, x3, delayed. ümlauts äre cööl."|capitalize:true}');
        $this->assertEquals("Next X-Men FiLm, X3, Delayed. Ümlauts Äre Cööl.", $this->smarty->fetch($tpl));
    }

    public function testTrueCaptials() {
        $tpl = $this->smarty->createTemplate('eval:{"next x-men fiLm, x3, delayed. ümlauts äre cööl."|capitalize:true:true}');
        $this->assertEquals("Next X-Men Film, X3, Delayed. Ümlauts Äre Cööl.", $this->smarty->fetch($tpl));
    }

    public function testDefaultWithoutMbstring() {
        \Box\Brainy\Brainy::$_MBSTRING = false;
        $tpl = $this->smarty->createTemplate('eval:{"next x-men fiLm, x3, delayed."|capitalize}');
        $this->assertEquals("Next X-Men FiLm, x3, Delayed.", $this->smarty->fetch($tpl));
        \Box\Brainy\Brainy::$_MBSTRING = true;
    }

    public function testDigitsWithoutMbstring() {
        \Box\Brainy\Brainy::$_MBSTRING = false;
        $tpl = $this->smarty->createTemplate('eval:{"next x-men fiLm, x3, delayed."|capitalize:true}');
        $this->assertEquals("Next X-Men FiLm, X3, Delayed.", $this->smarty->fetch($tpl));
        \Box\Brainy\Brainy::$_MBSTRING = true;
    }

    public function testTrueCaptialsWithoutMbstring() {
        \Box\Brainy\Brainy::$_MBSTRING = false;
        $tpl = $this->smarty->createTemplate('eval:{"next x-men fiLm, x3, delayed."|capitalize:true:true}');
        $this->assertEquals("Next X-Men Film, X3, Delayed.", $this->smarty->fetch($tpl));
        \Box\Brainy\Brainy::$_MBSTRING = true;
    }

    public function testQuotes() {
        $tpl = $this->smarty->createTemplate('eval:{"next x-men fiLm, x3, \"delayed. umlauts\" foo."|capitalize}');
        $this->assertEquals("Next X-Men FiLm, x3, \"Delayed. Umlauts\" Foo.", $this->smarty->fetch($tpl));
        $tpl = $this->smarty->createTemplate('eval:{"next x-men fiLm, x3, \'delayed. umlauts\' foo."|capitalize}');
        $this->assertEquals("Next X-Men FiLm, x3, 'Delayed. Umlauts' Foo.", $this->smarty->fetch($tpl));
    }

    public function testQuotesWithoutMbstring() {
        \Box\Brainy\Brainy::$_MBSTRING = false;
        $tpl = $this->smarty->createTemplate('eval:{"next x-men fiLm, x3, \"delayed. umlauts\" foo."|capitalize}');
        $this->assertEquals("Next X-Men FiLm, x3, \"Delayed. Umlauts\" Foo.", $this->smarty->fetch($tpl));
        $tpl = $this->smarty->createTemplate('eval:{"next x-men fiLm, x3, \'delayed. umlauts\' foo."|capitalize}');
        $this->assertEquals("Next X-Men FiLm, x3, 'Delayed. Umlauts' Foo.", $this->smarty->fetch($tpl));
        \Box\Brainy\Brainy::$_MBSTRING = true;
    }

    public function testQuotesDigits() {
        $tpl = $this->smarty->createTemplate('eval:{"next x-men fiLm, x3, \"delayed. umlauts\" foo."|capitalize:true}');
        $this->assertEquals("Next X-Men FiLm, X3, \"Delayed. Umlauts\" Foo.", $this->smarty->fetch($tpl));
        $tpl = $this->smarty->createTemplate('eval:{"next x-men fiLm, x3, \'delayed. umlauts\' foo."|capitalize:true}');
        $this->assertEquals("Next X-Men FiLm, X3, 'Delayed. Umlauts' Foo.", $this->smarty->fetch($tpl));
    }

    public function testQuotesDigitsWithoutMbstring() {
        \Box\Brainy\Brainy::$_MBSTRING = false;
        $tpl = $this->smarty->createTemplate('eval:{"next x-men fiLm, x3, \"delayed. umlauts\" foo."|capitalize:true}');
        $this->assertEquals("Next X-Men FiLm, X3, \"Delayed. Umlauts\" Foo.", $this->smarty->fetch($tpl));
        $tpl = $this->smarty->createTemplate('eval:{"next x-men fiLm, x3, \'delayed. umlauts\' foo."|capitalize:true}');
        $this->assertEquals("Next X-Men FiLm, X3, 'Delayed. Umlauts' Foo.", $this->smarty->fetch($tpl));
        \Box\Brainy\Brainy::$_MBSTRING = true;
    }

    public function testQuotesTrueCapitals() {
        $tpl = $this->smarty->createTemplate('eval:{"next x-men fiLm, x3, \"delayed. umlauts\" foo."|capitalize:true:true}');
        $this->assertEquals("Next X-Men Film, X3, \"Delayed. Umlauts\" Foo.", $this->smarty->fetch($tpl));
        $tpl = $this->smarty->createTemplate('eval:{"next x-men fiLm, x3, \'delayed. umlauts\' foo."|capitalize:true:true}');
        $this->assertEquals("Next X-Men Film, X3, 'Delayed. Umlauts' Foo.", $this->smarty->fetch($tpl));
    }

    public function testQuotesTrueCapitalsWithoutMbstring() {
        \Box\Brainy\Brainy::$_MBSTRING = false;
        $tpl = $this->smarty->createTemplate('eval:{"next x-men fiLm, x3, \"delayed. umlauts\" foo."|capitalize:true:true}');
        $this->assertEquals("Next X-Men Film, X3, \"Delayed. Umlauts\" Foo.", $this->smarty->fetch($tpl));
        $tpl = $this->smarty->createTemplate('eval:{"next x-men fiLm, x3, \'delayed. umlauts\' foo."|capitalize:true:true}');
        $this->assertEquals("Next X-Men Film, X3, 'Delayed. Umlauts' Foo.", $this->smarty->fetch($tpl));
        \Box\Brainy\Brainy::$_MBSTRING = true;
    }

    public function testQuotesBeginning() {
        $tpl = $this->smarty->createTemplate('eval:{"\"delayed. umlauts\" foo."|capitalize}');
        $this->assertEquals("\"Delayed. Umlauts\" Foo.", $this->smarty->fetch($tpl));
        $tpl = $this->smarty->createTemplate('eval:{"\'delayed. umlauts\' foo."|capitalize}');
        $this->assertEquals("'Delayed. Umlauts' Foo.", $this->smarty->fetch($tpl));
    }

    public function testQuotesBeginningWithoutMbstring() {
        \Box\Brainy\Brainy::$_MBSTRING = false;
        $tpl = $this->smarty->createTemplate('eval:{"\"delayed. umlauts\" foo."|capitalize}');
        $this->assertEquals("\"Delayed. Umlauts\" Foo.", $this->smarty->fetch($tpl));
        $tpl = $this->smarty->createTemplate('eval:{"\'delayed. umlauts\' foo."|capitalize}');
        $this->assertEquals("'Delayed. Umlauts' Foo.", $this->smarty->fetch($tpl));
        \Box\Brainy\Brainy::$_MBSTRING = true;
    }
}
