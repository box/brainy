<?php
/**
* Smarty PHPunit tests of modifier
*
* @package PHPunit
* @author Rodney Rehm
*/

namespace Box\Brainy\Tests;


class PluginModifierStripTest extends Smarty_TestCase
{
    public function testDefault() {
        $tpl = $this->smarty->createTemplate('eval:{" hello     spaced words  "|strip}');
        $this->assertEquals(" hello spaced words ", $this->smarty->fetch($tpl));
    }

    public function testUnicodeSpaces() {
        // Some Unicode Spaces
        $string = "&#8199;hello      spaced&#8196; &#8239;  &#8197;&#8199;  words  ";
        $string = mb_convert_encoding($string, 'UTF-8', "HTML-ENTITIES");
        if (iconv_strlen(preg_replace('!\s+!u', '', $string)) > 30) {
            $this->markTestSkipped('https://github.com/facebook/hhvm/issues/3542');
        }
        $tpl = $this->smarty->createTemplate('eval:{"' . $string . '"|strip}');
        $this->assertEquals(" hello spaced words ", $this->smarty->fetch($tpl));
    }

    public function testLinebreak() {
        $tpl = $this->smarty->createTemplate('eval:{" hello
            spaced words  "|strip}');
        $this->assertEquals(" hello spaced words ", $this->smarty->fetch($tpl));
    }
}
