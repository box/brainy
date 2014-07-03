<?php
/**
 * Project:     Brainy
 * File:        Smarty.class.php
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * More information is available on the Brainy GitHub page.
 *
 * @link http://www.smarty.net/
 * @copyright 2008 New Digital Group, Inc.
 * @copyright 2014 Box, Inc.
 * @author Monte Ohrt <monte at ohrt dot com>
 * @author Uwe Tews
 * @author Rodney Rehm
 * @author Matt Basta
 * @package Brainy
 */


/**
 * define shorthand directory separator constant
 */
if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

/**
 * set SMARTY_DIR to absolute path to Smarty library files.
 * Sets SMARTY_DIR only if user application has not already defined it.
 */
if (!defined('SMARTY_DIR')) {
    define('SMARTY_DIR', dirname(__FILE__) . DS);
}

/**
 * set SMARTY_SYSPLUGINS_DIR to absolute path to Smarty internal plugins.
 * Sets SMARTY_SYSPLUGINS_DIR only if user application has not already defined it.
 */
if (!defined('SMARTY_SYSPLUGINS_DIR')) {
    define('SMARTY_SYSPLUGINS_DIR', SMARTY_DIR . 'sysplugins' . DS);
}
if (!defined('SMARTY_PLUGINS_DIR')) {
    define('SMARTY_PLUGINS_DIR', SMARTY_DIR . 'plugins' . DS);
}
if (!defined('SMARTY_MBSTRING')) {
    define('SMARTY_MBSTRING', function_exists('mb_split'));
}
if (!defined('SMARTY_RESOURCE_CHAR_SET')) {
    // UTF-8 can only be done properly when mbstring is available!
    /**
     * @deprecated in favor of Smarty::$_CHARSET
     */
    define('SMARTY_RESOURCE_CHAR_SET', SMARTY_MBSTRING ? 'UTF-8' : 'ISO-8859-1');
}
if (!defined('SMARTY_RESOURCE_DATE_FORMAT')) {
    /**
     * @deprecated in favor of Smarty::$_DATE_FORMAT
     */
    define('SMARTY_RESOURCE_DATE_FORMAT', '%b %e, %Y');
}

/**
 * register the class autoloader
 */
if (!defined('SMARTY_SPL_AUTOLOAD')) {
    define('SMARTY_SPL_AUTOLOAD', 0);
}

if (SMARTY_SPL_AUTOLOAD && set_include_path(get_include_path() . PATH_SEPARATOR . SMARTY_SYSPLUGINS_DIR) !== false) {
    $registeredAutoLoadFunctions = spl_autoload_functions();
    if (!isset($registeredAutoLoadFunctions['spl_autoload'])) {
        spl_autoload_register();
    }
} else {
    spl_autoload_register('smartyAutoload');
}

if (!function_exists('smarty_safe_array_lookup')) {

    /**
     * Performs a safe lookup of an array member with a safety value.
     * @param mixed $arr
     * @param string|int $key
     * @param int $safety
     * @return mixed
     * @throws InvalidArgumentException
     * @see Smarty::$safe_lookups
     * @internal
     */
    function smarty_safe_array_lookup($arr, $key, $safety) {
        if (is_array($arr) && isset($arr[$key])) {
            return $arr[$key];
        }
        if ($safety === Smarty::LOOKUP_SAFE_WARN) {
            trigger_error('Could not find member "' . $key . '" in Brainy template.', E_USER_WARNING);
        }
        return '';
    }
}

if (!function_exists('smarty_safe_var_lookup')) {
    /**
     * Performs a safe lookup of a variable.
     * @param array $arr
     * @param string|int $key
     * @param int $safety
     * @return mixed
     * @throws InvalidArgumentException
     * @see Smarty::$safe_lookups
     * @internal
     */
    function smarty_safe_var_lookup($arr, $key, $safety) {
        if (isset($arr[$key])) {
            return $arr[$key];
        }
        if ($safety === Smarty::LOOKUP_SAFE_WARN) {
            trigger_error('Could not find variable "' . $key . '" in Brainy template.', E_USER_WARNING);
        }
        return $arr[$key] = new Smarty_Variable;
    }
}

/**
 * Load always needed external class files
 */
include_once SMARTY_SYSPLUGINS_DIR . 'smarty_internal_data.php';
include_once SMARTY_SYSPLUGINS_DIR . 'smarty_internal_templatebase.php';
include_once SMARTY_SYSPLUGINS_DIR . 'smarty_internal_template.php';
include_once SMARTY_SYSPLUGINS_DIR . 'smarty_resource.php';
include_once SMARTY_SYSPLUGINS_DIR . 'smarty_internal_resource_file.php';
include_once SMARTY_SYSPLUGINS_DIR . 'smarty_cacheresource.php';
include_once SMARTY_SYSPLUGINS_DIR . 'smarty_internal_cacheresource_file.php';

/**
 * This is the main Brainy class
 * @package Brainy
 */
class Smarty extends Smarty_Internal_TemplateBase {
    /**
     * The current version string for the current version of Brainy.
     * @var string
     */
    const SMARTY_VERSION = 'Brainy-1.0.0-dev';

    /**
     * Represents local scope.
     * @var int
     */
    const SCOPE_LOCAL = 0;
    /**
     * Represents the parent template's scope.
     * @var int
     */
    const SCOPE_PARENT = 1;
    /**
     * Represents the root template's scope.
     * @var int
     */
    const SCOPE_ROOT = 2;
    /**
     * Represents the global scope.
     * @var int
     */
    const SCOPE_GLOBAL = 3;

    /**
     * Represents disabled caching.
     * @var int
     * @see Smarty::$caching Usage Information
     */
    const CACHING_OFF = 0;
    /**
     * When used, $cache_lifetime will be used to determine if the cache has
     * expired.
     * @var int
     * @see Smarty::$caching Usage Information
     */
    const CACHING_LIFETIME_CURRENT = 1;
    /**
     * When used, $cache_lifetime will be used to determine if the cache has
     * expired at the time the cache is generated.
     * @var int
     * @see Smarty::$caching Usage Information
     */
    const CACHING_LIFETIME_SAVED = 2;


    /**
     * When passed to various cache-clearing methods, expired cache items will
     * be removed.
     * @var int
     */
    const CLEAR_EXPIRED = -1;

