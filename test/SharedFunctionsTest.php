<?php
/**
* Smarty PHPunit tests of shared plugin functions
*
* @package PHPunit
* @author Rodney Rehm
*/

namespace Box\Brainy\Tests;


class SharedFunctionsTest extends Smarty_TestCase
{

    /**
    * test smarty_function_escape_special_chars()
    */
    public function testEscapeSpecialChars() {
        require_once BRAINY_PLUGINS_DIR . 'shared.escape_special_chars.php';

        $this->assertEquals('hello&lt;world &copy;', smarty_function_escape_special_chars('hello<world &copy;'));
        $this->assertEquals('ö€', smarty_function_escape_special_chars('ö€'));
    }
}
