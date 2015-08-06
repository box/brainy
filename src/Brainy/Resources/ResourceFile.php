<?php
/**
 * Smarty Internal Plugin Resource File
 *
 * @package Brainy
 * @subpackage TemplateResources
 * @author Uwe Tews
 * @author Rodney Rehm
 */

namespace Box\Brainy\Resources;

use \Box\Brainy\Exceptions\SmartyException;
use Box\Brainy\Templates\Template;
use Box\Brainy\Templates\TemplateSource;


class ResourceFile extends Resource
{
    /**
     * populate Source Object with meta data from Resource
     *
     * @param TemplateSource   $source    source object
     * @param Template $_template template object
     */
    public function populate(TemplateSource $source, Template $_template=null) {
        $source->filepath = $this->buildFilepath($source, $_template);

        if ($source->filepath !== false) {
            if (is_object($source->smarty->security_policy)) {
                $source->smarty->security_policy->isTrustedResourceDir($source->filepath);
            }

            $source->uid = sha1($source->filepath);
            if ($source->smarty->compile_check && !isset($source->timestamp)) {
                $source->timestamp = filemtime($source->filepath);
                $source->exists = !!$source->timestamp;
            }
        }
    }

    /**
     * populate Source Object with timestamp and exists from Resource
     *
     * @param TemplateSource $source source object
     */
    public function populateTimestamp(TemplateSource $source) {
        $source->timestamp = @filemtime($source->filepath);
        $source->exists = (bool) $source->timestamp;
    }

    /**
     * Load template's source from file into current template object
     *
     * @param  TemplateSource $source source object
     * @return string                 template source
     * @throws SmartyException        if source cannot be loaded
     */
    public function getContent(TemplateSource $source) {
        if ($source->timestamp) {
            return file_get_contents($source->filepath);
        }
        throw new SmartyException("Unable to read template {$source->type} '{$source->name}'");
    }

    /**
     * Determine basename for compiled filename
     *
     * @param  TemplateSource $source source object
     * @return string                 resource's basename
     */
    public function getBasename(TemplateSource $source) {
        $_file = $source->name;
        if (($_pos = strpos($_file, ']')) !== false) {
            $_file = substr($_file, $_pos + 1);
        }

        return basename($_file);
    }

}
