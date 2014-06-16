<?php
/**
 * Smarty PHPunit test suite
 *
 * @package PHPunit
 * @author Uwe Tews
 */


class  PHPUnit_Framework_TestCase
{
    public $current_function = '';
    public $error_functions = array();

    public function __construct()
    {
        $this->setUp();
    }

    public function __call($a, $b)
    {
        $this->error();
        echo '<br>Missing method  ' . $a;

        return true;
    }

    public function assertContains($a, $b)
    {
        if (strpos($b, $a) === false) {
            $this->error();
            echo '<br><br>result: ' . $b;
            echo '<br>should contain: ' . $a;
        }
    }

    public function assertNotContains($a, $b)
    {
        if (strpos($b, $a) !== false) {
            $this->error();
            echo '<br>result: ' . $b;
            echo '<br>should not contain: ' . $a;
        }
    }

    public function assertEquals($a, $b)
    {
        if ($a !== $b) {
            $this->error();
            echo '<br>expected ' . print_r($a);
            echo '<br>is: ' . print_r($b);
        }
    }

    public function assertFalse($a)
    {
        if ($a !== false) {
            $this->error();
            echo '<br>result was not false';
        }
    }

    public function assertTrue($a)
    {
        if ($a !== true) {
            $this->error();
            echo '<br>result was not true';
        }
    }

    public function assertNull($a)
    {
        if ($a !== null) {
            $this->error();
            echo '<br>result was not "null"';
        }
    }

    public function error()
    {
        echo '<br><br><br>ERROR in test:  ' . $this->current_function;
        $this->error_functions[] = $this->current_function;
    }
}
