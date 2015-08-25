<?php
/**
 * Smarty PHPunit tests double quoted strings
 *
 * @package PHPunit
 * @author Uwe Tews
 */

namespace Box\Brainy\Tests;


class DoubleQuotedStringTest extends Smarty_TestCase
{
    /**
     * test simple double quoted string
     */
    public function testSimpleDoubleQuotedString() {
        $tpl = $this->smarty->createTemplate('eval:{$foo="Hello World"}{$foo}', null, null, $this->smarty);
        $this->assertEquals('Hello World', $this->smarty->fetch($tpl));
    }
    /**
     * test expression tags in double quoted strings
     */
    public function testTagsInDoubleQuotedString() {
        $tpl = $this->smarty->createTemplate('eval:{$foo="Hello {1+2} World"}{$foo}', null, null, $this->smarty);
        $this->assertEquals('Hello 3 World', $this->smarty->fetch($tpl));
    }
    /**
     * test vars in double quoted strings
     */
    public function testVarsInDoubleQuotedString1() {
        $tpl = $this->smarty->createTemplate('eval:{$bar=\'blah\'}{$foo="Hello $bar World"}{$foo}', null, null, $this->smarty);
        $this->assertEquals('Hello blah World', $this->smarty->fetch($tpl));
    }
    public function testVarsInDoubleQuotedString2() {
        $tpl = $this->smarty->createTemplate('eval:{$bar=\'blah\'}{$buh=\'buh\'}{$foo="Hello $bar$buh World"}{$foo}', null, null, $this->smarty);
        $this->assertEquals('Hello blahbuh World', $this->smarty->fetch($tpl));
    }
    /**
     * test vars in delimiter in double quoted strings
     */
    public function testVarsDelimiterInDoubleQuotedString() {
        $tpl = $this->smarty->createTemplate('eval:{$bar=\'blah\'}{$foo="Hello {$bar}.test World"}{$foo}', null, null, $this->smarty);
        $this->assertEquals('Hello blah.test World', $this->smarty->fetch($tpl));
    }
    /**
     * test escaped quotes in double quoted strings
     */
    public function testEscapedQuotesInDoubleQuotedString() {
        $tpl = $this->smarty->createTemplate('eval:{$foo="Hello \" World"}{$foo}', null, null, $this->smarty);
        $this->assertEquals('Hello " World', $this->smarty->fetch($tpl));
    }

    /**
     * test single quotes in double quoted strings
     */
    public function testSingleQuotesInDoubleQuotedString() {
        $tpl = $this->smarty->createTemplate('eval:{$foo="Hello \'World\'"}{$foo}', null, null, $this->smarty);
        $this->assertEquals("Hello 'World'", $this->smarty->fetch($tpl));
    }
    /**
     * test empty double quoted strings
     */
    public function testEmptyDoubleQuotedString() {
        $tpl = $this->smarty->createTemplate('eval:{$foo=""}{$foo}', null, null, $this->smarty);
        $this->assertEquals("", $this->smarty->fetch($tpl));
    }
}
