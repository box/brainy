<?php
/**
* Smarty PHPunit tests of modifier
*
* @package PHPunit
* @author Rodney Rehm
*/

namespace Box\Brainy\Tests;


class PluginModifierCountSentencesTest extends Smarty_TestCase
{
    public function setUp() {
        $this->smarty = SmartyTests::$smarty;
        SmartyTests::init();
    }

    public function testDefault() {
        $tpl = $this->smarty->createTemplate('eval:{"hello world."|count_sentences}');
        $this->assertEquals("1", $this->smarty->fetch($tpl));
        $tpl = $this->smarty->createTemplate('eval:{"hello world. I\'m another? Sentence!"|count_sentences}');
        $this->assertEquals("3", $this->smarty->fetch($tpl));
        $tpl = $this->smarty->createTemplate('eval:{"hello world.wrong"|count_sentences}');
        $this->assertEquals("0", $this->smarty->fetch($tpl));
    }

    public function testDefaultWithoutMbstring() {
        \Box\Brainy\Brainy::$_MBSTRING = false;
        $tpl = $this->smarty->createTemplate('eval:{"hello world."|count_sentences}');
        $this->assertEquals("1", $this->smarty->fetch($tpl));
        $tpl = $this->smarty->createTemplate('eval:{"hello world. I\'m another? Sentence!"|count_sentences}');
        $this->assertEquals("3", $this->smarty->fetch($tpl));
        $tpl = $this->smarty->createTemplate('eval:{"hello world.wrong"|count_sentences}');
        $this->assertEquals("0", $this->smarty->fetch($tpl));
        \Box\Brainy\Brainy::$_MBSTRING = true;
    }

    public function testUmlauts() {
        if (preg_match_all('/\w/Su', 'ä') != 1) {
            $this->markTestSkipped('https://github.com/facebook/hhvm/issues/3543');
        }
        $tpl = $this->smarty->createTemplate('eval:{"hello worldä."|count_sentences}');
        $this->assertEquals("1", $this->smarty->fetch($tpl));
        $tpl = $this->smarty->createTemplate('eval:{"hello worldü. ä\'m another? Sentence!"|count_sentences}');
        $this->assertEquals("3", $this->smarty->fetch($tpl));
        $tpl = $this->smarty->createTemplate('eval:{"hello worlä.ärong"|count_sentences}');
        $this->assertEquals("0", $this->smarty->fetch($tpl));
        $tpl = $this->smarty->createTemplate('eval:{"hello worlä.wrong"|count_sentences}');
        $this->assertEquals("0", $this->smarty->fetch($tpl));
        $tpl = $this->smarty->createTemplate('eval:{"hello world.ärong"|count_sentences}');
        $this->assertEquals("0", $this->smarty->fetch($tpl));
    }
}
