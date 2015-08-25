<?php
/**
* Smarty PHPunit tests of generic getter/setter
*
* @package PHPunit
* @author Uwe Tews
*/

namespace Box\Brainy\Tests;


class GetterSetterTest extends Smarty_TestCase
{
    /**
    * test setter on Smarty object
    */
    public function testSmartySetter() {
        $this->smarty->setLeftDelimiter('<{');
        $this->smarty->setRightDelimiter('}>');
        $this->assertEquals('<{', $this->smarty->left_delimiter);
        $this->assertEquals('}>', $this->smarty->right_delimiter);
    }
    /**
    * test getter on Smarty object
    */
    public function testSmartyGetter() {
        $this->smarty->setLeftDelimiter('<{');
        $this->smarty->setRightDelimiter('}>');
        $this->assertEquals('<{', $this->smarty->getLeftDelimiter());
        $this->assertEquals('}>', $this->smarty->getRightDelimiter());
    }
}
