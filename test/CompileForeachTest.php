<?php
/**
* Smarty PHPunit tests compilation of {foreach} tag
*
* @package PHPunit
* @author Uwe Tews
*/

/**
* class for {foreach} tag tests
*/
class CompileForeachTest extends PHPUnit_Framework_TestCase
{
    public function setUp() {
        $this->smarty = SmartyTests::$smarty;
        SmartyTests::init();
        $this->smarty->safe_lookups = Smarty::LOOKUP_SAFE;
    }

    /**
    * test {foreach} tag
    */
    public function testForeach() {
        $tpl = $this->smarty->createTemplate('eval:{assign var=foo value=[0,1,2,3,4,5,6,7,8, 9]}{foreach item=x from=$foo}{$x}{/foreach}');
        $this->assertEquals("0123456789", $this->smarty->fetch($tpl));
    }
    public function testForeachBreak() {
        $tpl = $this->smarty->createTemplate('eval:{assign var=foo value=[0,1,2,3,4,5,6,7,8, 9]}{foreach item=x from=$foo}{if $x == 2}{break}{/if}{$x}{/foreach}');
        $this->assertEquals("01", $this->smarty->fetch($tpl));
    }
    public function testForeachContinue() {
        $tpl = $this->smarty->createTemplate('eval:{assign var=foo value=[0,1,2,3,4,5,6,7,8, 9]}{foreach item=x from=$foo}{if $x == 2}{continue}{/if}{$x}{/foreach}');
        $this->assertEquals("013456789", $this->smarty->fetch($tpl));
    }
    public function testForeachNotElse() {
        $tpl = $this->smarty->createTemplate('eval:{assign var=foo value=[0,1,2,3,4,5,6,7,8, 9]}{foreach item=x from=$foo}{$x}{foreachelse}else{/foreach}');
        $this->assertEquals("0123456789", $this->smarty->fetch($tpl));
    }
    public function testForeachElse() {
        $tpl = $this->smarty->createTemplate('eval:{foreach item=x from=$foo}{$x}{foreachelse}else{/foreach}');
        $this->assertEquals("else", $this->smarty->fetch($tpl));
    }
    public function testForeachKey() {
        $tpl = $this->smarty->createTemplate('eval:{foreach item=x key=y from=[9,8,7,6,5,4,3,2,1, 0]}{$y}{$x}{foreachelse}else{/foreach}');
        $this->assertEquals("09182736455463728190", $this->smarty->fetch($tpl));
    }
    public function testForeachKeyProperty() {
        $tpl = $this->smarty->createTemplate('eval:{foreach item=x from=[9,8,7,6,5,4,3,2,1, 0]}{$x@key}{$x}{foreachelse}else{/foreach}');
        $this->assertEquals("09182736455463728190", $this->smarty->fetch($tpl));
    }
    public function testForeachTotal() {
        $tpl = $this->smarty->createTemplate('eval:{foreach item=x name=foo from=[0,1,2,3,4,5,6,7,8, 9]}{$x}{foreachelse}else{/foreach}total{$smarty.foreach.foo.total}');
        $this->assertEquals("0123456789total10", $this->smarty->fetch($tpl));
    }
    public function testForeachTotalProperty() {
        $tpl = $this->smarty->createTemplate('eval:{foreach item=x from=[0,1,2,3,4,5,6,7,8, 9]}{$x}{foreachelse}else{/foreach}total{$x@total}');
        $this->assertEquals("0123456789total10", $this->smarty->fetch($tpl));
    }
    public function testForeachIndexIteration() {
        $tpl = $this->smarty->createTemplate('eval:{foreach item=x name=foo from=[0,1,2,3,4,5,6,7,8, 9]}{$smarty.foreach.foo.index}{$smarty.foreach.foo.iteration}{foreachelse}else{/foreach}');
        $this->assertEquals("011223344556677889910", $this->smarty->fetch($tpl));
    }
    public function testForeachIndexIterationProperty() {
        $tpl = $this->smarty->createTemplate('eval:{foreach item=x from=[0,1,2,3,4,5,6,7,8, 9]}{$x@index}{$x@iteration}{foreachelse}else{/foreach}');
        $this->assertEquals("011223344556677889910", $this->smarty->fetch($tpl));
    }
    public function testForeachFirstLast() {
        $tpl = $this->smarty->createTemplate('eval:{foreach item=x name=foo from=[0,1,2,3,4,5,6,7,8, 9]}{if $smarty.foreach.foo.first}first{/if}{if $smarty.foreach.foo.last}last{/if}{$x}{foreachelse}else{/foreach}');
        $this->assertEquals("first012345678last9", $this->smarty->fetch($tpl));
    }
    public function testForeachFirstLastProperty() {
        $tpl = $this->smarty->createTemplate('eval:{foreach item=x name=foo from=[0,1,2,3,4,5,6,7,8, 9]}{if $x@first}first{/if}{if $x@last}last{/if}{$x}{foreachelse}else{/foreach}');
        $this->assertEquals("first012345678last9", $this->smarty->fetch($tpl));
    }
    public function testForeachShowTrue() {
        $tpl = $this->smarty->createTemplate('eval:{foreach item=x name=foo from=[0, 1]}{$x}{foreachelse}else{/foreach}{if $smarty.foreach.foo.show}show{else}noshow{/if}');
        $this->assertEquals("01show", $this->smarty->fetch($tpl));
    }
    public function testForeachShowTrueProperty() {
        $tpl = $this->smarty->createTemplate('eval:{foreach item=x name=foo from=[0, 1]}{$x}{foreachelse}else{/foreach}{if $x@show}show{else}noshow{/if}');
        $this->assertEquals("01show", $this->smarty->fetch($tpl));
    }
    public function testForeachShowFalse() {
        $tpl = $this->smarty->createTemplate('eval:{foreach item=x name=foo from=[]}{$x}{foreachelse}else{/foreach}{if $smarty.foreach.foo.show}show{else} noshow{/if}');
        $this->assertEquals("else noshow", $this->smarty->fetch($tpl));
    }
    public function testForeachShowFalseProperty() {
        $tpl = $this->smarty->createTemplate('eval:{foreach item=x name=foo from=[]}{$x}{foreachelse}else{/foreach}{if $x@show}show{else} noshow{/if}');
        $this->assertEquals("else noshow", $this->smarty->fetch($tpl));
    }
    public function testForeachShorttags() {
        $tpl = $this->smarty->createTemplate('eval:{foreach [9,8,7,6,5,4,3,2,1, 0] x y foo}{$y}{$x}{foreachelse}else{/foreach}total{$smarty.foreach.foo.total}');
        $this->assertEquals("09182736455463728190total10", $this->smarty->fetch($tpl));
    }
    /**
    * test {foreach $foo as $x} tag
    */
    public function testNewForeach() {
        $tpl = $this->smarty->createTemplate('eval:{assign var=foo value=[0,1,2,3,4,5,6,7,8, 9]}{foreach $foo as $x}{$x}{/foreach}');
        $this->assertEquals("0123456789", $this->smarty->fetch($tpl));
    }
    public function testNewForeachNotElse() {
        $tpl = $this->smarty->createTemplate('eval:{assign var=foo value=[0,1,2,3,4,5,6,7,8, 9]}{foreach $foo as $x}{$x}{foreachelse}else{/foreach}');
        $this->assertEquals("0123456789", $this->smarty->fetch($tpl));
    }
    public function testNewForeachElse() {
        $tpl = $this->smarty->createTemplate('eval:{foreach $foo as $x}{$x}{foreachelse}else{/foreach}');
        $this->assertEquals("else", $this->smarty->fetch($tpl));
    }
    public function testNewForeachKey() {
        $tpl = $this->smarty->createTemplate('eval:{assign var=foo value=[9,8,7,6,5,4,3,2,1, 0]}{foreach $foo as $y=>$x}{$y}{$x}{foreachelse}else{/foreach}');
        $this->assertEquals("09182736455463728190", $this->smarty->fetch($tpl));
    }
    public function testNewForeachKeyProperty() {
        $tpl = $this->smarty->createTemplate('eval:{assign var=foo value=[9,8,7,6,5,4,3,2,1, 0]}{foreach $foo as $x}{$x@key}{$x}{foreachelse}else{/foreach}');
        $this->assertEquals("09182736455463728190", $this->smarty->fetch($tpl));
    }
}
