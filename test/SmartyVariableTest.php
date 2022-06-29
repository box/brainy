<?php

namespace Box\Brainy\Tests;


class SmartyVariableTest extends Smarty_TestCase
{
    public function testVersion() {
        $this->assertEquals(\Box\Brainy\Brainy::SMARTY_VERSION, $this->smarty->fetch('eval:{$smarty.version}'));
    }
    public function testTemplate() {
        $this->assertStringContainsString('smarty.template.tpl', $this->smarty->fetch('smarty.template.tpl'));
    }
    public function testNow() {
        $out = $this->smarty->fetch('eval:{$smarty.now}');
        $now = time();
        $this->assertTrue(
            (int) $out === $now ||
            (int) $out + 1 === $now ||
            (int) $out - 1 === $now
        );
    }
}
