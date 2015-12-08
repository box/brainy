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
     * Source must be recompiled on every occasion
     * @var boolean
     */
    public $recompiled = null;

    /**
     * Resource Handler
     * @var Resource
     */
    public $handler = null;

    /**
     * Smarty instance
     * @var \Box\Brainy\Brainy
     */
    public $smarty = null;

    /**
     * Cache of the CompiledTemplate
     * @var CompiledTemplate|null
     */
    private $compiledCache = null;

    /**
     * Cache of the template's content
     * @var string|null
     */
    private $contentCache = null;

    /**
     * @param Resource           $handler         Resource Handler this source object communicates with
     * @param \Box\Brainy\Brainy $smarty          Smarty instance this source object belongs to
     * @param string             $type            type of resource
     * @param string             $name            resource name
     * @param string             $unique_resource unqiue resource name
     */
    public function __construct(Resource $handler, \Box\Brainy\Brainy $smarty, $type, $name, $unique_resource)
    {
        $this->handler = $handler; // Note: prone to circular references
        $this->recompiled = $this->handler instanceof \Box\Brainy\Resources\ResourceRecompiled;

        $this->smarty = $smarty;
        $this->type = $type;
        $this->name = $name;
        $this->unique_resource = $unique_resource;
    }

    /**
     * get a Compiled Object of this source
     *
     * @param  Template $_template template objet
     * @return CompiledTemplate compiled object
     */
    public function getCompiled(Template $_template)
    {
        if ($this->compiledCache) {
            return $this->compiledCache;
        }
        $compiled = new CompiledTemplate($this);
        $this->handler->populateCompiledFilepath($compiled, $_template);
        $compiled->timestamp = false;

        if ($compiled->filepath && file_exists($compiled->filepath)) {
            try {
                $compiled->timestamp = filemtime($compiled->filepath);
            } catch (\Exception $e) {
            }
        }
        $compiled->exists = (bool) $compiled->timestamp;

        $this->compiledCache = $compiled;

        return $compiled;
    }

    /**
     * Returns the raw content of the template
     * @return string
     */
    public function getContent()
    {
        if ($this->contentCache === null) {
            $this->contentCache = $this->handler->getContent($this);
        }
        return $this->contentCache;
    }

    /**
     * @param  string $property_name valid: timestamp, exists, content
     * @return mixed
     * @throws SmartyException if $property_name is not valid
     */
    public function __get($property_name)
    {
        switch ($property_name) {
        case 'timestamp':
        case 'exists':
            $this->handler->populateTimestamp($this);
            return $this->exists;

        default:
            throw new SmartyException("source property '$property_name' does not exist.");
        }
    }

}
