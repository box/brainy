<?php
/**
 * Extends All Resource
 *
 * Resource Implementation modifying the extends-Resource to walk
 * through the template_dirs and inherit all templates of the same name
 *
 * @package Resource-examples
 * @author Rodney Rehm
 */

namespace Box\Brainy\Tests\ResourcePlugins;

use \Box\Brainy\Exceptions\SmartyException;
use \Box\Brainy\Templates\Template;
use \Box\Brainy\Templates\TemplateSource;


class ResourceExtendsAll extends \Box\Brainy\Resources\ResourceExtends
{
    /**
     * populate Source Object with meta data from Resource
     *
     * @param  TemplateSource   $source    source object
     * @param  Template $_template template object
     * @return void
     */
    public function populate(TemplateSource $source, Template $_template = null) {
        $uid = '';
        $sources = array();
        $exists = true;
        foreach ($_template->smarty->getTemplateDir() as $key => $directory) {
            try {
                $s = \Box\Brainy\Resources\Resource::source(null, $source->smarty, '[' . $key . ']' . $source->name );
                if (!$s->exists) {
                    continue;
                }
                $sources[$s->uid] = $s;
                $uid .= $s->filepath;
            } catch (SmartyException $e) {}
        }

        $sources = array_reverse($sources, true);
        reset($sources);
        $s = current($sources);

        $source->components = $sources;
        $source->filepath = $s->filepath;
        $source->uid = sha1($uid);
        $source->exists = $exists;
        if ($_template && $_template->smarty->compile_check) {
            $source->timestamp = $s->timestamp;
        }
        // need the template at getContent()
        $source->template = $_template;
    }
}