    /**
     * Disables checking templates of compiled files to detect changes.
     * @var int
     * @see Smarty::$compile_check Usage Information
     */
    const COMPILECHECK_OFF = 0;
    /**
     * Enables checking templates of compiled files to detect changes.
     * @var int
     * @see Smarty::$compile_check Usage Information
     */
    const COMPILECHECK_ON = 1;
    /**
     * Enables checking templates of compiled files to detect changes when a
     * cache miss occurs.
     * @var int
     * @see Smarty::$compile_check Usage Information
     */
    const COMPILECHECK_CACHEMISS = 2;


    /**
     * Represents post-filtering
     * @var string
     * @see Smarty::setAutoloadFilters() Usage in setAutoloadFilters
     * @see Smarty::addAutoloadFilters() Usage in addAutoloadFilters
     */
    const FILTER_POST = 'post';
    /**
     * Represents pre-filtering
     * @var string
     * @see Smarty::setAutoloadFilters() Usage in setAutoloadFilters
     * @see Smarty::addAutoloadFilters() Usage in addAutoloadFilters
     */
    const FILTER_PRE = 'pre';
    /**
     * Represents output-filtering
     * @var string
     * @see Smarty::setAutoloadFilters() Usage in setAutoloadFilters
     * @see Smarty::addAutoloadFilters() Usage in addAutoloadFilters
     */
    const FILTER_OUTPUT = 'output';
    /**
     * Represents variable-filtering
     * @var string
     * @see Smarty::setAutoloadFilters() Usage in setAutoloadFilters
     * @see Smarty::addAutoloadFilters() Usage in addAutoloadFilters
     */
    const FILTER_VARIABLE = 'variable';

    /**
     * Represents a function plugin
     * @var string
     */
    const PLUGIN_FUNCTION = 'function';
    /**
     * Represents a block plugin
     * @var string
     */
    const PLUGIN_BLOCK = 'block';
    /**
     * Represents a compiler plugin
     * @var string
     */
    const PLUGIN_COMPILER = 'compiler';
    /**
     * Represents a modifier plugin
     * @var string
     */
    const PLUGIN_MODIFIER = 'modifier';
    /**
     * Represents a modifiercompiler plugin
     * @var string
     */
    const PLUGIN_MODIFIERCOMPILER = 'modifiercompiler';

    /**
     * When used, lookups will be unsafe (default Smarty 3 behavior)
     * @var int
     * @see Smarty::$safe_lookups Usage Information
     */
    const LOOKUP_UNSAFE = 0;
    /**
     * When used, lookups will be safe (default Smarty 2 behavior)
     * @var int
     * @see Smarty::$safe_lookups Usage Information
     */
    const LOOKUP_SAFE = 1;
    /**
     * When used, lookups will be safe and a warning will be raised using
     * trigger_error.
     * @var int
     * @see Smarty::$safe_lookups Usage Information
     */
    const LOOKUP_SAFE_WARN = 2;


    /**
     * assigned global tpl vars
     * @internal
     */
    public static $global_tpl_vars = array();

    /**
     * error handler returned by set_error_hanlder() in Smarty::muteExpectedErrors()
     * @internal
     */
    public static $_previous_error_handler = null;
    /**
     * contains directories outside of SMARTY_DIR that are to be muted by muteExpectedErrors()
     * @internal
     */
    public static $_muted_directories = array();
    /**
     * Flag denoting if Multibyte String functions are available
     * @internal
     */
    public static $_MBSTRING = SMARTY_MBSTRING;
    /**
     * The character set to adhere to (e.g. "UTF-8")
     * @internal
     */
    public static $_CHARSET = SMARTY_RESOURCE_CHAR_SET;
    /**
     * The date format to be used internally
     * (accepts date() and strftime())
     * @internal
     */
    public static $_DATE_FORMAT = SMARTY_RESOURCE_DATE_FORMAT;
    /**
     * Flag denoting if PCRE should run in UTF-8 mode
     * @internal
     */
    public static $_UTF8_MODIFIER = 'u';

    /**
     * Flag denoting if operating system is windows
     * @internal
     */
    public static $_IS_WINDOWS = false;



