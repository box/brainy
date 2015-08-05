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
 * @link https://github.com/box/brainy
 * @copyright 2008 New Digital Group, Inc.
 * @copyright 2014 Box, Inc.
 * @author Monte Ohrt <monte at ohrt dot com>
 * @author Uwe Tews
 * @author Rodney Rehm
 * @author Matt Basta
 * @package Brainy
 */

namespace \Box\Brainy;


/**
 * set SMARTY_DIR to absolute path to Smarty library files.
 * Sets SMARTY_DIR only if user application has not already defined it.
 */
if (!defined('SMARTY_DIR')) {
    define('SMARTY_DIR', dirname(__FILE__) . DIRECTORY_SEPARATOR);
}

/**
 * set SMARTY_SYSPLUGINS_DIR to absolute path to Smarty internal plugins.
 * Sets SMARTY_SYSPLUGINS_DIR only if user application has not already defined it.
 */
if (!defined('SMARTY_SYSPLUGINS_DIR')) {
    define('SMARTY_SYSPLUGINS_DIR', SMARTY_DIR . 'sysplugins' . DIRECTORY_SEPARATOR);
}
if (!defined('SMARTY_PLUGINS_DIR')) {
    define('SMARTY_PLUGINS_DIR', SMARTY_DIR . 'plugins' . DIRECTORY_SEPARATOR);
}
if (!defined('SMARTY_MBSTRING')) {
    define('SMARTY_MBSTRING', function_exists('mb_split'));
}


