<?php

namespace Box\Brainy\Tests;


class AttributeTest extends Smarty_TestCase
{

    /**
     * @expectedException Exception
     * @expectedExceptionMessage missing "var" attribute
     */
    public function testRequiredAttributeVar() {
        $this->smarty->fetch('eval:{assign value=1}');
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage unexpected "bar" attribute
     */
    public function testUnexpectedAttribute() {
        $this->smarty->fetch('eval:{assign var=foo value=1 bar=2}');
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage too many shorthand attributes
     */
    public function testTooManyShorthands() {
        $this->smarty->fetch('eval:{assign foo 1 2}');
    }

}
