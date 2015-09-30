<?php
/**
 * Smarty Internal Plugin Resource Extends
 *
 * @package Brainy
 * @subpackage TemplateResources
 * @author Uwe Tews
 * @author Rodney Rehm
 */

namespace Box\Brainy\Resources;

use Box\Brainy\Templates\Template;
use Box\Brainy\Templates\TemplateSource;


class ResourceExtends extends Resource
{
    /**
     * mbstring.overload flag
     *
     * @var int
     */
    public $mbstring_overload = 0;

    /**
     * populate Source Object with meta data from Resource
     *
     * @param TemplateSource $source    source object
     * @param Template $_template template object
     */
    public function populate(TemplateSource $source, Template $_template = null) {
        $uid = '';
        $sources = array();
        $components = explode('|', $source->name);
        $exists = true;
        foreach ($components as $component) {
            $s = Resource::source(null, $source->smarty, $component);
            $sources[$s->uid] = $s;
            $uid .= $s->filepath;
            if ($_template && $_template->smarty->compile_check) {
                $exists = $exists && $s->exists;
            }
        }
        $source->components = $sources;
        $source->filepath = realpath($s->filepath);
        $source->uid = sha1($uid);
        if ($_template && $_template->smarty->compile_check) {
            $source->timestamp = $s->timestamp;
            $source->exists = $exists;
        }
        // need the template at getContent()
        $source->template = $_template;
    }

    /**
     * populate Source Object with timestamp and exists from Resource
     *
     * @param TemplateSource $source source object
     */
    public function populateTimestamp(TemplateSource $source) {
        $source->exists = true;
        foreach ($source->components as $s) {
            $source->exists = $source->exists && $s->exists;
        }
        $source->timestamp = $s->timestamp;
    }

    /**
     * Load template's source from files into current template object
     *
     * @param TemplateSource $source source object
     * @return string template source
     * @throws SmartyException if source cannot be loaded
     */
    public function getContent(TemplateSource $source) {
        if (!$source->exists) {
            throw new SmartyException("Unable to read template {$source->type} '{$source->name}'");
        }

        $_components = array_reverse($source->components);

        $_content = '';
        foreach ($_components as $_component) {
            // read content
            $_content .= $_component->content;
        }
        return $_content;
    }

    /**
     * Determine basename for compiled filename
     *
     * @param TemplateSource $source source object
     * @return string resource's basename
     */
    public function getBasename(TemplateSource $source) {
        return str_replace(':', '.', basename($source->filepath));
    }

}
