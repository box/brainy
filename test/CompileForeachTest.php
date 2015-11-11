<?php
namespace Box\Brainy\Tests;


class CompileForeachTest extends Smarty_TestCase
{
    public function setUp() {
        parent::setUp();
        $this->smarty->safe_lookups = \Box\Brainy\Brainy::LOOKUP_SAFE;
    }

    public function dataProviderForForEachLoops()
    {
        return array(
            array('{assign var=foo value=[0,1,2,3,4,5,6,7,8,9]}{foreach item=x from=$foo}{$x}{/foreach}', "0123456789"),
            array('{assign var=foo value=[0,1,2,3,4,5,6,7,8,9]}{foreach item=x from=$foo}{if $x == 2}{break}{/if}{$x}{/foreach}', "01"),
            array('{assign var=foo value=[0,1,2,3,4,5,6,7,8,9]}{foreach item=x from=$foo}{if $x == 2}{continue}{/if}{$x}{/foreach}', "013456789"),
            array('{assign var=foo value=[0,1,2,3,4,5,6,7,8,9]}{foreach item=x from=$foo}{$x}{foreachelse}else{/foreach}', "0123456789"),
            array('{foreach item=x from=$foo}{$x}{foreachelse}else{/foreach}', "else"),
            array('{foreach item=x key=y from=[9,8,7,6,5,4,3,2,1,0]}{$y}{$x}{foreachelse}else{/foreach}', "09182736455463728190"),
            array('{foreach item=x name=foo from=[0,1,2,3,4,5,6,7,8,9]}{$x}{foreachelse}else{/foreach}total{$smarty.foreach.foo.total}', "0123456789total10"),
            array('{foreach item=x name=foo from=[0,1,2,3,4,5,6,7,8,9]}{$smarty.foreach.foo.index}{$smarty.foreach.foo.iteration}{foreachelse}else{/foreach}', "011223344556677889910"),
            array('{foreach item=x name=foo from=[0,1,2,3,4,5,6,7,8,9]}{if $smarty.foreach.foo.first}first{/if}{if $smarty.foreach.foo.last}last{/if}{$x}{foreachelse}else{/foreach}', "first012345678last9"),
            array('{foreach item=x name=foo from=[0,1]}{$x}{foreachelse}else{/foreach}{if $smarty.foreach.foo.show}show{else}noshow{/if}', "01show"),
            array('{foreach item=x name=foo from=[]}{$x}{foreachelse}else{/foreach}{if $smarty.foreach.foo.show}show{else} noshow{/if}', "else noshow"),
            array('{assign var=foo value=[0,1,2,3,4,5,6,7,8,9]}{foreach $foo as $x}{$x}{/foreach}', "0123456789"),
            array('{assign var=foo value=[0,1,2,3,4,5,6,7,8,9]}{foreach $foo as $x}{$x}{foreachelse}else{/foreach}', "0123456789"),
            array('{foreach $foo as $x}{$x}{foreachelse}else{/foreach}', "else"),
            array('{assign var=foo value=[9,8,7,6,5,4,3,2,1,0]}{foreach $foo as $y=>$x}{$y}{$x}{foreachelse}else{/foreach}', "09182736455463728190"),
        );
    }

    /**
     * @dataProvider dataProviderForForEachLoops
     */
    public function testForEachLoops($template, $expected)
    {
        $this->assertEquals($expected, $this->smarty->fetch('eval:' . $template));
    }
}
