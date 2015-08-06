<?php

namespace Box\Brainy\Templates;


/**
 * @author Rodney Rehm
 */
class CompiledTemplate
{
    /**
     * Compiled Filepath
     * @var string
     */
    public $filepath = null;

    /**
     * Compiled Timestamp
     * @var integer|null
     */
    public $timestamp = null;

    /**
     * Compiled Existence
     * @var boolean
     */
    public $exists = false;

    /**
     * Compiled Content Loaded
     * @var boolean
     */
    public $loaded = false;

    /**
     * Template was compiled
     * @var boolean
     */
    public $isCompiled = false;

    /**
     * Source Object
     * @var Smarty_Template_Source
     */
    public $source = null;

    /**
     * Metadata properties
     *
     * populated by Template::decodeProperties()
     * @var array
     */
    public $_properties = null;

    /**
     * create Compiled Object container
     *
     * @param Smarty_Template_Source $source source object this compiled object belongs to
     */
    public function __construct(Source $source) {
        $this->source = $source;
    }

}
