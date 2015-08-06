<?php
/**
* Smarty PHPunit tests of modifier
*
* @package PHPunit
* @author Rodney Rehm
*/

namespace Box\Brainy\Tests;


class PluginModifierCharsetTest extends Smarty_TestCase
{
    public function setUp() {
        $this->smarty = SmartyTests::$smarty;
        SmartyTests::init();
    }

    public function testToLatin1() {
        $encoded = "hällö wörld 1";
        $result = utf8_decode($encoded);
        $tpl = $this->smarty->createTemplate('eval:{"' . $encoded . '"|to_charset}');
        $this->assertEquals($result, $tpl->fetch());
    }

    public function testToLatin1WithoutMbstring() {
        \Box\Brainy\Brainy::$_MBSTRING = false;
        $encoded = "hällö wörld 2";
        $result = utf8_decode($encoded);
        $tpl = $this->smarty->createTemplate('eval:{"' . $encoded . '"|to_charset}');
        $this->assertEquals($encoded, $tpl->fetch());
        \Box\Brainy\Brainy::$_MBSTRING = true;
    }

    public function testFromLatin1() {
        $result = "hällö wörld 3";
        $encoded = utf8_decode($result);
        $tpl = $this->smarty->createTemplate('eval:{"' . $encoded . '"|from_charset}');
        $this->assertEquals($result, $tpl->fetch());
    }

    public function testFromLatin1WithoutMbstring() {
        \Box\Brainy\Brainy::$_MBSTRING = false;
        $result = "hällö wörld 4";
        $encoded = utf8_decode($result);
        $tpl = $this->smarty->createTemplate('eval:{"' . $encoded . '"|from_charset}');
        $this->assertEquals($encoded, $tpl->fetch());
        \Box\Brainy\Brainy::$_MBSTRING = true;
    }

    public function testFromUtf32le() {
        $result = "hällö wörld 5";
        $encoded = mb_convert_encoding($result, "UTF-32LE", "UTF-8");
        $tpl = $this->smarty->createTemplate('eval:{"' . $encoded . '"|from_charset:"UTF-32LE"}');
        $this->assertEquals($result, $tpl->fetch());
    }

    public function testFromUtf32leWithoutMbstring() {
        \Box\Brainy\Brainy::$_MBSTRING = false;
        $result = "hällö wörld 6";
        $encoded = mb_convert_encoding($result, "UTF-32LE", "UTF-8");
        $tpl = $this->smarty->createTemplate('eval:{"' . $encoded . '"|from_charset:"UTF-32LE"}');
        $this->assertEquals($encoded, $tpl->fetch());
        \Box\Brainy\Brainy::$_MBSTRING = true;
    }

    public function testToUtf32le() {
        $encoded = "hällö wörld 7";
        $result = mb_convert_encoding($encoded, "UTF-32LE", "UTF-8");
        $tpl = $this->smarty->createTemplate('eval:{"' . $encoded . '"|to_charset:"UTF-32LE"}');
        $this->assertEquals($result, $tpl->fetch());
    }

    public function testToUtf32leWithoutMbstring() {
        \Box\Brainy\Brainy::$_MBSTRING = false;
        $encoded = "hällö wörld 8";
        $result = mb_convert_encoding($encoded, "UTF-32LE", "UTF-8");
        $tpl = $this->smarty->createTemplate('eval:{"' . $encoded . '"|to_charset:"UTF-32LE"}');
        $this->assertEquals($encoded, $tpl->fetch());
        \Box\Brainy\Brainy::$_MBSTRING = true;
    }
}
