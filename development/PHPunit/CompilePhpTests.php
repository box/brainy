<?php
/**
 * Smarty PHPunit tests compilation of {php} and <?php...?> tag
 *
 * @package PHPunit
 * @author Uwe Tews
 */

/**
 * class for {php} and <?php...?> tag tests
 */
class CompilePhpTests extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->smartyBC = SmartyTests::$smartyBC;
        SmartyTests::init();
        $this->smartyBC->disableSecurity();
    }

    static function isRunnable()
    {
        return true;
    }

    /**
     * test <?php...\> tag
     * default is PASSTHRU
     */
    public function testPhpTag()
    {
        $tpl = $this->smartyBC->createTemplate("eval:<?php echo 'hello world'; ?>");
        $content = $this->smartyBC->fetch($tpl);
        $this->assertEquals("<?php echo 'hello world'; ?>", $content);
    }
    /**
     * test <?=...\> shorttag
     * default is PASSTHRU
     */
    public function testShortTag()
    {
        $this->smartyBC->assign('foo', 'bar');
        $content = $this->smartyBC->fetch('eval:<?=$foo?>');
        $this->assertEquals('<?=$foo?>', $content);
    }
}
