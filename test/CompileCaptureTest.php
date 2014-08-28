<?php
/**
 * Smarty PHPunit tests compilation of capture tags
 *
 * @package PHPunit
 * @author Uwe Tews
 */

/**
* class for capture tags tests
*/
class CompileCaptureTest extends PHPUnit_Framework_TestCase
{
    public function setUp() {
        $this->smarty = SmartyTests::$smarty;
        SmartyTests::init();
    }
    public function tearDown() {
        Smarty::$assignment_compat = Smarty::ASSIGN_COMPAT;
        Smarty::$enforce_expression_modifiers = [];
    }

    /**
    * test capture tag
    */
    public function testCapture1() {
        $tpl = $this->smarty->createTemplate('eval:{capture assign=foo}hello world{/capture}');
        $this->assertEquals("", $this->smarty->fetch($tpl));
    }
    public function testCapture2() {
        $tpl = $this->smarty->createTemplate('eval:{assign var=foo value=bar}{capture assign=foo}hello world{/capture}{$foo}');
        $this->assertEquals("hello world", $this->smarty->fetch($tpl));
    }
    public function testCapture3() {
        $tpl = $this->smarty->createTemplate('eval:{capture name=foo}hello world{/capture}{$smarty.capture.foo}');
        $this->assertEquals("hello world", $this->smarty->fetch($tpl));
    }
    public function testCapture4() {
        $tpl = $this->smarty->createTemplate('eval:{capture name=foo assign=bar}hello world{/capture}{$smarty.capture.foo} {$bar}');
        $this->assertEquals("hello world hello world", $this->smarty->fetch($tpl));
    }
    public function testCapture5() {
        $tpl = $this->smarty->createTemplate('eval:{capture}hello world{/capture}{$smarty.capture.default}');
        $this->assertEquals("hello world", $this->smarty->fetch($tpl));
    }
    public function testCapture6() {
        $tpl = $this->smarty->createTemplate('eval:{capture short}hello shorttag{/capture}{$smarty.capture.short}');
        $this->assertEquals("hello shorttag", $this->smarty->fetch($tpl));
    }
    public function testCapture7() {
        $tpl = $this->smarty->createTemplate('eval:{capture append=foo}hello{/capture}bar{capture append=foo}world{/capture}{foreach $foo item} {$item@key} {$item}{/foreach}');
        $this->assertEquals("bar 0 hello 1 world", $this->smarty->fetch($tpl));
    }
    public function testCapture8() {
        $tpl = $this->smarty->createTemplate('eval:{capture assign=foo}hello {capture assign=bar}this is my {/capture}world{/capture}{$foo} {$bar}');
        $this->assertEquals("hello world this is my ", $this->smarty->fetch($tpl));
    }

    public function dataProviderSwitch()
    {
        return array(
            array(true, '<success>', null),
            array(false, '<fail>', null),
            array(true, '<success>', array('escape')),
            array(false, '<fail>', array('escape')),
        );
    }

    /**
     * @dataProvider dataProviderSwitch
     */
    public function testConditionalsInCapture($x, $result, $modifiers)
    {
        Smarty::$enforce_expression_modifiers = $modifiers;
        $this->smarty->assign('x', $x);
        $this->assertEquals($result, $this->smarty->fetch('eval:{capture assign="foo"}{if $x && true}success{else}fail{/if}{/capture}<{$foo|escape}>'));
    }

    /**
     * @dataProvider dataProviderSwitch
     */
    public function testStripAroundCapture($x, $result, $modifiers)
    {
        Smarty::$enforce_expression_modifiers = $modifiers;
        $this->smarty->assign('x', $x);
        $this->assertEquals($result, $this->smarty->fetch('eval:{strip}{capture assign="foo"}{if $x && true}success{else}fail{/if}{/capture}<{$foo|escape}>{/strip}'));
    }

    /**
     * @dataProvider dataProviderSwitch
     */
    public function testMultipleStripsAroundCapture($x, $result, $modifiers)
    {
        Smarty::$enforce_expression_modifiers = $modifiers;
        $this->smarty->assign('x', $x);
        $this->assertEquals($result, $this->smarty->fetch('eval:{strip}{strip}{capture assign="foo"}{if $x && true}success{else}fail{/if}{/capture}<{$foo|escape}>{/strip}{/strip}'));
    }

    public function testNumericStringsInConditionalInCapture()
    {
        $this->smarty->assign('x', true);
        $this->assertEquals('<1>', $this->smarty->fetch('eval:{capture assign="foo"}{if $x}1{else}0{/if}{/capture}<{$foo}>'));
    }

}
