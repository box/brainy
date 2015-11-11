<?php
namespace Box\Brainy\Tests;


class CompileForTest extends Smarty_TestCase
{

    public function dataProviderForForLoops()
    {
        return array(
            array('{for $x=0;$x<10;$x++}{$x}{/for}', "0123456789"),
            array('{for $x=0; $x<10; $x++}{$x}{forelse}else{/for}', "0123456789"),
            array('{for $x=10;$x<10;$x++}{$x}{forelse}else{/for}', "else"),
            array('{for $x=9;$x>=0;$x--}{$x}{forelse}else{/for}', "9876543210"),
            array('{for $x=-1;$x>=0;$x--}{$x}{forelse}else{/for}', "else"),
            array('{for $x=0,$y=10;$x<$y;$x++}{$x}{forelse}else{/for}', "0123456789"),
            array('{for $x=0;$x<10;$x=$x+2}{$x}{/for}', "02468"),
            array('{for $x=0 to 8}{$x}{/for}', "012345678"),
            array('{for $x=0 to 8 step=2}{$x}{/for}', "02468"),
            array('{for $x=8 to 0 step=-2}{$x}{/for}', "86420"),
            array('{for $x=8 to 0 step=2}{$x}{forelse}step error{/for}', "step error"),
            array('{for $x=8 to 0 step -1 max=3}{$x}{/for}', "876"),
        );
    }

    /**
     * @dataProvider dataProviderForForLoops
     */
    public function testForLoops($template, $expected)
    {
        $this->assertEquals($expected, $this->smarty->fetch('eval:' . $template));
    }
}
