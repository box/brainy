<?php
/**
* Smarty PHPunit tests loadFilter method
*
* @package PHPunit
* @author Uwe Tews
*/

namespace Box\Brainy\Tests;


class LoadFilterTest extends Smarty_TestCase
{
    public function setUp() {
        $this->smarty = SmartyTests::$smarty;
        SmartyTests::init();
    }

    /**
    * test loadFilter method
    */
    public function testLoadFilter() {
        $this->smarty->loadFilter('output', 'trimwhitespace');
        $this->assertTrue(is_callable($this->smarty->registered_filters['output']['smarty_outputfilter_trimwhitespace']));
    }
}
