<?php

$loader = require __DIR__ . '/../vendor/autoload.php';
$loader->add('Box\\Brainy\\Test\\', __DIR__);


use \Box\Brainy\Brainy;


class SmartyTests
{
    public static $smarty = null;
    public static $smartyBC = null;

    public static function _init($smarty) {
        Brainy::$enforce_expression_modifiers = array();
        $smarty->setTemplateDir(realpath('test' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR));
        $smarty->setCompileDir(realpath('test' . DIRECTORY_SEPARATOR . 'compiled' . DIRECTORY_SEPARATOR));
        $smarty->setPluginsDir(SMARTY_PLUGINS_DIR);
        $smarty->setConfigDir(realpath('test' . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR));
        $smarty->template_objects = array();
        $smarty->config_vars = array();
        Brainy::$global_tpl_vars = array();
        $smarty->template_functions = array();
        $smarty->tpl_vars = array();
        $smarty->force_compile = false;
        $smarty->auto_literal = true;
        Brainy::$_smarty_vars = array();
        $smarty->registered_plugins = array();
        $smarty->default_plugin_handler_func = null;
        $smarty->default_modifiers = array();
        $smarty->registered_filters = array();
        $smarty->autoload_filters = array();
        $smarty->escape_html = false;
        $smarty->use_sub_dirs = false;
        $smarty->config_overwrite = true;
        $smarty->config_booleanize = true;
        $smarty->config_read_hidden = true;
        $smarty->security_policy = null;
        $smarty->left_delimiter = '{';
        $smarty->right_delimiter = '}';
        $smarty->enableSecurity();
        $smarty->error_unassigned = false;
        $smarty->compile_id = null;
        $smarty->default_resource_type = 'file';
        $smarty->safe_lookups = Brainy::LOOKUP_UNSAFE;
    }

    public static function init() {
        self::_init(SmartyTests::$smarty);
        self::_init(SmartyTests::$smartyBC);
        Smarty_Resource::$sources = array();
        Smarty_Resource::$compileds = array();

        self::clearFiles();
    }

    /**
     * clear $smarty->compile_dir
     *
     * @return void
     */
    protected static function clearFiles() {
        $directory = realpath(self::$smarty->getCompileDir());

        $di = new RecursiveDirectoryIterator($directory);
        $it = new RecursiveIteratorIterator($di, RecursiveIteratorIterator::CHILD_FIRST);
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

class Smarty_TestCase extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->smarty = SmartyTests::$smarty;
        SmartyTests::init();
        $this->smarty->escape_html = true;
        parent::setUp();
    }

    protected function setUpInstance($smarty)
    {
        return SmartyTests::_init($smarty);
    }
}

SmartyTests::$smarty = new Brainy();
SmartyTests::$smartyBC = new SmartyBC();

ini_set('date.timezone', 'UTC');
