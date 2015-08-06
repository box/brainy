<?php
/**
* Smarty PHPunit tests clearing all assigned variables
*
* @package PHPunit
* @author Uwe Tews
*/

namespace Box\Brainy\Tests;

use Box\Brainy\Templates\TemplateData;


class ClearAllAssignTest extends Smarty_TestCase
{
    protected $_data = null;
    protected $_tpl = null;
    protected $_dataBC = null;
    protected $_tplBC = null;

    public function setUp() {
        $this->smarty = SmartyTests::$smarty;
        $this->smartyBC = SmartyTests::$smartyBC;
        SmartyTests::init();
        $this->smarty->safe_lookups = \Box\Brainy\Brainy::LOOKUP_SAFE;
        $this->smartyBC->safe_lookups = \Box\Brainy\Brainy::LOOKUP_SAFE;

        $this->smarty->assign('foo','foo');
        $this->_data = new TemplateData($this->smarty);
        $this->_data->assign('bar','bar');
        $this->_tpl = $this->smarty->createTemplate('eval:{$foo}{$bar}{$blar}', null, null, $this->_data);
        $this->_tpl->assign('blar','blar');

        $this->smartyBC->assign('foo','foo');
        $this->_dataBC = new TemplateData($this->smartyBC);
        $this->_dataBC->assign('bar','bar');
        $this->_tplBC = $this->smartyBC->createTemplate('eval:{$foo}{$bar}{$blar}', null, null, $this->_dataBC);
        $this->_tplBC->assign('blar','blar');
    }

    /**
    * test all variables accessable
    */
    public function testAllVariablesAccessable() {
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
    * test clear all assign in data
    */
    public function testClearAllAssignInData() {
        $this->_data->clearAllAssign();
        $this->assertEquals('fooblar', $this->smarty->fetch($this->_tpl));
    }
    /**
    * test clear all assign in Smarty object
    */
    public function testClearAllAssignInSmarty() {
        $this->smarty->clearAllAssign();
        $this->assertEquals('barblar', $this->smarty->fetch($this->_tpl));
    }
    public function testSmarty2ClearAllAssignInSmarty() {
        $this->smartyBC->clear_all_assign();
        $this->assertEquals('barblar', $this->smartyBC->fetch($this->_tplBC));
    }
}