    /**
     * When `true`, delimiter strings will be ignored if they are surrounded by whitespace.
     * @var boolean
     */
    public $auto_literal = true;
    /**
     * When `true`, an error will be displayed when accessing undefined variables.
     * @var boolean
     */
    public $error_unassigned = false;
    /**
     * When `true`, the include_path will be respected for finding file resources.
     * @var boolean
     * @deprecated This can cause incompatibility issues between environments and generally causes confusion. It is a source of problems.
     */
    public $use_include_path = false;
    /**
     * Directory that cached templates are stored in. See the following
     * methods instead:
     *
     * * Smarty::setTemplateDir()
     * * Smarty::getTemplateDir()
     * * Smarty::addTemplateDir()
     * @var array|null
     */
    private $template_dir = array();
    /**
     * joined template directory string used in cache keys
     * @var string
     * @internal
     * @deprecated This should not be used, as it should be private.
     */
    public $joined_template_dir = null;
    /**
     * joined config directory string used in cache keys
     * @var string
     * @internal
     * @deprecated This should not be used, as it should be private.
     */
    public $joined_config_dir = null;
    /**
     * Expects a function to use to fetch templates when it cannot be fetched
     * through the default means. The function should use the following
     * prototype:
     *
     * * string $resource_type
     * * string $resource_name
     * * string &$config_content
     * * int &$modified_timestamp
     * * Smarty $smarty
     *
     * It is expected to return a string (a path to a file) or false if no
     * template could be loaded.
     * @var callable
     */
    public $default_template_handler_func = null;
    /**
     * Expects a function to use to fetch config when it cannot be fetched
     * through the default means. The function should use the following
     * prototype:
     *
     * * string $resource_type
     * * string $resource_name
     * * string &$template_content
     * * int &$modified_timestamp
     * * Smarty $smarty
     *
     * It is expected to return a string (a path to a file) or false if no
     * config could be loaded.
     * @var callable
     */
    public $default_config_handler_func = null;
    /**
     * Expects a function to use to fetch plugins when it cannot be fetched
     * through the default means. The function should use the following
     * prototype:
     *
     * * string $resource_type
     * * string $resource_name
     * * string &$config_content
     * * int &$modified_timestamp
     * * Smarty $smarty
     *
     * It is expected to return a string (a path to a file) or false if no
     *  plugin could be loaded.
     * @var callable
     */
    public $default_plugin_handler_func = null;
    /**
     * Directory that compiled templates are stored in. See the following
     * methods instead:
     *
     * * Smarty::setCompileDir()
     * * Smarty::getCompileDir()
     * @var string|null
     */
    private $compile_dir = null;
    /**
     * Directory that plugins are stored in. See the following methods instead:
     *
     * * Smarty::setPluginsDir()
     * * Smarty::getPluginsDir()
     * * Smarty::addPluginsDir()
     * @var string|array|null
     */
    private $plugins_dir = array();
    /**
     * Directory that cached templates are stored in. See the following methods
     * instead:
     *
     * * Smarty::setCacheDir()
     * * Smarty::getCacheDir()
     * @var string|null
     */
    private $cache_dir = null;
    /**
     * Directory that config files are stored in. See the following methods
     * instead:
     *
     * * Smarty::setConfigDir()
     * * Smarty::getConfigDir()
     * * Smarty::addConfigDir()
     * @var string|array|null
     */
    private $config_dir = array();
    /**
     * When true, Brainy will never use the compiled versions of templates,
     * though compiled files will continue to be generated. This overrides
     * `Smarty::$compile_check`. Do not use this in production.
     * @var boolean
     */
    public $force_compile = false;
    /**
     * When true or Smarty::COMPILECHECK_ON, templates and config are checked
     * for changes. If changes exist, the template will be recompiled
     * regardless of whether it has been compiled or cached. Disabling this
     * in production may yield performance improvements if templates and config
     * do not change. If set to Smarty::COMPILECHECK_CACHEMISS, compiled
     * templates are revalidated once a cache file is generated, preventing
     * multiple compile_checks when a cached template expires.
     * @var boolean|int
     * @uses Smarty::COMPILECHECK_ON
     * @uses Smarty::COMPILECHECK_OFF
     * @uses Smarty::COMPILECHECK_CACHEMISS
     */
    public $compile_check = COMPILECHECK_ON;
    /**
     * When true, subdirectories will be created within the cache and compile
     * directories. This is useful for applications with very large numbers of
     * templates, as it minimizes the number of files in each of the
     * directories. This does not work when PHP's safe_mode is set to on.
     * @var boolean
     */
    public $use_sub_dirs = false;
    /**
     * When true, ambiguous resources are allowed.
     * @var boolean
     */
    public $allow_ambiguous_resources = false;
    /**
     * Controls the caching strategy.
     *
     * * Smarty::CACHING_LIFETIME_CURRENT and Smarty::CACHING_LIFETIME_SAVED enable caching.
     * * Smarty::CACHING_LIFETIME_CURRENT uses $cache_lifetime to determine if the cache has expired.
     * * Smarty::CACHING_LIFETIME_SAVED works similarly, except $cache_lifetime is checked at the time the cache is generated.
     * * If $compile_check is true, cached content will be regenerated when the templates or configs change.
     * * If $force_compile is true, caching will be disabled.
     *
     * @var boolean|int
     * @uses Smarty::CACHING_LIFETIME_CURRENT
     * @uses Smarty::CACHING_LIFETIME_SAVED
     */
    public $caching = false;
    /**
     * When true, included templates will be compiled into the templates that
     * they are included in. The {include} function has an attribute that
     * allows this to be performed on a case-by-case basis.
     * @var boolean
     * @see The {include} function
     */
    public $merge_compiled_includes = false;
    /**
     * When true, templates will be compiled into the templates that they
     * inherit from.
     * @var boolean
     */
    public $inheritance_merge_compiled_includes = true;
    /**
     * The amount of time that a cached template will live for.
     *
     * Smarty::$caching must be turned on for this to have an effect.
     *
     * -1 will prevent the cache from expiring. 0 will disable the cache.
     *
     * @var integer
     * @see Smarty::$caching More information on caching
     */
    public $cache_lifetime = 3600;
    /**
     * When true, Brainy will never use the cached versions of templates,
     * though cache files will continue to be generated if Smarty::$caching
     * specifies it. Do not use this in production.
     * @var boolean
     */
    public $force_cache = false;
    /**
     * Set this if you want different sets of cache files for the same
     * templates.
     *
     * @var string
     */
    public $cache_id = null;
    /**
     * Set this if you want different sets of compiled files for the same
     * templates.
     *
     * @var string
     */
    public $compile_id = null;
    /**
     * Indicates whether to perform only safe variable and member lookups.
     * If set to LOOKUP_SAFE, lookups referring to missing variables or
     * members will return a falsey value. LOOKUP_SAFE_WARN will log a warning
     * when the member does not exist.
     *
     * @var int
     * @uses Smarty::LOOKUP_UNSAFE
     * @uses Smarty::LOOKUP_SAFE
     * @uses Smarty::LOOKUP_SAFE_WARN
     */
    public $safe_lookups = Smarty::LOOKUP_UNSAFE;
    /**
     * The left delimiter string
     * @var string
     */
    public $left_delimiter = "{";
    /**
     * The right delimiter string
     * @var string
     */
    public $right_delimiter = "}";

    /**
     * The name of the class to use for security.
     *
     * @var string
     * @deprecated This will be removed to eliminate magic.
     */
    public $security_class = 'Smarty_Security';
    /**
     * The Smarty_Security instance to use as a security policy.
     *
     * @var Smarty_Security
     */
    public $security_policy = null;
    /**
     * When true, browsers will be blocked from accessing compiled or cached
     * templates. Changing this to false is not recommended.
     *
     * {@internal
     * Currently used by Smarty_Internal_Template only.
     * }}
     *
     * @var boolean
     * @todo Evaluate whether this is still useful
     */
    public $direct_access_security = true;
    /**
     * When set, this will be used as the default error reporting level.
     * @var int|null
     */
    public $error_reporting = null;
    /**
     * Internal flag for getTags()
     * @var boolean
     * @internal
     */
    public $get_used_tags = false;

    /**#@+
     * config var settings
     */

    /**
     * When true, repeated values in config files will overwrite previous
     * config values with the same name.
     * @var boolean
     */
    public $config_overwrite = true;
    /**
     * When true, config values of on/true/yes and off/false/no get converted
     * to boolean values automatically rather than returning their string
     * value.
     * @var boolean
     */
    public $config_booleanize = true;
    /**
     * When true, hidden sections in config (sections whose names begin with
     * a dot) will not be hidden.
     * @var boolean
     */
    public $config_read_hidden = false;


    /**
     * When true, concurrent template compilation is disabled.
     * @var boolean
     */
    public $compile_locking = true;
    /**
     * When true, cached templates will not be generated concurrently by
     * multiple requests.
     * @var boolean
     */
    public $cache_locking = false;
    /**
     * The maximum amount of time (in seconds) that a cached template will lock
     * for, when $cache_locking is set.
     * @var float
     */
    public $locking_timeout = 10;


