<?php

namespace Box\Brainy\Tests;


class ArrayTest extends Smarty_TestCase
{

    public function dataProviderForArrays()
    {
        return array(
            // Array syntax tests
            array('12345', '{$foo=[1,2,3,4,5]}{foreach $foo as $bar}{$bar}{/foreach}'),
            array('123', '{$foo=[1,2,3,4,5]}{$foo.0}{$foo.1}{$foo.2}'),
            array('123', '{$foo=[1,2,3,4, 5]}{$foo[0]}{$foo[1]}{$foo[2]}'),
            array('1234', '{$x="d"}{$foo=[a=>1,"b"=>2,"c"=>3,$x=>4]}{$foo["a"]}{$foo["b"]}{$foo["c"]}{$foo["d"]}'),
            array('b', '{$foo=[1,2,[a,b,c],4,5]}{$foo[2][1]}'),

            // Parse tests to exercise the grammar
            array('', '{$foo=[1,2,[7,8,9],4,5]}'),

            // Lookup tests
            array('1', '{$foo=[1,2,[7,8,9],4,5]}{$foo[0]}'),
            array('8', '{$foo=[1,2,[7,8,9],4,5]}{$foo[2][1]}'),

            array('9', '{$foo=[1,2,[7,8,9],4,5]}{$foo[2][1]+1}'),

            array('9', '{$foo=[1,2,[7,8,9],4,5]}{$foo.2.1+1}'),
            array('10', '{$foo=[1,2,[7,8,9],4,5]}{2+$foo[2][1]}'),
            array('10', '{$foo=[1,2,[7,8,9],4,5]}{2+$foo.2.1}'),
            array('15', '{$foo=[1,2,[7,8,9],4,5]}{$foo[2][0]+$foo[2][1]}'),
            array('15', '{$foo=[1,2,[7,8,9],4,5]}{$foo.2.0+$foo.2.1}'),
            array('7', '{$foo=[1,2,[7,8,9],4,5]}{$x=2}{$y=0}{$foo.$x.$y}'),
            array('7', '{$foo=[1,2,[7,8,9],4,5]}{$x=2}{$foo.$x.0}'),
            array('7', '{$foo=[1,2,[7,8,9],4,5]}{$x=0}{$foo.2.$x}'),
            array('7', '{$foo=[1,2,[7,8,9],4,5]}{$x=[1,0]}{$foo.2.{$x.1}}'),
        );
    }

    /**
     * @dataProvider dataProviderForArrays
     */
    public function testArrays($expected, $code)
    {
        $this->assertEquals($expected, $this->smarty->fetch('eval:' . $code));
    }
}
