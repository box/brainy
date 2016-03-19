<?php
/**
* Smarty PHPunit tests assign method
*
* @package PHPunit
* @author Victor Shen
*/

namespace Box\Brainy\Tests;

class FetchTest extends Smarty_TestCase
{
    public function testDisplayRecompilesTemplateWhenSourceTimestampisOutdated() {
        $tpl = $this->smarty->createTemplate('file:helloworld.tpl');
        $tpl->fetch(); // compiles the template to disk
        $filepath = $tpl->compiled->filepath;

        $tpl->source->timestamp += 1; // Set source timestamp to be outdated
        $tpl->smarty->force_compile = 0;
        $tpl->smarty->compile_check = 1;
        $tpl->compiled->isCompiled = 0;
        $tpl->compiled->exists = 1;
        $tpl->compiled->loaded = 0;
        $tpl->compiled->properties = null;
        $tpl->properties['unifunc'] = 'doesnotexist';

        $output = $tpl->fetch('helloworld.tpl');
        $this->assertEquals($output, 'hello world'); // should still compile
        $this->assertNotEquals($tpl->compiled->filepath, $filepath); // should be a different compiled template
    }
}
