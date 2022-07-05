<?php
/**
 * Smarty PHPunit tests  of the <?xml...> tag handling
 *
 * @package PHPunit
 * @author Uwe Tews
 */

namespace Box\Brainy\Tests;


class XmlTest extends Smarty_TestCase
{
    public function setup(): void {
        parent::setUp();
        $this->smarty->force_compile = true;
    }

    /**
     * test standard xml
     */
    public function testXml() {
        $tpl = $this->smarty->createTemplate('xml.tpl');
        $this->assertEquals('<?xml version="1.0" encoding="UTF-8"?>', $this->smarty->fetch($tpl));
    }
}
