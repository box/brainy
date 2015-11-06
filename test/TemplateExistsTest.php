<?php
/**
* Smarty PHPunit tests for templateExists method
*
* @package PHPunit
* @author Uwe Tews
*/

namespace Box\Brainy\Tests;


class TemplateExistsTest extends Smarty_TestCase
{
    public function testSmartyTemplateExists() {
        $this->assertTrue($this->smarty->templateExists('helloworld.tpl'));
    }

    public function testSmartyTemplateNotExists() {
        $this->assertFalse($this->smarty->templateExists('notthere.tpl'));
    }
}