    /**
     * global template functions
     * @var array
     * @internal
     */
    public $template_functions = array();
    /**
     * resource type used if none given
     *
     * Must be an valid key of $registered_resources.
     * @var string
     */
    public $default_resource_type = 'file';
    /**
     * The type of resource to use to load templates.
     *
     * Must be an element of Smarty::$cache_resource_types.
     * @var string
     */
    public $caching_type = 'file';
    /**
     * internal config properties
     * @var array
     * @internal
     */
    public $properties = array();
    /**
     * The type of resource to use to load config.
     *
     * Must be an element of Smarty::$cache_resource_types.
     * @var string
     */
    public $default_config_type = 'file';
    /**
     * cached template objects
     * @var array
     * @internal
     */
    public $template_objects = array();
    /**
     * registered plugins
     * @var array
     * @internal
     */
    public $registered_plugins = array();
    /**
     * The order in which to search for plugins
     * @var string[]
     */
    public $plugin_search_order = array('function', 'block', 'compiler', 'class');
    /**
     * registered objects
     * @var array
     * @internal
     */
    public $registered_objects = array();
    /**
     * registered classes
     * @var array
     * @internal
     */
    public $registered_classes = array();
    /**
     * registered filters
     * @var array
     * @internal
     */
    public $registered_filters = array();
    /**
     * registered resources
     * @var array
     * @internal
     */
    public $registered_resources = array();
    /**
     * resource handler cache
     * @var array
     * @internal
     */
    public $_resource_handlers = array();
    /**
     * registered cache resources
     * @var array
     * @internal
     */
    public $registered_cache_resources = array();
    /**
     * cache resource handler cache
     * @var array
     * @internal
     */
    public $_cacheresource_handlers = array();
    /**
     * autoload filter
     * @var array
     * @internal
     */
    public $autoload_filters = array();
    /**
     * default modifier
     * @var array
     */
    public $default_modifiers = array();
    /**
     * When true, all variables will be implicitly wrapped in
     *
     * ```
     * htmlspecialchars({$variable}, ENT_QUOTES, SMARTY_RESOURCE_CHAR_SET)
     * ```
     *
     * Variables may use the {$variable nofilter} syntax to prevent this behavior.
     * @var boolean
     */
    public $escape_html = false;
    /**
     * global internal smarty vars
     * @var array
     * @internal
     */
    public static $_smarty_vars = array();
    /**
     * start time for execution time calculation
     * @var int
     * @internal
     */
    public $start_time = 0;
    /**
     * default file permissions
     * @var int
     * @todo Make this a constant
     * @internal
     */
    public $_file_perms = 0644;
    /**
     * default dir permissions
     * @var int
     * @todo Make this a constant
     * @internal
     */
    public $_dir_perms = 0771;
    /**
     * block tag hierarchy
     * @var array
     * @internal
     */
    public $_tag_stack = array();
    /**
     * self pointer to Smarty object
     * @var Smarty
     * @internal
     * @todo Investigate whether this is necessary.
     */
    public $smarty;
    /**
     * required by the compiler for BC
     * @var string
     * @internal
     */
    public $_current_file = null;
    /**
     * internal flag to enable parser debugging
     * @var bool
     * @internal
     */
    public $_parserdebug = false;
    /**
     * Saved parameter of merged templates during compilation
     *
     * @var array
     * @internal
     */
    public $merged_templates_func = array();


    /**
     * Initialize a new instance of Brainy.
     */
    public function __construct() {
        // selfpointer needed by some other class methods
        $this->smarty = $this;
        if (is_callable('mb_internal_encoding')) {
            mb_internal_encoding(Smarty::$_CHARSET);
        }
        $this->start_time = microtime(true);
        // set default dirs
        $this->setTemplateDir('.' . DS . 'templates' . DS)
            ->setCompileDir('.' . DS . 'compiled' . DS)
            ->setPluginsDir(SMARTY_PLUGINS_DIR)
            ->setCacheDir('.' . DS . 'cache' . DS)
            ->setConfigDir('.' . DS . 'configs' . DS);

        if (isset($_SERVER['SCRIPT_NAME'])) {
            $this->assignGlobal('SCRIPT_NAME', $_SERVER['SCRIPT_NAME']);
        }
    }

    /**
     * Class destructor
     */
    public function __destruct() {
        // intentionally left blank
    }

    /**
     * @internal
     */
    public function __clone() {
        $this->smarty = $this;
    }

    /**
     * <<magic>> Generic getter.
     *
     * Calls the appropriate getter function.
     * Issues an E_USER_NOTICE if no valid getter is found.
     *
     * @param  string $name property name
     * @return mixed
     * @internal
     */
    public function __get($name) {
        $allowed = array(
            'template_dir' => 'getTemplateDir',
            'config_dir' => 'getConfigDir',
            'plugins_dir' => 'getPluginsDir',
            'compile_dir' => 'getCompileDir',
            'cache_dir' => 'getCacheDir',
        );

        if (isset($allowed[$name])) {
            return $this->{$allowed[$name]}();
        } else {
            trigger_error('Undefined property: '. get_class($this) .'::$'. $name, E_USER_NOTICE);
        }
    }

    /**
     * <<magic>> Generic setter.
     *
     * Calls the appropriate setter function.
     * Issues an E_USER_NOTICE if no valid setter is found.
     *
     * @param string $name  property name
     * @param mixed  $value parameter passed to setter
     * @internal
     */
    public function __set($name, $value) {
        $allowed = array(
            'template_dir' => 'setTemplateDir',
            'config_dir' => 'setConfigDir',
            'plugins_dir' => 'setPluginsDir',
            'compile_dir' => 'setCompileDir',
            'cache_dir' => 'setCacheDir',
        );

        if (isset($allowed[$name])) {
            $this->{$allowed[$name]}($value);
        } else {
            trigger_error('Undefined property: ' . get_class($this) . '::$' . $name, E_USER_NOTICE);
        }
    }

    /**
     * Returns whether a template with the given name exists
     *
     * @param  string  $resource_name template name
     * @return boolean Whether the template exists
     */
    public function templateExists($resource_name) {
        // create template object
        $save = $this->template_objects;
        $tpl = new $this->template_class($resource_name, $this);
        // check if it does exists
        $result = $tpl->source->exists;
        $this->template_objects = $save;

        return $result;
    }

    /**
     * Returns a single or all global variables
     *
     * @param  object $smarty
     * @param  string $varname variable name or null
     * @return mixed Variable value or array of variable values
     */
    public function getGlobal($varname = null) {
        if (isset($varname)) {
            if (isset(self::$global_tpl_vars[$varname])) {
                return self::$global_tpl_vars[$varname]->value;
            } else {
                return '';
            }
        } else {
            $_result = array();
            foreach (self::$global_tpl_vars AS $key => $var) {
                $_result[$key] = $var->value;
            }

            return $_result;
        }
    }

