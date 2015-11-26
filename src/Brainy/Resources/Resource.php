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
use \Box\Brainy\Exceptions\SmartyException;
use \Box\Brainy\Templates\CompiledTemplate;
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
     * cache for CompiledTemplate instances
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
        'file' => '\Box\Brainy\Resources\ResourceFile',
        'string' => '\Box\Brainy\Resources\ResourceString',
        'eval' => '\Box\Brainy\Resources\ResourceEval',
    );

    /**
     * Load template's source into current template object
     *
     * {@internal The loaded source is assigned to $tpl->source->content directly.}}
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
     * @param Template $tpl template object
     */
    abstract public function populate(TemplateSource $source, Template $tpl = null);

    /**
     * populate Source Object with timestamp and exists from Resource
     *
     * @param TemplateSource $source source object
     */
    public function populateTimestamp(TemplateSource $source)
    {} // intentionally left blank

    /**
     * modify resource_name according to resource handlers specifications
     *
     * @param  Brainy $brainy        Smarty instance
     * @param  string $resourceName
     * @return string unique resource name
     */
    protected function buildUniqueResourceName(Brainy $brainy, $resourceName)
    {
        return self::buildUniqueNameForResource($brainy, $this, $resourceName);
    }

    /**
     * Builds a unique resource name
     * @param  Brainy $brainy
     * @param  Resource $resource
     * @param  string $resourceName
     * @return string
     */
    public static function buildUniqueNameForResource(Brainy $brainy, $resource, $resourceName)
    {
        return get_class($resource) . '#' . $brainy->joined_template_dir . '#' . $resourceName;
    }

    /**
     * populate Compiled Object with compiled filepath
     *
     * @param CompiledTemplate $compiled  compiled object
     * @param Template $tpl template object
     */
    public function populateCompiledFilepath(CompiledTemplate $compiled, Template $tpl)
    {
        $compileID = isset($tpl->compile_id) ? preg_replace('![^\w\|]+!', '_', $tpl->compile_id) : null;
        $path = $compiled->source->uid;
        // if use_sub_dirs, break file into directories
        if ($tpl->smarty->use_sub_dirs) {
            $path = substr($path, 0, 2) . DIRECTORY_SEPARATOR .
                substr($path, 2, 2) . DIRECTORY_SEPARATOR .
                substr($path, 4, 2) . DIRECTORY_SEPARATOR .
                $path;
            $ds = DIRECTORY_SEPARATOR;
        } else {
            $ds = '^';
        }
        if ($compileID) {
            $path = $compileID . $ds . $path;
        }
        // set basename if not specified
        $baseName = $this->getBasename($compiled->source);
        if ($baseName === null) {
            $baseName = basename(preg_replace('![^\w\/]+!', '_', $compiled->source->name));
        }
        // separate (optional) basename by dot
        if ($baseName) {
            $baseName = '.' . $baseName;
        }

        $compileDir = $tpl->smarty->getCompileDir();
        $compiled->filepath = $compileDir . $path . '.' . $compiled->source->type . $baseName . '.php';
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
     * @param  Brainy          $brainy smarty object
     * @param  string          $type   name of the resource
     * @return Resource Resource Handler
     */
    public static function load(Brainy $brainy, $type)
    {
        // try registered resource
        if (isset($brainy->registered_resources[$type])) {
            if ($brainy->registered_resources[$type] instanceof Resource) {
                // note registered to smarty is not kept unique!
                return $brainy->registered_resources[$type];
            }

            if (!isset(self::$resources['registered'])) {
                self::$resources['registered'] = new ResourceRegistered();
            }
            return self::$resources['registered'];
        }

        // try sysplugins dir
        if (isset(self::$sysplugins[$type]) && !isset(self::$resources[$type])) {
            self::$resources[$type] = new self::$sysplugins[$type]();
        }

        if (isset(self::$resources[$type])) {
            return self::$resources[$type];
        }

        self::$resources[$type] = new $type();
        return self::$resources[$type];
    }

    /**
     * initialize Source Object for given resource
     *
     * Either [$tpl] or [$brainy, $template_resource] must be specified
     *
     * @param  Template $tpl         template object
     * @param  Brainy                   $brainy            smarty object
     * @param  string                   $template_resource resource identifier
     * @return TemplateSource   Source Object
     */
    public static function source(Template $tpl = null, Brainy $brainy = null, $template_resource = null)
    {
        if ($tpl) {
            $brainy = $tpl->smarty;
            $template_resource = $tpl->template_resource;
        }

        $parts = explode(':', $template_resource, 2);
        if (!isset($parts[1]) || !isset($parts[0][1])) {
            // no resource given, use default
            // or single character before the colon is not a resource type, but part of the filepath
            $type = 'file';
            $name = $template_resource;
        } else {
            $type = $parts[0];
            $name = $parts[1];
        }

        $resource = self::load($brainy, $type);
        // go relative to a given template?
        $fileIsRelative = (
            isset($name[0]) &&
            $name[0] === '.' &&
            isset($name[1]) &&
            ($name[1] === '.' || $name[1] === DIRECTORY_SEPARATOR)
        );
        if ($fileIsRelative &&
                isset($tpl) &&
                $tpl->parent instanceof Template &&
                $tpl->parent->source->type == 'file') {
            $name2 = dirname($tpl->parent->source->filepath) . DIRECTORY_SEPARATOR . $name;
        } else {
            $name2 = $name;
        }
        $unique_resource_name = $resource->buildUniqueResourceName($brainy, $name2);

        // check runtime cache
        $cacheKey = 'template|' . $unique_resource_name;
        $compileID = isset($tpl) ? $tpl->compile_id ?: $brainy->compile_id : $brainy->compile_id;
        if ($compileID) {
            $cacheKey .= '|' . $compileID;
        }
        if (isset(self::$sources[$cacheKey])) {
            return self::$sources[$cacheKey];
        }

        // create source
        $source = new TemplateSource($resource, $brainy, $type, $name, $unique_resource_name);
        $resource->populate($source, $tpl);
        self::$sources[$cacheKey] = $source;

        return $source;
    }

}