if (!function_exists('smarty_safe_array_lookup')) {

    /**
     * Performs a safe lookup of an array member with a safety value.
     * @param mixed $arr
     * @param string|int $key
     * @param int $safety
     * @return mixed
     * @throws InvalidArgumentException
     * @see Brainy::$safe_lookups
     * @internal
     */
    function smarty_safe_array_lookup($arr, $key, $safety) {
        if (is_array($arr) && isset($arr[$key])) {
            return $arr[$key];
        }
        if ($safety === Brainy::LOOKUP_SAFE_WARN) {
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
     * @see Brainy::$safe_lookups
     * @internal
     */
    function smarty_safe_var_lookup($arr, $key, $safety) {
        if (isset($arr[$key])) {
            return $arr[$key];
        }
        if ($safety === Brainy::LOOKUP_SAFE_WARN) {
            trigger_error('Could not find variable "' . $key . '" in Brainy template.', E_USER_WARNING);
        }
        return $arr[$key] = new Smarty_Variable;
    }
}


/**
 * This is the main Brainy class
 * @package Brainy
 */
class Brainy extends Smarty_Internal_TemplateBase {
    /**
     * The current version string for the current version of Brainy.
     * @var string
     */
    const SMARTY_VERSION = 'Brainy-3.0.0';

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
     * Disables checking templates of compiled files to detect changes.
     * @var int
     * @see Brainy::$compile_check Usage Information
     */
    const COMPILECHECK_OFF = 0;
    /**
     * Enables checking templates of compiled files to detect changes.
     * @var int
     * @see Brainy::$compile_check Usage Information
     */
    const COMPILECHECK_ON = 1;
    /**
     * Enables checking templates of compiled files to detect changes when a
     * cache miss occurs.
     * @var int
     * @see Brainy::$compile_check Usage Information
     */
    const COMPILECHECK_CACHEMISS = 2;


    /**
     * Represents post-filtering
     * @var string
     * @see Brainy::setAutoloadFilters() Usage in setAutoloadFilters
     * @see Brainy::addAutoloadFilters() Usage in addAutoloadFilters
     */
    const FILTER_POST = 'post';
    /**
     * Represents pre-filtering
     * @var string
     * @see Brainy::setAutoloadFilters() Usage in setAutoloadFilters
     * @see Brainy::addAutoloadFilters() Usage in addAutoloadFilters
     */
    const FILTER_PRE = 'pre';
    /**
     * Represents output-filtering
     * @var string
     * @see Brainy::setAutoloadFilters() Usage in setAutoloadFilters
     * @see Brainy::addAutoloadFilters() Usage in addAutoloadFilters
     */
    const FILTER_OUTPUT = 'output';
    /**
     * Represents variable-filtering
     * @var string
     * @see Brainy::setAutoloadFilters() Usage in setAutoloadFilters
     * @see Brainy::addAutoloadFilters() Usage in addAutoloadFilters
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
     * @see Brainy::$safe_lookups Usage Information
     */
    const LOOKUP_UNSAFE = 0;
    /**
     * When used, lookups will be safe (default Smarty 2 behavior)
     * @var int
     * @see Brainy::$safe_lookups Usage Information
     */
    const LOOKUP_SAFE = 1;
    /**
     * When used, lookups will be safe and a warning will be raised using
     * trigger_error.
     * @var int
     * @see Brainy::$safe_lookups Usage Information
     */
    const LOOKUP_SAFE_WARN = 2;

    /**
     * When used, assignments under SmartyBC will use the Smarty 2 assignment
     * semantics. Assignments will preserve references to previously assigned
     * values.
     * @var int
     * @see Brainy::$assignment_compat Usage Information
     */
    const ASSIGN_COMPAT = 0;
    /**
     * When used, assignments will always use Smarty 3 semantics, regardless of
     * whether SmartyBC is used.
     * @var int
     * @see Brainy::$assignment_compat Usage Information
     */
    const ASSIGN_NO_COMPAT = 1;


    /**
     * assigned global tpl vars
     * @internal
     */
    public static $global_tpl_vars = array();

    /**
     * Flag denoting if Multibyte String functions are available
     * @internal
     */
    public static $_MBSTRING = SMARTY_MBSTRING;
    /**
     * The character set to adhere to (e.g. "UTF-8")
     * @internal
     * @deprecated The charset should be assumed to be UTF-8.
     */
    public static $_CHARSET = 'UTF-8';
    /**
     * The date format to be used internally
     * (accepts date() and strftime())
     * @internal
     */
    public static $_DATE_FORMAT = '%b %e, %Y';
    /**
     * Flag denoting if PCRE should run in UTF-8 mode
     * @internal
     */
    public static $_UTF8_MODIFIER = 'u';

    /**
     * Sets the default assignment location for a variable. By default,
     * variables are assigned to the local scope. In some contexts, it may
     * be desirable for variable assignment to take place in the global scope.
     * By setting this property, the default scope can be set. This affects the
     * following:
     *
     * * The `assign()` method on any Smarty_Internal_Data instance
     * * The `{assign}` function
     * * The `{capture}` function (it uses `assign()`)
     *
     * @var int
     * @uses Brainy::SCOPE_LOCAL
     * @uses Brainy::SCOPE_PARENT
     * @uses Brainy::SCOPE_ROOT
     * @uses Brainy::SCOPE_GLOBAL
     */
    public static $default_assign_scope = Brainy::SCOPE_LOCAL;
    /**
     * SmartyBC's usage will--by default--use Smarty 2's semantics for varaible
     * assignment. This means that if a variable is already defined, a clone of
     * the existing variable will be made to preserve references.
     *
     * If this value is changed to `Brainy::ASSIGN_NO_COMPAT`, Smarty 3's
     * assignment semantics will always be used regardless of whether SmartyBC
     * is used or not.
     *
     * This is considered at compile time and not at runtime.
     *
     * @var int
     * @uses Brainy::ASSIGN_COMPAT
     * @uses Brainy::ASSIGN_NO_COMPAT
     * @see Smarty_Internal_Compile_Assign
     */
    public static $assignment_compat = Brainy::ASSIGN_COMPAT;
    /**
     * This member allows the enforcement of a modifier being applied to
     * expressions that are output. If one of the modifiers in the list is not
     * used, a SmartyException will be raised.
     *
     * @var string[]
     */
    public static $enforce_expression_modifiers = array('escape', 'unsafe_noescape');
    /**
     * When Brainy::$enforce_expression_modifiers is set and this member is set
     * to true, even static values will require a modifier. For example:
     *
     *     {'foo'}
     *
     * The above would--by default--not require a modifier. When this is set to
     * true, it would.
     *
     * @var bool
     * @uses Brainy::$enforce_expression_modifiers
     */
    public static $enforce_modifiers_on_static_expressions = false;
    /**
     * Setting this option enables strict mode. This removes access to
     * deprecated, unperformant, or otherwise suspect features that were
     * previously available to Smarty templates.
     *
     * @var bool
     */
    public static $strict_mode = false;



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
     * Directory that templates are stored in. See the following
     * methods instead:
     *
     * * Brainy::setTemplateDir()
     * * Brainy::getTemplateDir()
     * * Brainy::addTemplateDir()
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
     * Expects a function to use to fetch templates when it cannot be fetched
     * through the default means. The function should use the following
     * prototype:
     *
     * * string $resource_type
     * * string $resource_name
     * * int &$modified_timestamp
     * * Smarty $smarty
     *
     * It is expected to return a string (a path to a file) or false if no
     * template could be loaded.
     * @var callable
     */
    public $default_template_handler_func = null;
    /**
     * Expects a function to use to fetch plugins when it cannot be fetched
     * through the default means. The function should use the following
     * prototype:
     *
     * * string $resource_type
     * * string $resource_name
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
     * * Brainy::setCompileDir()
     * * Brainy::getCompileDir()
     * @var string|null
     */
    private $compile_dir = null;
    /**
     * Directory that plugins are stored in. See the following methods instead:
     *
     * * Brainy::setPluginsDir()
     * * Brainy::getPluginsDir()
     * * Brainy::addPluginsDir()
     * @var string|array|null
     */
    private $plugins_dir = array();
    /**
     * When true, Brainy will never use the compiled versions of templates,
     * though compiled files will continue to be generated. This overrides
     * `Brainy::$compile_check`. Do not use this in production.
     * @var boolean
     */
    public $force_compile = false;
    /**
     * When true or Brainy::COMPILECHECK_ON, templates are checked
     * for changes. If changes exist, the template will be recompiled
     * regardless of whether it has been compiled or cached. Disabling this
     * in production may yield performance improvements if templates
     * do not change.
     * @var boolean|int
     * @uses Brainy::COMPILECHECK_ON
     * @uses Brainy::COMPILECHECK_OFF
     */
    public $compile_check = Brainy::COMPILECHECK_ON;
    /**
     * When true, subdirectories will be created within compile directory.
     * This is useful for applications with very large numbers of
     * templates, as it minimizes the number of files in each of the
     * directories. This does not work when PHP's safe_mode is set to on.
     * @var boolean
     */
    public $use_sub_dirs = false;
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
     * @uses Brainy::LOOKUP_UNSAFE
     * @uses Brainy::LOOKUP_SAFE
     * @uses Brainy::LOOKUP_SAFE_WARN
     */
    public $safe_lookups = Brainy::LOOKUP_UNSAFE;
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
     * The Smarty_Security instance to use as a security policy.
     *
     * @var Smarty_Security
     */
    public $security_policy = null;
    /**
     * Internal flag for getTags()
     * @var boolean
     * @internal
     */
    public $get_used_tags = false;

    /**
     * When true, concurrent template compilation is disabled.
     * @var boolean
     */
    public $compile_locking = true;


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
     * internal config properties
     * @var array
     * @internal
     */
    public $properties = array();
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
     * htmlspecialchars({$variable}, ENT_QUOTES, 'UTF-8')
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
            mb_internal_encoding(Brainy::$_CHARSET);
        }
        // set default dirs
        $this->setTemplateDir('.' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR)
            ->setCompileDir('.' . DIRECTORY_SEPARATOR . 'compiled' . DIRECTORY_SEPARATOR)
            ->setPluginsDir(SMARTY_PLUGINS_DIR);
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
     * @param  string|null|void $varname variable name or null
     * @return mixed Variable value or array of variable values
     */
    public function getGlobal($varname = null) {
        if ($varname) {
            if (!isset(self::$global_tpl_vars[$varname])) {
                return '';
            }
            return self::$global_tpl_vars[$varname]->value;
        }
        $_result = array();
        foreach (self::$global_tpl_vars as $key => $var) {
            $_result[$key] = $var->value;
        }

        return $_result;
    }

    /**
     * Loads security class and enables security
     *
     * @param  Smarty_Security|null|void $security_class
     * @return Smarty The current Smarty instance for chaining
     * @throws SmartyException when an invalid class is provided
     */
    public function enableSecurity($security_class = null) {
        if ($security_class === null) {
            $security_class = new Smarty_Security($this);
        }
        if (!($security_class instanceof Smarty_Security)) {
            throw new SmartyException('Unknown security object provided');
        }
        $this->security_policy = $security_class;
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
            $this->template_dir[$k] = preg_replace('#(\w+)(/|\\\\){1,}#', '$1$2', rtrim($v, '/\\')) . DIRECTORY_SEPARATOR;
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
                $v = preg_replace('#(\w+)(/|\\\\){1,}#', '$1$2', rtrim($v, '/\\')) . DIRECTORY_SEPARATOR;
                if (is_int($k)) {
                    // indexes are not merged but appended
                    $this->template_dir[] = $v;
                } else {
                    // string indexes are overridden
                    $this->template_dir[$k] = $v;
                }
            }
        } else {
            $v = preg_replace('#(\w+)(/|\\\\){1,}#', '$1$2', rtrim($template_dir, '/\\')) . DIRECTORY_SEPARATOR;
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
     * Set plugins directory
     *
     * @param string|array $plugins_dir directory(s) of plugins
     * @return Smarty current Smarty instance for chaining
     */
    public function setPluginsDir($plugins_dir) {
        $this->plugins_dir = array();
        foreach ((array) $plugins_dir as $k => $v) {
            $this->plugins_dir[$k] = rtrim($v, '/\\') . DIRECTORY_SEPARATOR;
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
                    $this->plugins_dir[] = rtrim($v, '/\\') . DIRECTORY_SEPARATOR;
                } else {
                    // string indexes are overridden
                    $this->plugins_dir[$k] = rtrim($v, '/\\') . DIRECTORY_SEPARATOR;
                }
            }
        } else {
            // append new directory
            $this->plugins_dir[] = rtrim($plugins_dir, '/\\') . DIRECTORY_SEPARATOR;
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
        $this->compile_dir = rtrim($compile_dir, '/\\') . DIRECTORY_SEPARATOR;
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
     * @see Brainy::FILTER_POST         Allows post filtering
     * @see Brainy::FILTER_PRE          Allows pre filtering
     * @see Brainy::FILTER_OUTPUT       Allows output filtering
     * @see Brainy::FILTER_VARIABLE     Allows variable filtering
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
     * Brainy::display() or Brainy::fetch() method.
     *
     * @param  string  $template   the resource handle of the template file
     * @param  mixed|void   $cache_id   cache id to be used with this template
     * @param  mixed|void   $compile_id compile id to be used with this template
     * @param  object|null|void  $parent     Parent scope to assign to the template
     * @param  boolean|void $do_clone   When true, the Smarty object will be cloned
     * @return Smarty_Internal_Template
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

        // already in template cache?
        $_templateId = $this->joined_template_dir . '#' . $template . $compile_id;
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
            } else {
                $tpl = new $this->template_class($template, clone $this, $parent, $compile_id);
            }
        } else {
            if (isset($this->template_objects[$_templateId])) {
                // return cached template object
                $tpl = $this->template_objects[$_templateId];
                $tpl->parent = $parent;
                $tpl->tpl_vars = array();
            } else {
                $tpl = new $this->template_class($template, $this, $parent, $compile_id);
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
                if (!file_exists($file)) {
                    continue;
                }
                require_once($file);
                return $file;
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
     * A stub function that is called whenever a template is included. This is
     * included to allow implementers to detect when a template was included by
     * Brainy.
     * @param string $templatePath The path to the included template
     * @return void
     */
    public function fetchedTemplate($templatePath) {}
}

// let PCRE (preg_*) treat strings as ISO-8859-1 if we're not dealing with UTF-8
if (Brainy::$_CHARSET !== 'UTF-8') {
    Brainy::$_UTF8_MODIFIER = '';
}
