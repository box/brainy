<?php

namespace Box\Brainy\Tests;

class StrictModeTest extends Smarty_TestCase
{

    public function setup(): void
    {
        parent::setUp();
        $this->smarty->security_policy = null;
        \Box\Brainy\Brainy::$assignment_compat = \Box\Brainy\Brainy::ASSIGN_COMPAT;
        $this->smarty->error_reporting = error_reporting() & ~(E_NOTICE|E_USER_NOTICE);
    }

    public function test_empty_strict_mode_file_passes() {
        $this->expectOutputString('');
        $this->smarty->fetch('eval:{* set strict *}');
    }

    public function banned_constructs_provider() {
        return array(
            array('{${$foo}="bar"}'), // Variable variable assignment
            array('{${$foo}}'), // Variable variable lookups
            array('{$foo.$bar}'), // Variable variable indices
            array('{$foo.{$bar}}'), // Dot notation with variable subscript
            array('{$foo=${$bar}}'), // Variable variable non-base lookups
            array('{$foo|@json_encode}'), // @modifiers
            array('{if $x XOR $y}{/if}'), // XOR operator
            // Banned functions
            array('{next($foo)}'),
            array('{prev($foo)}'),
            array('{end($foo)}'),
            array('{current($foo)}'),
            array('{reset($foo)}'),

            // Banned Smarty variable stuff
            array('{$smarty.template}'),

            // Banned shorthand:
            array('{block "foo"}{/block}'),
            array('{include "foo"}'),
            array('{capture "foo"}{/capture}'),
        );
    }

    /**
     * @dataProvider banned_constructs_provider
     * @expectedException \Box\Brainy\Exceptions\BrainyStrictModeException
     * @expectedExceptionMessage Strict Mode:
     */
    public function test_banned_constructs_are_not_allowed($source) {
        $this->smarty->createTemplate("eval:{* set strict *}\n" . $source)->compileTemplateSource();
    }

    /**
     * @dataProvider banned_constructs_provider
     */
    public function test_banned_constructs_are_allowed_outside_strict($source) {
        $output = $this->smarty->createTemplate('eval:' . $source);
        $output->compileTemplateSource();
        $this->assertTrue($output->compiled !== null);
    }

    public function providerOfThingsThatArentBanned() {
        return array(
            array('{if $foo}{/if}'),
            array('{$foo}'),

            array('{for $i=0 to $foo.bar.baz-1}{/for}'),
            array('{for $i=0 to count($foo)-1}{/for}'),
            array('{for $i=0 to count($foo)-1 step 2}{/for}'),
            array('{for $i=1 to $foo}{/for}'),
            array('{for $i=count($foo)-1 to 0 step -1}{/for}'),
            array('{for $i=0 to 5 step 2}{/for}'),
        );
    }

    /**
     * @dataProvider providerOfThingsThatArentBanned
     */
    public function testThingsThatArentBannedDontGetBanned($source) {
        $this->smarty->createTemplate('eval:{* set strict *}' . $source)->compileTemplateSource();
        $this->assertTrue(true); // to prevent this from being a sketchy test
    }

    public function banned_plugin_provider() {
        return array(
            array('{html_checkboxes options=array()}'),
            array('{html_options}'),
            array('{html_radios options=array()}'),
            array('{html_select_date}'),
            array('{html_select_time}'),
            array('{html_table loop="foo" rows=1}'),
            array('{$foo|noprint}'),
        );
    }

    /**
     * @dataProvider banned_plugin_provider
     * @expectedException \Box\Brainy\Exceptions\BrainyStrictModeException
     * @expectedExceptionMessage Strict Mode:
     */
    public function test_banned_plugins_are_not_allowed($source) {
        $this->smarty->display('eval:{* set strict *}' . $source);
    }

    /**
     * @dataProvider banned_plugin_provider
     */
    public function test_banned_plugins_are_allowed_outside_strict($source) {
        $this->smarty->safe_lookups = \Box\Brainy\Brainy::LOOKUP_SAFE;
        $output = $this->smarty->fetch('eval:' . $source);
        $this->assertTrue($output !== null);
    }

}
