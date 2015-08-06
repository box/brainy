<?php

namespace Box\Brainy\Tests\ResourcePlugins;

use \Box\Brainy\Templates\Template;
use \Box\Brainy\Templates\TemplateSource;


class ResourceDB2 extends \Box\Brainy\Resources\ResourceRecompiled
{
    public function populate(TemplateSource $source, Template $_template = null) {
        $source->filepath = 'db2:';
        $source->uid = sha1($source->resource);
        $source->timestamp = 0;
        $source->exists = true;
    }

    public function getContent(TemplateSource $source) {
        return '{$x="hello world"}{$x}';
    }
}
