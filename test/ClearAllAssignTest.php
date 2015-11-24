<?php
/**
* Smarty PHPunit tests clearing all assigned variables
*
* @package PHPunit
* @author Uwe Tews
*/

namespace Box\Brainy\Tests;


class ClearAllAssignTest extends Smarty_TestCase
{
    protected $_data = null;
    protected $_tpl = null;
    protected $_dataBC = null;
    protected $_tplBC = null;

    public function setUp() {
        parent::setUp();
        $this->smarty->safe_lookups = \Box\Brainy\Brainy::LOOKUP_SAFE;
        $this->smartyBC->safe_lookups = \Box\Brainy\Brainy::LOOKUP_SAFE;

        $this->smarty->assign('foo', 'foo');
        $this->_data = new Helpers\Data($this->smarty);
        $this->_data->assign('bar', 'bar');
        $this->_tpl = $this->smarty->createTemplate('eval:{$foo}{$bar}{$blar}', null, $this->_data);
        $this->_tpl->assign('blar', 'blar');

        $this->smartyBC->assign('foo', 'foo');
        $this->_dataBC = new Helpers\Data($this->smartyBC);
        $this->_dataBC->assign('bar', 'bar');
        $this->_tplBC = $this->smartyBC->createTemplate('eval:{$foo}{$bar}{$blar}', null, $this->_dataBC);
        $this->_tplBC->assign('blar', 'blar');
    }

    /**
    * test all variables accessable
    */
    public function testAllVariablesAccessible() {
        $this->assertEquals('foobarblar', $this->smarty->fetch($this->_tpl));
    }

    /**
    * test clear all assign in template
    */
    public function testClearAllAssignInTemplate() {
        $this->_tpl->clearAllAssign();
        $this->assertEquals('foobar', $this->smarty->fetch($this->_tpl));
    }

    /**
     * Clearing all on Brainy shouldn't affect the tempalte once the template
     * is already created. There should be no logical connection between the
     * Brainy instance's root scope and the template instance's root scope.
     */
    public function testClearAllAssignInSmarty() {
        $this->smarty->clearAllAssign();
        $this->assertEquals('foobarblar', $this->smarty->fetch($this->_tpl));
    }
    public function testSmarty2ClearAllAssignInSmarty() {
        $this->smartyBC->clear_all_assign();
        $this->assertEquals('foobarblar', $this->smartyBC->fetch($this->_tplBC));
    }
}
