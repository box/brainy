<?php
/**
 * Smarty PHPunit tests of modifier
 *
 * @package PHPunit
 * @author Rodney Rehm
 */

namespace Box\Brainy\Tests;


class PluginModifierEscapeTest extends Smarty_TestCase
{

    protected function hhvmBugTest() {
        if (htmlentities('ä', ENT_QUOTES) === '') {
            $this->markTestSkipped('https://github.com/facebook/hhvm/issues/2266');
        }
    }

    public function testHtml() {
        $tpl = $this->smarty->createTemplate('eval:{"I\'m some <html> to ä be \"escaped\" or &copy;"|escape:"html"}');
        $this->assertEquals("I&#039;m some &lt;html&gt; to ä be &quot;escaped&quot; or &amp;copy;", $this->smarty->fetch($tpl));
    }

    public function testHtmlDouble() {
        $tpl = $this->smarty->createTemplate('eval:{"I\'m some <html> to ä be \"escaped\" or &copy;"|escape:"html":null:false}');
        $this->assertEquals("I&#039;m some &lt;html&gt; to ä be &quot;escaped&quot; or &copy;", $this->smarty->fetch($tpl));
    }

    public function testUrl() {
        $tpl = $this->smarty->createTemplate('eval:{"http://some.encoded.com/url?parts#foo"|escape:"url"}');
        $this->assertEquals("http%3A%2F%2Fsome.encoded.com%2Furl%3Fparts%23foo", $this->smarty->fetch($tpl));
    }

    public function testUrlpathinfo() {
        $tpl = $this->smarty->createTemplate('eval:{"http://some.encoded.com/url?parts#foo"|escape:"urlpathinfo"}');
        $this->assertEquals("http%3A//some.encoded.com/url%3Fparts%23foo", $this->smarty->fetch($tpl));
    }

    public function testHex() {
        $tpl = $this->smarty->createTemplate('eval:{"a/cäa"|escape:"hex"}');
        $this->assertEquals("%61%2f%63%c3%a4%61", $this->smarty->fetch($tpl));
    }

    public function testHexentity() {
        $q = "a&#228;&#1047;&#1076;&#1088;&#1072;&#1074;&#1089;&#1089;&#1090;&#1074;&#1091;&#1081;&#1090;&#1077;";
        $r = html_entity_decode($q, ENT_NOQUOTES, 'UTF-8');
        $tpl = $this->smarty->createTemplate('eval:{"' . $r . '"|escape:"hexentity"}');
        $this->assertEquals("&#x61;&#xE4;&#x417;&#x434;&#x440;&#x430;&#x432;&#x441;&#x441;&#x442;&#x432;&#x443;&#x439;&#x442;&#x435;", $this->smarty->fetch($tpl));

        $tpl = $this->smarty->createTemplate('eval:{"abc"|escape:"hexentity"}');
        $this->assertEquals("&#x61;&#x62;&#x63;", $this->smarty->fetch($tpl));
    }

    public function testDecentity() {
        $q = "a&#228;&#1047;&#1076;&#1088;&#1072;&#1074;&#1089;&#1089;&#1090;&#1074;&#1091;&#1081;&#1090;&#1077;";
        $r = html_entity_decode($q, ENT_NOQUOTES, 'UTF-8');
        $tpl = $this->smarty->createTemplate('eval:{"' . $r . '"|escape:"decentity"}');
        $this->assertEquals("&#97;&#228;&#1047;&#1076;&#1088;&#1072;&#1074;&#1089;&#1089;&#1090;&#1074;&#1091;&#1081;&#1090;&#1077;", $this->smarty->fetch($tpl));

        $tpl = $this->smarty->createTemplate('eval:{"abc"|escape:"decentity"}');
        $this->assertEquals("&#97;&#98;&#99;", $this->smarty->fetch($tpl));
    }

    public function testJavascript() {
        $tpl = $this->smarty->createTemplate('eval:{"var x = { foo : \"bar\n\" };"|escape:"javascript"}');
        $this->assertEquals("var x = { foo : \\\"bar\\n\\\" };", $this->smarty->fetch($tpl));
    }

    public function testMail() {
        $tpl = $this->smarty->createTemplate('eval:{"smarty@example.com"|escape:"mail"}');
        $this->assertEquals("smarty [AT] example [DOT] com", $this->smarty->fetch($tpl));
    }

    public function testNonstd() {
        $tpl = $this->smarty->createTemplate('eval:{"sma\'rty|»example«.com"|escape:"nonstd"}');
        $this->assertEquals("sma'rty|&#187;example&#171;.com", $this->smarty->fetch($tpl));
    }

}