    /**
     * Clear the entire template cache folder.
     *
     * If $expire_time is set, only files older than $expire_time seconds will
     * be deleted. Smarty::CLEAR_EXPIRED can also be passed to delete all
     * expired templates.
     *
     * @param  integer|null $exp_time expiration time
     * @param  string|null  $type     resource type
     * @return integer Number of cache files deleted
     * @uses Smarty::CLEAR_EXPIRED
     */
    public function clearAllCache($exp_time = null, $type = null) {
        // load cache resource and call clearAll
        $_cache_resource = Smarty_CacheResource::load($this, $type);
        Smarty_CacheResource::invalidLoadedCache($this);

        return $_cache_resource->clearAll($this, $exp_time);
    }

    /**
     * Empty cache for a specific template.
     *
     * If $cache_id or $compile_id are specified, they will limit the
     * operation to only cached files with those IDs, respectively. If
     * $expire_time is set, it will be used as the minimum age for files to be
     * deleted. Smarty::CLEAR_EXPIRED can also be passed to delete all expired
     * templates.
     *
     * @param  string  $template_name template name
     * @param  string|null  $cache_id      cache id
     * @param  string|null  $compile_id    compile id
     * @param  integer|null $exp_time      expiration time
     * @param  string|null  $type          resource type
     * @return integer Number of cache files deleted
     * @uses Smarty::CLEAR_EXPIRED
     */
    public function clearCache($template_name, $cache_id = null, $compile_id = null, $exp_time = null, $type = null) {
        // load cache resource and call clear
        $_cache_resource = Smarty_CacheResource::load($this, $type);
        Smarty_CacheResource::invalidLoadedCache($this);

        return $_cache_resource->clear($this, $template_name, $cache_id, $compile_id, $exp_time);
    }

    /**
     * Loads security class and enables security
     *
     * @param  string|Smarty_Security $security_class if a string is used, it must be class-name
     * @return Smarty                 The current Smarty instance for chaining
     * @throws SmartyException        when an invalid class name is provided
     * @deprecated Passing a class name is deprecated.
     */
    public function enableSecurity($security_class = null) {
        if ($security_class instanceof Smarty_Security) {
            $this->security_policy = $security_class;

            return $this;
        } elseif (is_object($security_class)) {
            throw new SmartyException("Class '" . get_class($security_class) . "' must extend Smarty_Security.");
        }
        if ($security_class == null) {
            $security_class = $this->security_class;
        }
        if (!class_exists($security_class)) {
            throw new SmartyException("Security class '$security_class' is not defined");
        } elseif ($security_class !== 'Smarty_Security' && !is_subclass_of($security_class, 'Smarty_Security')) {
            throw new SmartyException("Class '$security_class' must extend Smarty_Security.");
        } else {
            $this->security_policy = new $security_class($this);
        }

        return $this;
    }

    /**
     * Disable the currently loaded security policy.
     * @return Smarty The current Smarty instance for chaining
     */
    public function disableSecurity() {
        $this->security_policy = null;

        return $this;
    }

    /**
     * Set template directory
     *
     * @param string|string[] $template_dir directory(s) of template sources
     * @return Smarty The current Smarty instance for chaining
     */
    public function setTemplateDir($template_dir) {
        $this->template_dir = array();
        foreach ((array) $template_dir as $k => $v) {
            $this->template_dir[$k] = str_replace(array('//','\\\\'), DS, rtrim($v, '/\\')) . DS;
        }

        $this->joined_template_dir = join(DIRECTORY_SEPARATOR, $this->template_dir);

        return $this;
    }

    /**
     * Add a directory to the list of directories where templates are stored
     *
     * @param  string|array    $template_dir directory(s) of template sources
     * @param  string|null     $key          of the array element to assign the template dir to
     * @return Smarty          The current Smarty instance for chaining
     * @throws SmartyException when the given template directory is not valid
     */
    public function addTemplateDir($template_dir, $key = null) {
        // make sure we're dealing with an array
        $this->template_dir = (array) $this->template_dir;

        if (is_array($template_dir)) {
            foreach ($template_dir as $k => $v) {
                $v = str_replace(array('//','\\\\'), DS, rtrim($v, '/\\')) . DS;
                if (is_int($k)) {
                    // indexes are not merged but appended
                    $this->template_dir[] = $v;
                } else {
                    // string indexes are overridden
                    $this->template_dir[$k] = $v;
                }
            }
        } else {
            $v = str_replace(array('//','\\\\'), DS, rtrim($template_dir, '/\\')) . DS;
            if ($key !== null) {
                // override directory at specified index
                $this->template_dir[$key] = $v;
            } else {
                // append new directory
                $this->template_dir[] = $v;
            }
        }
        $this->joined_template_dir = join(DIRECTORY_SEPARATOR, $this->template_dir);

        return $this;
    }

    /**
     * Get template directories
     *
     * @param int|null $index of directory to get, null to get all
     * @return array|string list of template directories, or directory of $index
     */
    public function getTemplateDir($index = null) {
        if ($index !== null) {
            return isset($this->template_dir[$index]) ? $this->template_dir[$index] : null;
        }

        return (array) $this->template_dir;
    }

    /**
     * Set config directory
     *
     * @param string|string[] $config_dir directory(s) of configuration sources
     * @return Smarty The current Smarty instance for chaining
     */
    public function setConfigDir($config_dir) {
        $this->config_dir = array();
        foreach ((array) $config_dir as $k => $v) {
            $this->config_dir[$k] = str_replace(array('//','\\\\'), DS, rtrim($v, '/\\')) . DS;
        }

        $this->joined_config_dir = join(DIRECTORY_SEPARATOR, $this->config_dir);

        return $this;
    }

    /**
     * Add a directory to the list of directories where config files are stored.
     *
     * @param string|array $config_dir directory(s) of config sources
     * @param string|null $key of the array element to assign the config dir to
     * @return Smarty The current Smarty instance for chaining
     */
    public function addConfigDir($config_dir, $key = null) {
        // make sure we're dealing with an array
        $this->config_dir = (array) $this->config_dir;

        if (is_array($config_dir)) {
            foreach ($config_dir as $k => $v) {
                $v = str_replace(array('//','\\\\'), DS, rtrim($v, '/\\')) . DS;
                if (is_int($k)) {
                    // indexes are not merged but appended
                    $this->config_dir[] = $v;
                } else {
                    // string indexes are overridden
                    $this->config_dir[$k] = $v;
                }
            }
        } else {
            $v = str_replace(array('//','\\\\'), DS, rtrim($config_dir, '/\\')) . DS;
            if ($key !== null) {
                // override directory at specified index
                $this->config_dir[$key] = rtrim($v, '/\\') . DS;
            } else {
                // append new directory
                $this->config_dir[] = rtrim($v, '/\\') . DS;
            }
        }

        $this->joined_config_dir = join(DIRECTORY_SEPARATOR, $this->config_dir);

        return $this;
    }

