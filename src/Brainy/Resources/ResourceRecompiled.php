<?php
/**
 * Smarty Resource Plugin
 *
 * @package    Brainy
 * @subpackage TemplateResources
 * @author     Rodney Rehm
 */

namespace Box\Brainy\Resources;

use \Box\Brainy\Templates\CompiledTemplate;
use \Box\Brainy\Templates\Template;

abstract class ResourceRecompiled extends Resource
{
    /**
     * populate Compiled Object with compiled filepath
     *
     * @param  CompiledTemplate $compiled  compiled object
     * @param  Template         $_template template object
     * @return void
     */
    public function populateCompiledFilepath(CompiledTemplate $compiled, Template $_template)
    {
        $compiled->filepath = false;
        $compiled->timestamp = false;
        $compiled->exists = false;
    }
}
