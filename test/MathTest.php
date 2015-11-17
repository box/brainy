<?php

namespace Box\Brainy\Tests;


class MathTest extends Smarty_TestCase
{

    public function dataProviderForMaths()
    {
        return array(
            array('3', '{1+2}'),
            array('2', '{1*2}'),
            array('7', '{3*2+1}'),
            array('7', '{1+3*2}'),
            array('-5', '{1+3*-2}'),
        );
    }

    /**
     * @dataProvider dataProviderForMaths
     */
    public function testMaths($expected, $code)
    {
        $this->assertEquals($expected, $this->smarty->fetch('eval:' . $code));
    }
}