    /**
     * Get config directory
     *
     * @param int|null $index of directory to get, null to get all
     * @return array|string configuration directory
     */
    public function getConfigDir($index = null) {
        if ($index !== null) {
            return isset($this->config_dir[$index]) ? $this->config_dir[$index] : null;
        }

        return (array) $this->config_dir;
    }

    /**
     * Set plugins directory
     *
     * @param string|array $plugins_dir directory(s) of plugins
     * @return Smarty current Smarty instance for chaining
     */
    public function setPluginsDir($plugins_dir) {
        $this->plugins_dir = array();
        foreach ((array) $plugins_dir as $k => $v) {
            $this->plugins_dir[$k] = rtrim($v, '/\\') . DS;
        }

        return $this;
    }

    /**
     * Add a directory to the list of directories where plugins are stored
     *
     * @param string|array $plugins_dir plugins folder
     * @return Smarty current Smarty instance for chaining
     */
    public function addPluginsDir($plugins_dir) {
        // make sure we're dealing with an array
        $this->plugins_dir = (array) $this->plugins_dir;

        if (is_array($plugins_dir)) {
            foreach ($plugins_dir as $k => $v) {
                if (is_int($k)) {
                    // indexes are not merged but appended
                    $this->plugins_dir[] = rtrim($v, '/\\') . DS;
                } else {
                    // string indexes are overridden
                    $this->plugins_dir[$k] = rtrim($v, '/\\') . DS;
                }
            }
        } else {
            // append new directory
            $this->plugins_dir[] = rtrim($plugins_dir, '/\\') . DS;
        }

        $this->plugins_dir = array_unique($this->plugins_dir);

        return $this;
    }

    /**
     * Get plugin directories
     *
     * @return string[] List of plugin directories
     */
    public function getPluginsDir() {
        return (array) $this->plugins_dir;
    }

    /**
     * Set compile directory
     *
     * @param  string $compile_dir directory to store compiled templates in
     * @return Smarty current Smarty instance for chaining
     */
    public function setCompileDir($compile_dir) {
        $this->compile_dir = rtrim($compile_dir, '/\\') . DS;
        if (!isset(Smarty::$_muted_directories[$this->compile_dir])) {
            Smarty::$_muted_directories[$this->compile_dir] = null;
        }

        return $this;
    }

    /**
     * Get compiled template directory
     *
     * @return string Path to compiled templates
     */
    public function getCompileDir() {
        return $this->compile_dir;
    }

    /**
     * Set cache directory
     *
     * @param  string $cache_dir directory to store cached templates in
     * @return Smarty current Smarty instance for chaining
     */
    public function setCacheDir($cache_dir) {
        $this->cache_dir = rtrim($cache_dir, '/\\') . DS;
        if (!isset(Smarty::$_muted_directories[$this->cache_dir])) {
            Smarty::$_muted_directories[$this->cache_dir] = null;
        }

        return $this;
    }

    /**
     * Get cached template directory
     *
     * @return string Path of cache directory
     */
    public function getCacheDir() {
        return $this->cache_dir;
    }

    /**
     * Set default modifiers
     *
     * @param  array|string $modifiers modifier or list of modifiers to set
     * @return Smarty       current Smarty instance for chaining
     */
    public function setDefaultModifiers($modifiers) {
        $this->default_modifiers = (array) $modifiers;

        return $this;
    }

    /**
     * Add default modifiers
     *
     * @param  array|string $modifiers modifier or list of modifiers to add
     * @return Smarty       current Smarty instance for chaining
     */
    public function addDefaultModifiers($modifiers) {
        if (is_array($modifiers)) {
            $this->default_modifiers = array_merge($this->default_modifiers, $modifiers);
        } else {
            $this->default_modifiers[] = $modifiers;
        }

        return $this;
    }

    /**
     * Get default modifiers
     *
     * @return array list of default modifiers
     */
    public function getDefaultModifiers() {
        return $this->default_modifiers;
    }


    /**
     * Set autoload filters
     *
     * @param  array  $filters filters to load automatically
     * @param  string|null $type "pre", "output", … specify the filter type to set. Defaults to none treating $filters' keys as the appropriate types
     * @return Smarty current Smarty instance for chaining
     * @see Smarty::FILTER_POST         Allows post filtering
     * @see Smarty::FILTER_PRE          Allows pre filtering
     * @see Smarty::FILTER_OUTPUT       Allows output filtering
     * @see Smarty::FILTER_VARIABLE     Allows variable filtering
     */
    public function setAutoloadFilters($filters, $type = null) {
        if ($type !== null) {
            $this->autoload_filters[$type] = (array) $filters;
        } else {
            $this->autoload_filters = (array) $filters;
        }

        return $this;
    }

    /**
     * Add autoload filters
     *
     * @param  array  $filters filters to load automatically
     * @param  string $type    "pre", "output", … specify the filter type to set. Defaults to none treating $filters' keys as the appropriate types
     * @return Smarty current Smarty instance for chaining
     */
    public function addAutoloadFilters($filters, $type=null) {
        if ($type !== null) {
            if (!empty($this->autoload_filters[$type])) {
                $this->autoload_filters[$type] = array_merge($this->autoload_filters[$type], (array) $filters);
            } else {
                $this->autoload_filters[$type] = (array) $filters;
            }
        } else {
            foreach ((array) $filters as $key => $value) {
                if (!empty($this->autoload_filters[$key])) {
                    $this->autoload_filters[$key] = array_merge($this->autoload_filters[$key], (array) $value);
                } else {
                    $this->autoload_filters[$key] = (array) $value;
                }
            }
        }

        return $this;
    }

    /**
     * Get autoload filters
     *
     * @param  string $type type of filter to get autoloads for. Defaults to all autoload filters
     * @return array  array( 'type1' => array( 'filter1', 'filter2', … ) ) or array( 'filter1', 'filter2', …) if $type was specified
     */
    public function getAutoloadFilters($type=null) {
        if ($type !== null) {
            return isset($this->autoload_filters[$type]) ? $this->autoload_filters[$type] : array();
        }

        return $this->autoload_filters;
    }

