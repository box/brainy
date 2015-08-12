<?php

namespace Box\Brainy\Tests;

require __DIR__ . '/../vendor/autoload.php';


class SmartyTests
{
    public static $smarty = null;
    public static $smartyBC = null;

    public static function _init($smarty) {
        \Box\Brainy\Brainy::$enforce_expression_modifiers = array();
        $smarty->setTemplateDir(realpath('test' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR));
        $smarty->setCompileDir(realpath('test' . DIRECTORY_SEPARATOR . 'compiled' . DIRECTORY_SEPARATOR));
        $smarty->setPluginsDir(SMARTY_PLUGINS_DIR);
        $smarty->template_objects = array();
        \Box\Brainy\Brainy::$global_tpl_vars = array();
        $smarty->template_functions = array();
        $smarty->tpl_vars = array();
        $smarty->force_compile = false;
        $smarty->auto_literal = true;
        $smarty->registered_plugins = array();
        $smarty->default_modifiers = array();
        $smarty->escape_html = false;
        $smarty->use_sub_dirs = false;
        $smarty->security_policy = null;
        $smarty->left_delimiter = '{';
        $smarty->right_delimiter = '}';
        $smarty->enableSecurity();
        $smarty->error_unassigned = false;
        $smarty->compile_id = null;
        $smarty->safe_lookups = \Box\Brainy\Brainy::LOOKUP_UNSAFE;
    }

    public static function init() {
        self::_init(SmartyTests::$smarty);
        self::_init(SmartyTests::$smartyBC);
        \Box\Brainy\Resources\Resource::$sources = array();
        \Box\Brainy\Resources\Resource::$compileds = array();

        self::clearFiles();
    }

    /**
     * clear $smarty->compile_dir
     *
     * @return void
     */
    public static function clearFiles() {
        $directory = realpath(self::$smarty->getCompileDir());

        $di = new \RecursiveDirectoryIterator($directory);
        $it = new \RecursiveIteratorIterator($di, \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($it as $file) {
            $_file = $file->__toString();

            if (preg_match("#[\\\\/]\.#", $_file)) {
                continue;
            }

            if ($file->isDir()) {
                rmdir($_file);
            } else {
                unlink($_file);
            }

        }
    }
}

class Smarty_TestCase extends \PHPUnit_Framework_TestCase
{
    public $smarty = null;
    public $smartyBC = null;

    public function setUp()
    {
        $this->smarty = &SmartyTests::$smarty;
        $this->smartyBC = &SmartyTests::$smartyBC;
        SmartyTests::init();
        $this->smarty->escape_html = true;
        $this->smartyBC->escape_html = true;
        parent::setUp();
    }

    protected function setUpInstance($smarty)
    {
        return SmartyTests::_init($smarty);
    }

    protected function clearFiles()
    {
        SmartyTests::clearFiles();
    }
}


class _object_toString
{
    protected $string = null;
    public function __construct($string) {
        $this->string = (string) $string;
    }

    public function __toString() {
        return $this->string;
    }
}

class _object_noString
{
    protected $string = null;
    public function __construct($string) {
        $this->string = (string) $string;
    }
}


SmartyTests::$smarty = new \Box\Brainy\Brainy();
SmartyTests::$smartyBC = new \Box\Brainy\SmartyBC();

ini_set('date.timezone', 'UTC');
