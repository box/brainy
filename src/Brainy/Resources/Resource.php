<?php
/**
 * Smarty Resource Plugin
 *
 * @package Brainy
 * @subpackage TemplateResources
 * @author Rodney Rehm
 */

namespace Box\Brainy\Resources;

use \Box\Brainy\Brainy;
use \Box\Brainy\Templates\Template;
use \Box\Brainy\Templates\TemplateSource;


abstract class Resource
{
    /**
     * cache for TemplateSource instances
     * @var array
     */
    public static $sources = array();
    /**
     * cache for Smarty_Template_Compiled instances
     * @var array
     */
    public static $compileds = array();
    /**
     * cache for \Box\Brainy\Resources\Resource instances
     * @var array
     */
    public static $resources = array();
    /**
     * resource types provided by the core
     * @var array
     */
    protected static $sysplugins = array(
        'file' => true,
        'string' => true,
        'extends' => true,
        'eval' => true,
    );

    /**
     * Load template's source into current template object
     *
     * {@internal The loaded source is assigned to $_template->source->content directly.}}
     *
     * @param  TemplateSource $source source object
     * @return string                 template source
     * @throws SmartyException        if source cannot be loaded
     */
    abstract public function getContent(TemplateSource $source);

    /**
     * populate Source Object with meta data from Resource
     *
     * @param TemplateSource   $source    source object
     * @param Template $_template template object
     */
    abstract public function populate(TemplateSource $source, Template $_template = null);

    /**
     * populate Source Object with timestamp and exists from Resource
     *
     * @param TemplateSource $source source object
     */
    public function populateTimestamp(TemplateSource $source) {
        // intentionally left blank
    }

    /**
     * modify resource_name according to resource handlers specifications
     *
     * @param  Smarty $smarty        Smarty instance
     * @param  string $resource_name resource_name to make unique
     * @return string unique resource name
     */
    protected function buildUniqueResourceName(Smarty $smarty, $resource_name) {
        return get_class($this) . '#' . $smarty->joined_template_dir . '#' . $resource_name;
    }

    /**
     * populate Compiled Object with compiled filepath
     *
     * @param Smarty_Template_Compiled $compiled  compiled object
     * @param Template $_template template object
     */
    public function populateCompiledFilepath(Smarty_Template_Compiled $compiled, Template $_template) {
        $_compile_id = isset($_template->compile_id) ? preg_replace('![^\w\|]+!', '_', $_template->compile_id) : null;
        $_filepath = $compiled->source->uid;
        // if use_sub_dirs, break file into directories
        if ($_template->smarty->use_sub_dirs) {
            $_filepath = substr($_filepath, 0, 2) . DIRECTORY_SEPARATOR
             . substr($_filepath, 2, 2) . DIRECTORY_SEPARATOR
             . substr($_filepath, 4, 2) . DIRECTORY_SEPARATOR
             . $_filepath;
        }
        $_compile_dir_sep = $_template->smarty->use_sub_dirs ? DIRECTORY_SEPARATOR : '^';
        if (isset($_compile_id)) {
            $_filepath = $_compile_id . $_compile_dir_sep . $_filepath;
        }
        $_compile_dir = $_template->smarty->getCompileDir();
        // set basename if not specified
        $_basename = $this->getBasename($compiled->source);
        if ($_basename === null) {
            $_basename = basename( preg_replace('![^\w\/]+!', '_', $compiled->source->name) );
        }
        // separate (optional) basename by dot
        if ($_basename) {
            $_basename = '.' . $_basename;
        }

        $compiled->filepath = $_compile_dir . $_filepath . '.' . $compiled->source->type . $_basename . '.php';
    }

    /**
     * Normalize Paths "foo/../bar" to "bar"
     *
     * @param  string  $_path path to normalize
     * @param  boolean $ds    respect windows directory separator
     * @return string  normalized path
     */
    protected function normalizePath($_path, $ds=true) {
        if ($ds) {
            // don't we all just love windows?
            $_path = str_replace('\\', '/', $_path);
        }

        $offset = 0;

        // resolve simples
        $_path = preg_replace('#/\./(\./)*#', '/', $_path);
        // resolve parents
        while (true) {
            $_parent = strpos($_path, '/../', $offset);
            if (!$_parent) {
                break;
            } elseif ($_path[$_parent - 1] === '.') {
                $offset = $_parent + 3;
                continue;
            }

            $_pos = strrpos($_path, '/', $_parent - strlen($_path) - 1);
            if ($_pos === false) {
                // don't we all just love windows?
                $_pos = $_parent;
            }

            $_path = substr_replace($_path, '', $_pos, $_parent + 3 - $_pos);
        }

        if ($ds && DIRECTORY_SEPARATOR != '/') {
            // don't we all just love windows?
            $_path = str_replace('/', '\\', $_path);
        }

        return $_path;
    }

