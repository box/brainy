<?php
/**
* Smarty PHPunit tests compilation of assign tags
*
* @package PHPunit
* @author Uwe Tews
*/

namespace Box\Brainy\Tests;


class CompileAssignTest extends Smarty_TestCase
{
    /**
     * test old style of assign tag
     */
    public function testAssignOld1() {
        $this->assertEquals("1", $this->smarty->fetch('eval:{assign var=foo   value=1}{$foo}'));
        $this->assertEquals("1", $this->smarty->fetch('eval:{assign var = foo   value= 1}{$foo}'));
    }
    public function testAssignOld2() {
        $tpl = $this->smarty->createTemplate('eval:{assign var=\'foo\' value=1}{$foo}');
        $this->assertEquals("1", $this->smarty->fetch($tpl));
    }
    public function testAssignOld3() {
        $tpl = $this->smarty->createTemplate('eval:{assign var="foo" value=1}{$foo}');
        $this->assertEquals("1", $this->smarty->fetch($tpl));
    }
    public function testAssignOld4() {
        $tpl = $this->smarty->createTemplate('eval:{assign var=foo value=bar}{$foo}');
        $this->assertEquals("bar", $this->smarty->fetch($tpl));
    }
    public function testAssignOld5() {
        $tpl = $this->smarty->createTemplate('eval:{assign var=foo value=1+2}{$foo}');
        $this->assertEquals("3", $this->smarty->fetch($tpl));
    }
    public function testAssignOld6() {
        $this->smarty->security_policy->php_functions = array('strlen');
        $tpl = $this->smarty->createTemplate('eval:{assign var=foo value=strlen(\'bar\')}{$foo}');
        $this->assertEquals("3", $this->smarty->fetch($tpl));
    }
    public function testAssignOld7() {
        $this->smarty->security_policy->php_modifiers = array('strlen');
        $tpl = $this->smarty->createTemplate('eval:{assign var=foo value=\'bar\'|strlen}{$foo}');
        $this->assertEquals("3", $this->smarty->fetch($tpl));
    }
    public function testAssignOld8() {
        $tpl = $this->smarty->createTemplate('eval:{assign var=foo value=[9,8,7, 6]}{foreach $foo as $x}{$x}{/foreach}');
        $this->assertEquals("9876", $this->smarty->fetch($tpl));
    }
    public function testAssignOld9() {
        $tpl = $this->smarty->createTemplate('eval:{assign var=foo value=[\'a\'=>9,\'b\'=>8,\'c\'=>7,\'d\'=>6]}{foreach $foo as $k => $x}{$k}{$x}{/foreach}');
        $this->assertEquals("a9b8c7d6", $this->smarty->fetch($tpl));
    }
    /**
    * test new style of assign tag
    */
    public function testAssignNew1() {
        $this->assertEquals("1", $this->smarty->fetch('eval:{$foo=1}{$foo}'));
        $this->assertEquals("1", $this->smarty->fetch('eval:{$foo =1}{$foo}'));
        $this->assertEquals("1", $this->smarty->fetch('eval:{$foo =  1}{$foo}'));
    }
    public function testAssignNew2() {
        $tpl = $this->smarty->createTemplate('eval:{$foo=bar}{$foo}');
        $this->assertEquals("bar", $this->smarty->fetch($tpl));
    }
    public function testAssignNew3() {
        $this->assertEquals("3", $this->smarty->fetch('eval:{$foo=1+2}{$foo}'));
        $this->assertEquals("3", $this->smarty->fetch('eval:{$foo = 1+2}{$foo}'));
        $this->assertEquals("3", $this->smarty->fetch('eval:{$foo = 1 + 2}{$foo}'));
    }
    public function testAssignNew4() {
        $this->smarty->security_policy->php_functions = array('strlen');
        $tpl = $this->smarty->createTemplate('eval:{$foo=strlen(\'bar\')}{$foo}');
        $this->assertEquals("3", $this->smarty->fetch($tpl));
    }
    public function testAssignNew5() {
        $this->smarty->security_policy->php_modifiers = array('strlen');
        $tpl = $this->smarty->createTemplate("eval:{\$foo='bar'|strlen}{\$foo}");
        $this->assertEquals("3", $this->smarty->fetch($tpl));
    }
    public function testAssignNew6() {
        $tpl = $this->smarty->createTemplate("eval:{\$foo=[9,8,7, 6]}{foreach \$foo as \$x}{\$x}{/foreach}");
        $this->assertEquals("9876", $this->smarty->fetch($tpl));
    }
    public function testAssignNew7() {
        $tpl = $this->smarty->createTemplate("eval:{\$foo=['a'=>9,'b'=>8,'c'=>7,'d'=>6]}{foreach \$foo as \$k => \$x}{\$k}{\$x}{/foreach}");
        $this->assertEquals("a9b8c7d6", $this->smarty->fetch($tpl));
    }
    public function testAssignMemberInSafeMode()
    {
        $this->smarty->safe_lookups = \Box\Brainy\Brainy::LOOKUP_SAFE;

        // This test previously failed because safe mode caused invalid code generation.
        // `{$foo.bar =` compiled to `lookup(lookup('foo'), 'bar') = ...`, which is
        // syntactiaclly invalid. The fix for this bug involved eliminating the safe mode
        // wrapper on the final output of the LHS of the assignment.
        $tpl = $this->smarty->createTemplate("eval:{\$foo = []}\n{\$foo.bar = 123}\n{\$foo.bar}");
        $this->assertEquals("123", $this->smarty->fetch($tpl), $tpl->compileTemplateSource());
    }

    public function testInvalidScope() {
        $this->expectException(\Box\Brainy\Exceptions\SmartyCompilerException::class);
        $this->expectExceptionMessage('missing "value" attribute');
        $this->smarty->display('eval:{assign var=foo scope="foo"}');
    }
}
