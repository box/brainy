<?php
/**
* Smarty PHPunit tests ternary operator
*
* @package PHPunit
* @author Uwe Tews
*/

namespace Box\Brainy\Tests;


class TernaryTest extends Smarty_TestCase
{
    public function setup(): void {
        parent::setUp();
        $this->smarty->safe_lookups = \Box\Brainy\Brainy::LOOKUP_SAFE;
    }

    /**
    * test output on boolean constant
    */
    public function testTernaryOutputBoolean1() {
        $tpl = $this->smarty->createTemplate("eval:{(true) ? 'yes' : 'no'}");
        $this->assertEquals('yes', $this->smarty->fetch($tpl));
    }
    public function testTernaryOutputBoolean2() {
        $tpl = $this->smarty->createTemplate("eval:{(false) ? 'yes' : 'no'}");
        $this->assertEquals('no', $this->smarty->fetch($tpl));
    }
    /**
    * test result expressions
    */
    public function testTernaryExpression1() {
        $tpl = $this->smarty->createTemplate("eval:{\$x=1}{(true) ? \$x : 'no'}");
        $this->assertEquals(1, $this->smarty->fetch($tpl));
    }
    public function testTernaryExpression2() {
        $tpl = $this->smarty->createTemplate("eval:{\$x=1}{(false) ? 'no' : \$x}");
        $this->assertEquals(1, $this->smarty->fetch($tpl));
    }
    public function testTernaryExpression3() {
        $tpl = $this->smarty->createTemplate("eval:{\$x=1}{(true) ? \$x+1 : 'no'}");
        $this->assertEquals(2, $this->smarty->fetch($tpl));
    }
    public function testTernaryExpression4() {
        $tpl = $this->smarty->createTemplate("eval:{\$x=1}{(false) ? 'no' : \$x+1}");
        $this->assertEquals(2, $this->smarty->fetch($tpl));
    }
    /**
    * test output on variable
    */
    public function testTernaryOutputVariable1() {
        $tpl = $this->smarty->createTemplate("eval:{\$foo=true}{(\$foo) ? 'yes' : 'no'}");
        $this->assertEquals('yes', $this->smarty->fetch($tpl));
    }
    public function testTernaryOutputVariable2() {
        $tpl = $this->smarty->createTemplate("eval:{\$foo=false}{(\$foo) ? 'yes' : 'no'}");
        $this->assertEquals('no', $this->smarty->fetch($tpl));
    }
    /**
    * test output on array element
    */
    public function testTernaryOutputArray3() {
        $tpl = $this->smarty->createTemplate("eval:{(\$foo.1.2) ? 'yes' : 'no'}");
        $this->assertEquals('no', $this->smarty->fetch($tpl));
    }
    public function testTernaryOutputArray4() {
        $tpl = $this->smarty->createTemplate("eval:{(\$foo[1][2]) ? 'yes' : 'no'}");
        $this->assertEquals('no', $this->smarty->fetch($tpl));
    }
    /**
    * test output on condition
    */
    public function testTernaryOutputCondition1() {
        $tpl = $this->smarty->createTemplate("eval:{\$foo=true}{(\$foo === true) ? 'yes' : 'no'}");
        $this->assertEquals('yes', $this->smarty->fetch($tpl));
    }
    public function testTernaryOutputCondition2() {
        $tpl = $this->smarty->createTemplate("eval:{\$foo=true}{(\$foo === false) ? 'yes' : 'no'}");
        $this->assertEquals('no', $this->smarty->fetch($tpl));
    }
    /**
    * test output on function
    */
    public function testTernaryOutputFunction1() {
        $tpl = $this->smarty->createTemplate("eval:{(time()) ? 'yes' : 'no'}");
        $this->assertEquals('yes', $this->smarty->fetch($tpl));
    }
    /**
    * test output on expression
    */
    public function testTernaryOutputExpression1() {
        $tpl = $this->smarty->createTemplate("eval:{(1 + 2 === 3) ? 'yes' : 'no'}");
        $this->assertEquals('yes', $this->smarty->fetch($tpl));
    }
    public function testTernaryOutputExpression2() {
        $tpl = $this->smarty->createTemplate("eval:{((1 + 2) === 3) ? 'yes' : 'no'}");
        $this->assertEquals('yes', $this->smarty->fetch($tpl));
    }
    /**
    * test assignment on boolean constant
    */
    public function testTernaryAssignBoolean1() {
        $tpl = $this->smarty->createTemplate("eval:{\$foo=(true) ? 'yes' : 'no'}{\$foo}");
        $this->assertEquals('yes', $this->smarty->fetch($tpl));
    }
    /**
    * test attribute on boolean constant
    */
    public function testTernaryAttributeBoolean1() {
        $tpl = $this->smarty->createTemplate("eval:{assign var=foo value=(true) ? 'yes' : 'no'}{\$foo}");
        $this->assertEquals('yes', $this->smarty->fetch($tpl));
    }
}
