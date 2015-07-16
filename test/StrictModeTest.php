<?php
/**
 * Tests for strict mode
 * @author Matt Basta
 */

class StrictModeTest extends Smarty_TestCase
{

    public function setUp()
    {
        parent::setUp();
        $this->smarty->security_policy = null;
        Smarty::$assignment_compat = Smarty::ASSIGN_COMPAT;
        $this->smarty->error_reporting = error_reporting() & ~(E_NOTICE|E_USER_NOTICE);
    }

    public function test_empty_strict_mode_file_passes() {
        $this->expectOutputString('');
        $this->smarty->fetch('string:{* set strict *}');
    }

    public function banned_constructs_provider() {
        return array(
            array('{$foo="bar" scope="global"}'), // Passing attributes in shorthand assignments
            array('{${$foo}="bar"}'), // Variable variable assignment
            array('{${$foo}}'), // Variable variable lookups
            array('{$foo.$bar}'), // Variable variable indices
            array('{$foo.$bar@zap}'), // Variable variable indices
            array('{$foo.{$bar}}'), // Dot notation with variable subscript
            array('{$foo.{section_name}}'), // Section tag syntax
            array('{$foo=${$bar}}'), // Variable variable non-base lookups
            array('{$foo->$foo}'), // Variable variable methods
            array('{$foo->{$x+$y}}'), // Expression methods
            array('{$foo->bar{$x+$y}}'), // Expression methods
            array('{if isset(#foo#)}{/if}'), // Isset on config variable
            array('{$foo|@json_encode}'), // @modifiers
            array('{if $x XOR $y}{/if}'), // XOR operator
            // Banned functions
            array('{next($foo)}'),
            array('{prev($foo)}'),
            array('{end($foo)}'),
            array('{current($foo)}'),
            array('{reset($foo)}'),
            // Banned operators:
            array('{if $x is not div by $y}{/if}'),
            array('{if $x is not even}{/if}'),
            array('{if $x is even by $y}{/if}'),
            array('{if $x is not even by $y}{/if}'),
            array('{if $x is not odd}{/if}'),
            array('{if $x is not odd by $y}{/if}'),
            array('{if $x instanceof $y}{/if}'),
            array('{if $x instanceof Foo}{/if}'),
        );
    }

    /**
     * @dataProvider banned_constructs_provider
     * @expectedException BrainyStrictModeException
     * @expectedExceptionMessage Strict Mode:
     */
    public function test_banned_constructs_are_not_allowed($source) {
        $this->smarty->createTemplate('eval:{* set strict *}' . $source)->compileTemplateSource();
    }

    /**
     * @dataProvider banned_constructs_provider
     */
    public function test_banned_constructs_are_allowed_outside_strict($source) {
        $output = $this->smarty->createTemplate('eval:' . $source);
        $output->compileTemplateSource();
        $this->assertTrue($output->compiled !== null);
    }

    public function banned_plugin_provider() {
        return array(
            array('{textformat}{/textformat}'),
            array('{html_checkboxes options=array()}'),
            array('{html_options}'),
            array('{html_radios options=array()}'),
            array('{html_select_date}'),
            array('{html_select_time}'),
            array('{html_table loop="foo" rows=1}'),
            array('{$foo|from_charset}'),
            array('{$foo|noprint}'),
            array('{$foo|to_charset}'),
            array('{$foo|unescape}'),
            array('{append var="foo" value="bar"}'),
        );
    }

    /**
     * @dataProvider banned_plugin_provider
     * @expectedException BrainyStrictModeException
     * @expectedExceptionMessage Strict Mode:
     */
    public function test_banned_plugins_are_not_allowed($source) {
        $this->smarty->fetch('eval:{* set strict *}' . $source);
    }

    /**
     * @dataProvider banned_plugin_provider
     */
    public function test_banned_plugins_are_allowed_outside_strict($source) {
        $output = $this->smarty->fetch('eval:' . $source);
        $this->assertTrue($output !== null);
    }

    /**
     * @expectedException BrainyStrictModeException
     * @expectedExceptionMessage Strict Mode:
     */
    public function test_html_image_is_not_allowed() {
        $this->smarty->fetch('eval:{* set strict *}{html_image}');
    }

    /**
     * @expectedExceptionMessage html_image: missing
     */
    public function test_html_image_is_allowed_outside_strict() {
        $output = $this->smarty->fetch('eval:{html_image}');
        $this->assertEmpty($output);
    }

    public function banned_special_construct_provider() {
        return array(
            array('{$smarty.section.foo.bar}'),
            array('{$smarty.cookies.foo}'),
            array('{$smarty.request.foo}'),
            array('{$smarty.session.foo}'),
            array('{$smarty.server.foo}'),
            array('{$smarty.get.foo}'),
            array('{$smarty.post.foo}'),
            array('{$smarty.env.foo}'),
            array('{$smarty.template_object->compile_id}'),
            array('{$smarty.const.FOO}'),
        );
    }

    /**
     * @dataProvider banned_special_construct_provider
     * @expectedException BrainyStrictModeException
     * @expectedExceptionMessage Strict Mode:
     */
    public function test_banned_special_constructs_are_not_allowed($source) {
        $this->smarty->fetch('eval:{* set strict *}' . $source);
    }

    /**
     * @dataProvider banned_special_construct_provider
     */
    public function test_banned_special_constructs_are_allowed_outside_strict($source) {
        $output = $this->smarty->fetch('eval:' . $source);
        $this->assertTrue($output !== null);
    }
}