    /**
     * Creates a template object.
     *
     * This creates a template object which later can be rendered by the
     * Smarty::display() or Smarty::fetch() method.
     *
     * @param  string  $template   the resource handle of the template file
     * @param  mixed   $cache_id   cache id to be used with this template
     * @param  mixed   $compile_id compile id to be used with this template
     * @param  object|null  $parent     Parent scope to assign to the template
     * @param  boolean|null $do_clone   When true, the Smarty object will be cloned
     * @return object  template object
     */
    public function createTemplate($template, $cache_id = null, $compile_id = null, $parent = null, $do_clone = true) {
        if ($cache_id !== null && (is_object($cache_id) || is_array($cache_id))) {
            $parent = $cache_id;
            $cache_id = null;
        }
        if ($parent !== null && is_array($parent)) {
            $data = $parent;
            $parent = null;
        } else {
            $data = null;
        }
        // default to cache_id and compile_id of Smarty object
        $cache_id = $cache_id === null ? $this->cache_id : $cache_id;
        $compile_id = $compile_id === null ? $this->compile_id : $compile_id;
        // already in template cache?
        if ($this->allow_ambiguous_resources) {
            $_templateId = Smarty_Resource::getUniqueTemplateName($this, $template) . $cache_id . $compile_id;
        } else {
            $_templateId = $this->joined_template_dir . '#' . $template . $cache_id . $compile_id;
        }
        if (isset($_templateId[150])) {
            $_templateId = sha1($_templateId);
        }
        if ($do_clone) {
            if (isset($this->template_objects[$_templateId])) {
                // return cached template object
                $tpl = clone $this->template_objects[$_templateId];
                $tpl->smarty = clone $tpl->smarty;
                $tpl->parent = $parent;
                $tpl->tpl_vars = array();
                $tpl->config_vars = array();
            } else {
                $tpl = new $this->template_class($template, clone $this, $parent, $cache_id, $compile_id);
            }
        } else {
            if (isset($this->template_objects[$_templateId])) {
                // return cached template object
                $tpl = $this->template_objects[$_templateId];
                $tpl->parent = $parent;
                $tpl->tpl_vars = array();
                $tpl->config_vars = array();
            } else {
                $tpl = new $this->template_class($template, $this, $parent, $cache_id, $compile_id);
            }
        }
        // fill data if present
        if (!empty($data) && is_array($data)) {
            // set up variable values
            foreach ($data as $_key => $_val) {
                $tpl->tpl_vars[$_key] = new Smarty_variable($_val);
            }
        }

        return $tpl;
    }


    /**
     * Takes unknown classes and loads plugin files for them
     * class name format: Smarty_PluginType_PluginName
     * plugin filename format: plugintype.pluginname.php
     *
     * @param  string $plugin_name class plugin name to load
     * @param  bool   $check       check if already loaded
     * @return string |boolean filepath of loaded file or false
     */
    public function loadPlugin($plugin_name, $check = false) {
        // if function or class exists, exit silently (already loaded)
        if ($check && (is_callable($plugin_name) || class_exists($plugin_name, false))) {
            return true;
        }
        // Plugin name is expected to be: Smarty_[Type]_[Name]
        $_name_parts = explode('_', $plugin_name, 3);
        // class name must have three parts to be valid plugin
        // count($_name_parts) < 3 === !isset($_name_parts[2])
        if (!isset($_name_parts[2]) || strtolower($_name_parts[0]) !== 'smarty') {
            throw new SmartyException("plugin {$plugin_name} is not a valid name format");

            return false;
        }
        // if type is "internal", get plugin from sysplugins
        if (strtolower($_name_parts[1]) == 'internal') {
            $file = SMARTY_SYSPLUGINS_DIR . strtolower($plugin_name) . '.php';
            if (file_exists($file)) {
                require_once($file);

                return $file;
            } else {
                return false;
            }
        }
        // plugin filename is expected to be: [type].[name].php
        $_plugin_filename = "{$_name_parts[1]}.{$_name_parts[2]}.php";

        $_stream_resolve_include_path = function_exists('stream_resolve_include_path');

        // loop through plugin dirs and find the plugin
        foreach ($this->getPluginsDir() as $_plugin_dir) {
            $names = array(
                $_plugin_dir . $_plugin_filename,
                $_plugin_dir . strtolower($_plugin_filename),
            );
            foreach ($names as $file) {
                if (file_exists($file)) {
                    require_once($file);

                    return $file;
                }
                if ($this->use_include_path && !preg_match('/^([\/\\\\]|[a-zA-Z]:[\/\\\\])/', $_plugin_dir)) {
                    // try PHP include_path
                    if ($_stream_resolve_include_path) {
                        $file = stream_resolve_include_path($file);
                    } else {
                        $file = Smarty_Internal_Get_Include_Path::getIncludePath($file);
                    }

                    if ($file !== false) {
                        require_once($file);

                        return $file;
                    }
                }
            }
        }
        // no plugin loaded
        return false;
    }

    /**
     * Compile all template files
     *
     * @param  string  $extension Optional file extension
     * @param  bool    $force_compile Optional boolean that compiles all files instead of modified files
     * @param  int     $time_limit Optional integer to specify a runtime limit in seconds for the compilation process
     * @param  int     $max_errors Optional integer to set an error limit. If more errors occur, the function will abort
     * @return integer number of template files recompiled
     */
    public function compileAllTemplates($extension = '.tpl', $force_compile = false, $time_limit = 0, $max_errors = null) {
        return Smarty_Internal_Utility::compileAllTemplates($extension, $force_compile, $time_limit, $max_errors, $this);
    }

    /**
     * Compile all config files
     *
     * @param  string  $extension Optional file extension
     * @param  bool    $force_compile Optional boolean that compiles all files instead of modified files
     * @param  int     $time_limit Optional integer to specify a runtime limit in seconds for the compilation process
     * @param  int     $max_errors Optional integer to set an error limit. If more errors occur, the function will abort
     * @return integer number of config files recompiled
     */
    public function compileAllConfig($extension = '.conf', $force_compile = false, $time_limit = 0, $max_errors = null) {
        return Smarty_Internal_Utility::compileAllConfig($extension, $force_compile, $time_limit, $max_errors, $this);
    }