    /**
     * build template filepath by traversing the template_dir array
     *
     * @param  TemplateSource   $source    source object
     * @param  Template $_template template object
     * @return string                   fully qualified filepath
     * @throws SmartyException          if default template handler is registered but not callable
     */
    protected function buildFilepath(TemplateSource $source, Template $_template=null) {
        $file = $source->name;
        $_directories = $source->smarty->getTemplateDir();

        // go relative to a given template?
        $_file_is_dotted = $file[0] == '.' && ($file[1] == '.' || $file[1] == '/' || $file[1] == "\\");
        if ($_template && $_template->parent instanceof Template && $_file_is_dotted) {
            if ($_template->parent->source->type != 'file' && $_template->parent->source->type != 'extends' && !$_template->parent->allow_relative_path) {
                throw new SmartyException("Template '{$file}' cannot be relative to template of resource type '{$_template->parent->source->type}'");
            }
            $file = dirname($_template->parent->source->filepath) . DIRECTORY_SEPARATOR . $file;
            $_file_exact_match = true;
            if (!preg_match('/^([\/\\\\]|[a-zA-Z]:[\/\\\\])/', $file)) {
                // the path gained from the parent template is relative to the current working directory
                // as expansions (like include_path) have already been done
                $file = getcwd() . DIRECTORY_SEPARATOR . $file;
            }
        }

        // resolve relative path
        if (!preg_match('/^([\/\\\\]|[a-zA-Z]:[\/\\\\])/', $file)) {
            // don't we all just love windows?
            $_path = str_replace('\\', '/', $file);
            $_path = DIRECTORY_SEPARATOR . trim($file, '/');
            $_was_relative = true;
        } else {
            // don't we all just love windows?
            $_path = str_replace('\\', '/', $file);
        }
        $_path = $this->normalizePath($_path, false);
        if (DIRECTORY_SEPARATOR != '/') {
            // don't we all just love windows?
            $_path = str_replace('/', '\\', $_path);
        }
        // revert to relative
        if (isset($_was_relative)) {
            $_path = substr($_path, 1);
        }

        // this is only required for directories
        $file = rtrim($_path, '/\\');

        // files relative to a template only get one shot
        if (isset($_file_exact_match)) {
            return $this->fileExists($source, $file) ? $file : false;
        }

        // template_dir index?
        if (preg_match('#^\[(?P<key>[^\]]+)\](?P<file>.+)$#', $file, $match)) {
            $_directory = null;
            // try string indexes
            if (isset($_directories[$match['key']])) {
                $_directory = $_directories[$match['key']];
            } elseif (is_numeric($match['key'])) {
                // try numeric index
                $match['key'] = (int) $match['key'];
                if (isset($_directories[$match['key']])) {
                    $_directory = $_directories[$match['key']];
                } else {
                    // try at location index
                    $keys = array_keys($_directories);
                    $_directory = $_directories[$keys[$match['key']]];
                }
            }

            if ($_directory) {
                $_file = substr($file, strpos($file, ']') + 1);
                $_filepath = $_directory . $_file;
                if ($this->fileExists($source, $_filepath)) {
                    return $_filepath;
                }
            }
        }

        // relative file name?
        if (!preg_match('/^([\/\\\\]|[a-zA-Z]:[\/\\\\])/', $file)) {
            foreach ($_directories as $_directory) {
                $_filepath = $_directory . $file;
                if ($this->fileExists($source, $_filepath)) {
                    return $this->normalizePath($_filepath);
                }
            }
        }

        // try absolute filepath
        if ($this->fileExists($source, $file)) {
            return $file;
        }

        // give up
        return false;
    }

