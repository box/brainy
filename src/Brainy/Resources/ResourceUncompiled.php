<?php
/**
 * Smarty Resource Plugin
 *
 * @package Brainy
 * @subpackage TemplateResources
 * @author Rodney Rehm
 */

namespace Box\Brainy\Resources;


abstract class ResourceUncompiled extends Resource
{
    /**
     * Render and output the template (without using the compiler)
     *
     * @param  Smarty_Template_Source   $source    source object
     * @param  Template $_template template object
     * @throws SmartyException          on failure
     */
    abstract public function renderUncompiled(Smarty_Template_Source $source, Template $_template);

    /**
     * populate compiled object with compiled filepath
     *
     * @param Smarty_Template_Compiled $compiled  compiled object
     * @param Template $_template template object (is ignored)
     */
    public function populateCompiledFilepath(Smarty_Template_Compiled $compiled, Template $_template) {
        $compiled->filepath = false;
        $compiled->timestamp = false;
        $compiled->exists = false;
    }

}
