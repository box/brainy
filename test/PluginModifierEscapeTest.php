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

    public function testJavascript() {
        $tpl = $this->smarty->createTemplate('eval:{"var x = { foo : \"bar\n\" };"|escape:"javascript"}');
        $this->assertEquals("var x = { foo : \\\"bar\\n\\\" };", $this->smarty->fetch($tpl));
    }

}