    /**
     * @param string $path
     * @return int|bool
     */
    protected function getFileTime($path)
    {
        try {
            return filemtime($path);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * @param  TemplateSource $source source object
     * @param  string                 $file   file name
     * @return bool                   true if file exists
     */
    protected function fileExists(TemplateSource $source, $file)
    {
        $source->timestamp = is_file($file) ? $this->getFileTime($file) : false;
        return $source->exists = (bool) $source->timestamp;
    }

    /**
     * Determine basename for compiled filename
     *
     * @param  TemplateSource $source source object
     * @return string                 resource's basename
     */
    protected function getBasename(TemplateSource $source)
    {
        return null;
    }

    /**
     * Load Resource Handler
     *
     * @param  Brainy          $smarty smarty object
     * @param  string          $type   name of the resource
     * @return Resource Resource Handler
     */
    public static function load(Brainy $smarty, $type) {
        // try smarty's cache
        if (isset($smarty->_resource_handlers[$type])) {
            return $smarty->_resource_handlers[$type];
        }

        // try registered resource
        if (isset($smarty->registered_resources[$type])) {
            if ($smarty->registered_resources[$type] instanceof Resource) {
                $smarty->_resource_handlers[$type] = $smarty->registered_resources[$type];
                // note registered to smarty is not kept unique!
                return $smarty->_resource_handlers[$type];
            }

            if (!isset(self::$resources['registered'])) {
                self::$resources['registered'] = new ResourceRegistered();
            }
            if (!isset($smarty->_resource_handlers[$type])) {
                $smarty->_resource_handlers[$type] = self::$resources['registered'];
            }

            return $smarty->_resource_handlers[$type];
        }

        // try sysplugins dir
        if (isset(self::$sysplugins[$type])) {
            if (!isset(self::$resources[$type])) {
                $_resource_class = '\Box\Brainy\Resources\Resource' . ucfirst($type);
                self::$resources[$type] = new $_resource_class();
            }

            return $smarty->_resource_handlers[$type] = self::$resources[$type];
        }

        if (isset(self::$resources[$type])) {
            return $smarty->_resource_handlers[$type] = self::$resources[$type];
        }

        self::$resources[$type] = new $type();
        return $smarty->_resource_handlers[$type] = self::$resources[$type];
    }

    /**
     * extract resource_type and resource_name from template_resource
     *
     * @param  string $resource_name    template_resource to parse
     * @param  string $default_resource the default resource_type defined in $smarty
     * @param  string &$name            the parsed resource name
     * @param  string &$type            the parsed resource type
     * @return void
     */
    protected static function parseResourceName($resource_name, $default_resource, &$name, &$type) {
        $parts = explode(':', $resource_name, 2);
        if (!isset($parts[1]) || !isset($parts[0][1])) {
            // no resource given, use default
            // or single character before the colon is not a resource type, but part of the filepath
            $type = $default_resource;
            $name = $resource_name;
        } else {
            $type = $parts[0];
            $name = $parts[1];
        }
    }

    /**
     * modify template_resource according to resource handlers specifications
     *
     * @param  Template $template            Smarty instance
     * @param  string $template_resource template_resource to extracate resource handler and name of
     * @return string unique resource name
     */
    public static function getUniqueTemplateName($template, $template_resource) {
        self::parseResourceName($template_resource, $template->smarty->default_resource_type, $name, $type);
        // TODO: optimize for Smarty's internal resource types
        $resource = self::load($template->smarty, $type);
        // go relative to a given template?
        $_file_is_dotted = $name[0] == '.' && ($name[1] == '.' || $name[1] == '/' || $name[1] == "\\");
        if ($template instanceof Template && $_file_is_dotted && ($template->source->type == 'file' || $template->parent->source->type == 'extends')) {
            $name = dirname($template->source->filepath) . DIRECTORY_SEPARATOR . $name;
        }
        return $resource->buildUniqueResourceName($template->smarty, $name);
    }

    /**
     * initialize Source Object for given resource
     *
     * Either [$_template] or [$smarty, $template_resource] must be specified
     *
     * @param  Template $_template         template object
     * @param  Smarty                   $smarty            smarty object
     * @param  string                   $template_resource resource identifier
     * @return TemplateSource   Source Object
     */
    public static function source(Template $_template=null, Smarty $smarty=null, $template_resource=null) {
        if ($_template) {
            $smarty = $_template->smarty;
            $template_resource = $_template->template_resource;
        }

        // parse resource_name, load resource handler, identify unique resource name
        self::parseResourceName($template_resource, $smarty->default_resource_type, $name, $type);
        $resource = self::load($smarty, $type);
        // go relative to a given template?
        $_file_is_dotted = isset($name[0]) && $name[0] == '.' && ($name[1] == '.' || $name[1] == '/' || $name[1] == "\\");
        if ($_file_is_dotted && isset($_template) && $_template->parent instanceof Template && ($_template->parent->source->type == 'file' || $_template->parent->source->type == 'extends')) {
            $name2 = dirname($_template->parent->source->filepath) . DIRECTORY_SEPARATOR . $name;
        } else {
            $name2 = $name;
        }
        $unique_resource_name = $resource->buildUniqueResourceName($smarty, $name2);

        // check runtime cache
        $_cache_key = 'template|' . $unique_resource_name;
        if ($smarty->compile_id) {
            $_cache_key .= '|'.$smarty->compile_id;
        }
        if (isset(self::$sources[$_cache_key])) {
            return self::$sources[$_cache_key];
        }

        // create source
        $source = new TemplateSource($resource, $smarty, $template_resource, $type, $name, $unique_resource_name);
        $resource->populate($source, $_template);

        // runtime cache
        self::$sources[$_cache_key] = $source;

        return $source;
    }

}
