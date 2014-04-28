<?php
/**
 * Smarty PHPunit test suite
 *
 * @package PHPunit
 * @author Uwe Tews
 */
include '_runTest.inc.php';

/**
 * class for running test suite
 */
class PHPunit_Smarty_Test extends PHPUnit_Framework_TestSuite
{


    /**
     * look for test units and run them
     */
    public static function suite()
    {
        $testorder = array(
            'ConfigVarTests'
        );
        $smarty_libs_dir = dirname(__FILE__) . '/../../distribution/libs';
        if (method_exists('PHPUnit_Util_Filter', $smarty_libs_dir)) {
            // Older versions of PHPUnit did not have this function,
            // which is used when determining which PHP files are
            // included in the PHPUnit code coverage result.
            PHPUnit_Util_Filter::addDirectoryToWhitelist($smarty_libs_dir);
            // PHPUnit_Util_Filt<er::removeDirectoryFromWhitelist('../');
            // PHPUnit_Util_Filter::addDirectoryToWhitelist('../libs/plugins');
        }
        $suite = new self('Smarty 3 - Unit Tests Report');
        // load test which should run in specific order
        foreach ($testorder as $class) {
            require_once $class . '.php';
            $suite->addTestSuite($class);
        }

        if (false) {
            foreach (new DirectoryIterator(dirname(__FILE__)) as $file) {
                if (!$file->isDot() && !$file->isDir() && (string)$file !== 'smartytests.php' && (string)$file !== 'smartysingletests.php' && substr((string)$file, -4) === '.php') {
                    $class = basename($file, '.php');
                    if (!in_array($class, $testorder)) {
                        require_once $file->getPathname();
                        // to have an optional test suite, it should implement a public static function isRunnable
                        // that returns true only if all the conditions are met to run it successfully, for example
                        // it can check that an external library is present
                        if (!method_exists($class, 'isRunnable') || call_user_func(array($class, 'isRunnable'))) {
                            $suite->addTestSuite($class);
                        }
                    }
                }
            }
        }

        return $suite;
    }
}