    /**
     * Delete a compiled template file.
     *
     * This clears the compiled version of the specified template resource, or
     * all compiled template files if one is not specified. If you pass a
     * $compile_id only the compiled template for this specific $compile_id is
     * cleared. If you pass an $exp_time, then only compiled templates older
     * than $exp_time seconds are cleared, by default all compiled templates
     * are cleared regardless of their age. This function is for advanced use
     * only, not normally needed.
     *
     * @param  string|null  $resource_name template name
     * @param  string|null  $compile_id    compile id
     * @param  integer|null $exp_time      expiration time
     * @return integer number of template files deleted
     */
    public function clearCompiledTemplate($resource_name = null, $compile_id = null, $exp_time = null) {
        return Smarty_Internal_Utility::clearCompiledTemplate($resource_name, $compile_id, $exp_time, $this);
    }


    /**
     * Return array of tag/attributes of all tags used by an template
     *
     * @param  object $template The template object
     * @return array of tag/attributes
     * @deprecated This method has never been fully supported.
     */
    public function getTags(Smarty_Internal_Template $template) {
        return Smarty_Internal_Utility::getTags($template);
    }

    /**
     * Run installation test
     *
     * This function verifies that all required working folders of the current
     * installation can be accessed appropriately.
     *
     * @param  array   $errors Array to write errors into, rather than outputting them
     * @return boolean true if setup is fine, false if something is wrong
     */
    public function testInstall(&$errors=null) {
        return Smarty_Internal_Utility::testInstall($this, $errors);
    }

    /**
     * Error Handler to mute expected messages
     *
     * @link http://php.net/set_error_handler
     * @param  integer $errno Error level
     * @return boolean
     */
    public static function mutingErrorHandler($errno, $errstr, $errfile, $errline, $errcontext) {
        $_is_muted_directory = false;

        // add the SMARTY_DIR to the list of muted directories
        if (!isset(Smarty::$_muted_directories[SMARTY_DIR])) {
            $smarty_dir = realpath(SMARTY_DIR);
            if ($smarty_dir !== false) {
                Smarty::$_muted_directories[SMARTY_DIR] = array(
                    'file' => $smarty_dir,
                    'length' => strlen($smarty_dir),
                );
            }
        }

        // walk the muted directories and test against $errfile
        foreach (Smarty::$_muted_directories as $key => &$dir) {
            // var_dump($dir);
            // var_dump($errfile);
            if (!$dir) {
                // resolve directory and length for speedy comparisons
                $file = realpath($key);
                if ($file === false) {
                    // this directory does not exist, remove and skip it
                    unset(Smarty::$_muted_directories[$key]);
                    continue;
                }
                $dir = array(
                    'file' => $file,
                    'length' => strlen($file),
                );
            }
            // var_dump(strncmp($errfile, $dir['file'], $dir['length']));
            if (strncmp($errfile, $dir['file'], $dir['length']) === 0) {
                $_is_muted_directory = true;
                break;
            }
        }

        // pass to next error handler if this error did not occur inside SMARTY_DIR
        // or the error was within smarty but masked to be ignored
        if (!$_is_muted_directory || ($errno && $errno & error_reporting())) {
            if (Smarty::$_previous_error_handler) {
                return call_user_func(Smarty::$_previous_error_handler, $errno, $errstr, $errfile, $errline, $errcontext);
            } else {
                return false;
            }
        }
    }

    /**
     * Enable error handler to mute expected messages
     *
     * error muting is done because some people implemented custom error_handlers using
     * http://php.net/set_error_handler and for some reason did not understand the following paragraph:
     *
     *     It is important to remember that the standard PHP error handler is completely bypassed for the
     *     error types specified by error_types unless the callback function returns FALSE.
     *     error_reporting() settings will have no effect and your error handler will be called regardless -
     *     however you are still able to read the current value of error_reporting and act appropriately.
     *     Of particular note is that this value will be 0 if the statement that caused the error was
     *     prepended by the @ error-control operator.
     *
     * A call to @filemtime() is used instead of file_exists() and filemtime() in some places. Reasons include:
     *
     * - @filemtime() is almost twice as fast as using an additional file_exists()
     * - between file_exists() and filemtime() a possible race condition is opened,
     *   which does not exist using the simple \@filemtime() approach.
     *
     * @return void
     * @deprecated Brainy will not support misbehaving code.
     */
    public static function muteExpectedErrors() {
        $error_handler = array('Smarty', 'mutingErrorHandler');
        $previous = set_error_handler($error_handler);

        // avoid dead loops
        if ($previous !== $error_handler) {
            Smarty::$_previous_error_handler = $previous;
        }
    }

    /**
     * Disable error handler muting expected messages
     *
     * @return void
     * @deprecated Brainy will not support misbehaving code.
     */
    public static function unmuteExpectedErrors() {
        restore_error_handler();
    }
}

// Check if we're running on windows
Smarty::$_IS_WINDOWS = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';

// let PCRE (preg_*) treat strings as ISO-8859-1 if we're not dealing with UTF-8
if (Smarty::$_CHARSET !== 'UTF-8') {
    Smarty::$_UTF8_MODIFIER = '';
}

/**
 * Smarty exception class
 * @package Brainy
 */
class SmartyException extends Exception {
    /**
     * Whether to HTML escape the contents of the exception.
     * @var boolean
     */
    public static $escape = false;

    /**
     * @internal
     */
    public function __toString() {
        return ' --> Smarty: ' . (self::$escape ? htmlentities($this->message) : $this->message)  . ' <-- ';
    }
}

/**
 * Smarty compiler exception class
 * @package Brainy
 */
class SmartyCompilerException extends SmartyException {
    /**
     * @internal
     */
    public function __toString() {
        return ' --> Smarty Compiler: ' . $this->message . ' <-- ';
    }
    /**
     * The line number of the template error
     * @var int|null
     */
    public $line = null;
    /**
     * The template source snippet relating to the error
     * @var string|null
     */
    public $source = null;
    /**
     * The raw text of the error message
     * @var string|null
     */
    public $desc = null;
    /**
     * The resource identifier or template name
     * @var string|null
     */
    public $template = null;
}

/**
 * Autoloader
 * @internal
 */
function smartyAutoload($class) {
    $_class = strtolower($class);
    static $_classes = array(
        'smarty_config_source' => true,
        'smarty_config_compiled' => true,
        'smarty_security' => true,
        'smarty_cacheresource' => true,
        'smarty_cacheresource_custom' => true,
        'smarty_cacheresource_keyvaluestore' => true,
        'smarty_resource' => true,
        'smarty_resource_custom' => true,
        'smarty_resource_uncompiled' => true,
        'smarty_resource_recompiled' => true,
    );

    if (!strncmp($_class, 'smarty_internal_', 16) || isset($_classes[$_class])) {
        include SMARTY_SYSPLUGINS_DIR . $_class . '.php';
    }
}
