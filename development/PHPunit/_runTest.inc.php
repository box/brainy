<?php
/**
 * Smarty PHPunit test suite
 *
 * @package PHPunit
 * @author Uwe Tews
 */
define ('SMARTY_DIR', '../../distribution/libs/');

require_once SMARTY_DIR . 'SmartyBC.class.php';

/**
 * class for running test suite
 */
class SmartyTests
{   public static $cwd = null;
    public static $smarty = null;
    public static $smartyBC = null;
    public static $smartyBC31 = null;

    protected static function _init($smarty)
    {
        $smarty->setTemplateDir('.' . DS . 'templates' . DS);
        $smarty->setCompileDir('.' . DS . 'templates_c' . DS);
        $smarty->setPluginsDir(SMARTY_PLUGINS_DIR);
        $smarty->setCacheDir('.' . DS . 'cache' . DS);
        $smarty->setConfigDir('.' . DS . 'configs' . DS);
        $smarty->template_objects = array();
        $smarty->config_vars = array();
        Smarty::$global_tpl_vars = array();
        $smarty->template_functions = array();
        $smarty->tpl_vars = array();
        $smarty->force_compile = false;
        $smarty->force_cache = false;
        $smarty->auto_literal = true;
        $smarty->caching = false;
        $smarty->debugging = false;
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
        $smarty->php_handling = Smarty::PHP_PASSTHRU;
        $smarty->enableSecurity();
        $smarty->error_reporting = null;
        $smarty->error_unassigned = true;
        $smarty->caching_type = 'file';
        $smarty->cache_locking = false;
        $smarty->cache_id = null;
        $smarty->compile_id = null;
        $smarty->default_resource_type = 'file';
    }

    public static function init()
    {
        chdir(self::$cwd);
        error_reporting(E_ALL | E_STRICT);
        self::_init(SmartyTests::$smarty);
        self::_init(SmartyTests::$smartyBC);
        Smarty_Resource::$sources = array();
        Smarty_Resource::$compileds = array();
        //        Smarty_Resource::$resources = array();
        SmartyTests::$smartyBC->registerPlugin('block','php','smarty_php_tag');
    }
}

SmartyTests::$cwd= getcwd();
SmartyTests::$smarty = new Smarty();
SmartyTests::$smartyBC = new SmartyBC();

