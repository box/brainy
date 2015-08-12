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

}
