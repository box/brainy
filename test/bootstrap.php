<?php

define('SMARTY_DIR', 'src/Brainy/');
require_once SMARTY_DIR . 'SmartyBC.class.php';

class SmartyTests
{
    public static $smarty = null;
    public static $smartyBC = null;

    public static function _init($smarty) {
        $smarty->setTemplateDir(realpath('test' . DS . 'templates' . DS));
        $smarty->setCompileDir(realpath('test' . DS . 'compiled' . DS));
        $smarty->setPluginsDir(SMARTY_PLUGINS_DIR);
        $smarty->setConfigDir(realpath('test' . DS . 'configs' . DS));
        $smarty->template_objects = array();
        $smarty->config_vars = array();
        Smarty::$global_tpl_vars = array();
        $smarty->template_functions = array();
        $smarty->tpl_vars = array();
        $smarty->force_compile = false;
        $smarty->auto_literal = true;
        Smarty::$_smarty_vars = array();
        $smarty->registered_plugins = array();
        $smarty->default_plugin_handler_func = null;
        $smarty->registered_objects = array();
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
        $smarty->error_reporting = null;
        $smarty->error_unassigned = true;
        $smarty->compile_id = null;
        $smarty->default_resource_type = 'file';
        $smarty->safe_lookups = Smarty::LOOKUP_UNSAFE;
    }

    public static function init() {
        self::_init(SmartyTests::$smarty);
        self::_init(SmartyTests::$smartyBC);
        Smarty_Resource::$sources = array();
        Smarty_Resource::$compileds = array();
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

SmartyTests::$smarty = new Smarty();
SmartyTests::$smartyBC = new SmartyBC();

ini_set('date.timezone', 'UTC');
