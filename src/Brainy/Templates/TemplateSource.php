<?php

namespace Box\Brainy\Templates;

use \Box\Brainy\Exceptions\SmartyException;
use \Box\Brainy\Resources\Resource;


/**
 * @author Rodney Rehm
 */
class TemplateSource
{
    /**
     * Unique Template ID
     * @var string
     */
    public $uid = null;

    /**
     * Template Resource (Template::$template_resource)
     * @var string
     */
    public $resource = null;

    /**
     * Resource Type
     * @var string
     */
    public $type = null;

    /**
     * Resource Name
     * @var string
     */
    public $name = null;

    /**
     * Unique Resource Name
     * @var string
     */
    public $unique_resource = null;

    /**
     * Source Filepath
     * @var string
     */
    public $filepath = null;

    /**
     * Source is bypassing compiler
     * @var boolean
     */
    public $uncompiled = null;

    /**
     * Source must be recompiled on every occasion
     * @var boolean
     */
    public $recompiled = null;

    /**
     * The Components an extended template is made of
     * @var array
     */
    public $components = null;

    /**
     * Resource Handler
     * @var \Box\Brainy\Resources\Resource
     */
    public $handler = null;

    /**
     * Smarty instance
     * @var \Box\Brainy\Brainy
     */
    public $smarty = null;

    /**
     * @param Resource $handler         Resource Handler this source object communicates with
     * @param \Box\Brainy\Brainy $smarty          Smarty instance this source object belongs to
     * @param string          $resource        full template_resource
     * @param string          $type            type of resource
     * @param string          $name            resource name
     * @param string          $unique_resource unqiue resource name
     */
    public function __construct(Resource $handler, \Box\Brainy\Brainy $smarty, $resource, $type, $name, $unique_resource) {
        $this->handler = $handler; // Note: prone to circular references

        $this->uncompiled = $this->handler instanceof \Box\Brainy\Resources\ResourceUncompiled;
        $this->recompiled = $this->handler instanceof \Box\Brainy\Resources\ResourceRecompiled;

        $this->smarty = $smarty;
        $this->resource = $resource;
        $this->type = $type;
        $this->name = $name;
        $this->unique_resource = $unique_resource;
    }

    /**
     * get a Compiled Object of this source
     *
     * @param  Template $_template template objet
     * @return Smarty_Template_Compiled compiled object
     */
    public function getCompiled(Template $_template) {
        // check runtime cache
        $_cache_key = $this->unique_resource . '#' . $_template->compile_id;
        if (isset(Resource::$compileds[$_cache_key])) {
            return Resource::$compileds[$_cache_key];
        }

        $compiled = new CompiledTemplate($this);
        $this->handler->populateCompiledFilepath($compiled, $_template);
        $compiled->timestamp = false;

        if ($compiled->filepath && file_exists($compiled->filepath)) {
            try {
                $compiled->timestamp = filemtime($compiled->filepath);
            } catch (Exception $e) {}
        }
        $compiled->exists = (bool) $compiled->timestamp;

        // runtime cache
        \Box\Brainy\Resources\Resource::$compileds[$_cache_key] = $compiled;

        return $compiled;
    }

    /**
     * render the uncompiled source
     *
     * @param Template $_template template object
     */
    public function renderUncompiled(Template $_template) {
        return $this->handler->renderUncompiled($this, $_template);
    }

    /**
     * <<magic>> Generic Setter.
     *
     * @param  string          $property_name valid: timestamp, exists, content, template
     * @param  mixed           $value         new value (is not checked)
     * @throws SmartyException if $property_name is not valid
     */
    public function __set($property_name, $value) {
        switch ($property_name) {
            // regular attributes
            case 'timestamp':
            case 'exists':
            case 'content':
            // required for extends: only
            case 'template':
                $this->$property_name = $value;
                break;

            default:
                throw new SmartyException("invalid source property '$property_name'.");
        }
    }

    /**
     * <<magic>> Generic getter.
     *
     * @param  string          $property_name valid: timestamp, exists, content
     * @return mixed
     * @throws SmartyException if $property_name is not valid
     */
    public function __get($property_name) {
        switch ($property_name) {
            case 'timestamp':
            case 'exists':
                $this->handler->populateTimestamp($this);

                return $this->$property_name;

            case 'content':
                return $this->content = $this->handler->getContent($this);

            default:
                throw new SmartyException("source property '$property_name' does not exist.");
        }
    }

}
