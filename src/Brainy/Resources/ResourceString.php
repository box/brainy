<?php
/**
 * Smarty Internal Plugin Resource String
 *
 * @package Brainy
 * @subpackage TemplateResources
 * @author Uwe Tews
 * @author Rodney Rehm
 */

namespace Box\Brainy\Resources;

use Box\Brainy\Templates\Template;
use Box\Brainy\Templates\TemplateSource;


class ResourceString extends Resource
{
    /**
     * populate Source Object with meta data from Resource
     *
     * @param  TemplateSource   $source    source object
     * @param  Template $_template template object
     * @return void
     */
    public function populate(TemplateSource $source, Template $_template=null) {
        $source->uid = $source->filepath = sha1($source->name);
        $source->timestamp = 0;
        $source->exists = true;
    }

    /**
     * Load template's source from $resource_name into current template object
     *
     * @uses decode() to decode base64 and urlencoded template_resources
     * @param  TemplateSource $source source object
     * @return string                 template source
     */
    public function getContent(TemplateSource $source) {
        return $source->name;
    }

    /**
     * modify resource_name according to resource handlers specifications
     *
     * @param  \Box\Brainy\Brainy $smarty        Smarty instance
     * @param  string $resource_name resource_name to make unique
     * @return string unique resource name
     */
    protected function buildUniqueResourceName(\Box\Brainy\Brainy $smarty, $resource_name) {
        return get_class($this) . '#' . $resource_name;
    }

    /**
     * Determine basename for compiled filename
     *
     * Always returns an empty string.
     *
     * @param  TemplateSource $source source object
     * @return string                 resource's basename
     */
    protected function getBasename(TemplateSource $source) {
        return '';
    }

}
