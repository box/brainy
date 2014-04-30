<?php
/**
* Smarty PHPunit tests for Extendsresource
*
* @package PHPunit
* @author Uwe Tews
*/

/**
* class for extends resource tests
*/
class ExtendsResourceTests extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->smarty = SmartyTests::$smarty;
        SmartyTests::init();
        $this->smarty->setTemplateDir(array('./templates/extendsresource/','./templates/'));
//        $this->smarty->registerFilter(Smarty::FILTER_PRE,'prefilterextends');
    }

    static function isRunnable()
    {
        return true;
    }

    /**
    * clear folders
    */
    public function clear()
    {
        $this->smarty->clearAllCache();
        $this->smarty->clearCompiledTemplate();
    }
    /**
     * test  child/parent template chain with prepend
     */
    public function testCompileBlockChildPrepend_003()
    {
        $result = $this->smarty->fetch('extends:003_parent.tpl|003_child_prepend.tpl');
        $this->assertContains("prepend - Default Title", $result);
    }
    /**
     * test  child/parent template chain with apppend
     */
    public function testCompileBlockChildAppend_004()
    {
        $result = $this->smarty->fetch('extends:004_parent.tpl|004_child_append.tpl');
        $this->assertContains("Default Title - append", $result);
    }

 }

function prefilterextends($input)
{
    return preg_replace('/{extends .*}/', '', $input);
}

