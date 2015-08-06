<?php
/**
* Smarty PHPunit tests compilation of strip tags
*
* @package PHPunit
* @author Uwe Tews
*/

namespace Box\Brainy\Tests;


class CompileStripTest extends Smarty_TestCase
{
    public function setUp() {
        $this->smarty = SmartyTests::$smarty;
        SmartyTests::init();
    }

    public function dataProviderForStripTests() {
        return array(
            array("<table>\n </table>", '<table></table>'),
            array("<table>\n foo\n  </table>", '<table>foo</table>'),
            array("<table foo=\"bar\"\n\t hello=\"there\">", '<table foo="bar" hello="there">'),
            array("<input \n disabled\n\t checked>", '<input disabled checked>'),
            array("foo  ", 'foo '),
            array("foo>  ", 'foo>'),
            array("foo   &nbsp;   bar", 'foo &nbsp; bar'),
        );
    }

    /**
     * @dataProvider dataProviderForStripTests
     */
    public function testStrip($source, $output) {
        $tpl = $this->smarty->createTemplate('eval:{strip}' . $source);
        $this->assertEquals($output, $this->smarty->fetch($tpl));
    }

    /**
     * @expectedException SmartyCompilerException
     */
    public function testUnbalancedStrip() {
        $this->smarty->fetch("eval:{strip}<table>\n </table>{/strip}{/strip}");
    }
}
