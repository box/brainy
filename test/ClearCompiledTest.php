<?php
/**
* Smarty PHPunit tests for deleting compiled templates
*
* @package PHPunit
* @author Uwe Tews
* @author Rodney Rehm
*/

namespace Box\Brainy\Tests;


class ClearCompiledTest extends Smarty_TestCase
{
    public function setup(): void {
        parent::setUp();
        $this->smarty->addTemplateDir('test/templates_2/');
    }

    /**
     * list of compiled files
     * @var array
     */
    protected $_files = array();
    /**
     * generate compiled files
     * @uses $_files to store references
     * @return array list of files array( id => path )
     */
    protected function makeFiles() {
        $this->_files = array();
        $directory_length = strlen($this->smarty->getCompileDir());
        $templates = array(
            'helloworld.tpl' => array(null, 'compile1', 'compile2'),
            'helloworld2.tpl' => array(null, 'compile1', 'compile2'),
            'ambiguous/case1/foobar.tpl' => array(null, 'compile1', 'compile2'),
            '[1]ambiguous/case1/foobar.tpl' => array(null, 'compile1', 'compile2'),
        );

        foreach ($templates as $template => $compile_ids) {
            foreach ($compile_ids as $compile_id) {
                $tpl = $this->smarty->createTemplate($template, $compile_id);
                $tpl->fetch(); // Force a compile and render
                $this->_files[$template . '#' . $compile_id] = substr($tpl->compiled->filepath, $directory_length - 1);
            }
        }

        \Box\Brainy\Resources\Resource::reset();

        return $this->_files;
    }
    /**
     * Transform $id to $path
     *
     * @param  array $keys IDs like "template#compile_id"
     * @return array list of (sorted) compiled file paths
     */
    protected function expectFiles($keys) {
        $files = array();
        foreach ($keys as $key) {
            if (isset($this->_files[$key])) {
                $files[] = $this->_files[$key];
            }
        }
        sort($files);

        return $files;
    }
    /**
     * update mtime of compiled files
     *
     * @param  array  $keys   IDs like "template#compile_id"
     * @param  string $offset time offset added to time()
     * @return void
     */
    protected function touchFiles($keys, $offset=0) {
        $base = rtrim($this->smarty->getCompileDir(), "\\/");
        $time = time();
        foreach ($keys as $key) {
            if (isset($this->_files[$key])) {
                touch($base . $this->_files[$key], $time + $offset);
            }
        }
    }
    /**
     * find all compiled files
     *
     * @return array list of (sorted) compiled file paths
     */
    protected function getFiles() {
        $directory = realpath($this->smarty->getCompileDir());
        if (!$directory) {
            return array();
        }

        $directory_length = strlen($directory);
        $files = array();

        $di = new \RecursiveDirectoryIterator($directory);
        $it = new \RecursiveIteratorIterator($di);
        foreach ($it as $file) {
            $_file = $file->__toString();
            // skip anything with a /. in it.
            if (preg_match("#[\\\\/]\.#", $_file) || !$file->isFile()) {
                continue;
            }

            $files[] = substr($file->__toString(), $directory_length);
        }
        sort($files);

        return $files;
    }

    // \Box\Brainy\Brainy::clearCompiledTemplate(null, null, null)
    public function testClearAll() {
        $this->runClearAll(false);
    }
    public function testSubsClearAll() {
        $this->runClearAll(true);
    }
    public function runClearAll($use_sub_dirs) {
        $this->smarty->use_sub_dirs = $use_sub_dirs;
        $this->clearFiles();
        $this->makeFiles();

        $expected = array();
        $this->assertEquals(12, $this->smarty->clearCompiledTemplate());

        $this->assertEquals($this->expectFiles($expected), $this->getFiles());
        $this->clearFiles();
    }

    // \Box\Brainy\Brainy::clearCompiledTemplate($template, null, null)
    public function testClearTemplate() {
        $this->runClearTemplate(false);
    }
    public function testSubsClearTemplate() {
        $this->runClearTemplate(true);
    }
    public function runClearTemplate($use_sub_dirs) {
        $this->smarty->use_sub_dirs = $use_sub_dirs;
        $this->clearFiles();
        $this->makeFiles();

        $expected = array(
            'helloworld2.tpl#', 'helloworld2.tpl#compile1', 'helloworld2.tpl#compile2',
            'ambiguous/case1/foobar.tpl#', 'ambiguous/case1/foobar.tpl#compile1', 'ambiguous/case1/foobar.tpl#compile2',
            '[1]ambiguous/case1/foobar.tpl#', '[1]ambiguous/case1/foobar.tpl#compile1', '[1]ambiguous/case1/foobar.tpl#compile2',
        );
        $numRemoved = $this->smarty->clearCompiledTemplate('helloworld.tpl');
        $actual = $this->getFiles();

        $this->assertEquals(3, $numRemoved);

        $this->assertEquals($this->expectFiles($expected), $actual);
        $this->clearFiles();
    }
    public function testClearOtherTemplate() {
        $this->runClearOtherTemplate(false);
    }
    public function testSubsClearOtherTemplate() {
        $this->runClearOtherTemplate(true);
    }
    public function runClearOtherTemplate($use_sub_dirs) {
        $this->smarty->use_sub_dirs = $use_sub_dirs;
        $this->clearFiles();
        $this->makeFiles();

        $expected = array_keys($this->_files);
        $this->assertEquals(0, $this->smarty->clearCompiledTemplate('foobar.tpl'));

        $this->assertEquals($this->expectFiles($expected), $this->getFiles());
        $this->clearFiles();
    }

    // \Box\Brainy\Brainy::clearCompiledTemplate(null, $cache_id, null)
    public function testClearCompileid() {
        $this->runClearCompileid(false);
    }
    public function testSubsClearCompileid() {
        $this->runClearCompileid(true);
    }
    public function testClearOtherCompileid() {
        $this->runClearOtherCompileid(false);
    }
    public function testSubsClearOtherCompileid() {
        $this->runClearOtherCompileid(true);
    }
    public function runClearCompileid($use_sub_dirs) {
        $this->smarty->use_sub_dirs = $use_sub_dirs;
        $this->clearFiles();
        $this->makeFiles();

        $expected = array(
            'helloworld.tpl#', 'helloworld.tpl#compile2',
            'helloworld2.tpl#', 'helloworld2.tpl#compile2',
            'ambiguous/case1/foobar.tpl#', 'ambiguous/case1/foobar.tpl#compile2',
            '[1]ambiguous/case1/foobar.tpl#', '[1]ambiguous/case1/foobar.tpl#compile2',
        );
        $this->assertEquals(4, $this->smarty->clearCompiledTemplate(null, 'compile1'));

        $this->assertEquals($this->expectFiles($expected), $this->getFiles());
        $this->clearFiles();

    }
    public function runClearOtherCompileid($use_sub_dirs) {
        $this->smarty->use_sub_dirs = $use_sub_dirs;
        $this->clearFiles();
        $this->makeFiles();

        $expected = array_keys($this->_files);
        $this->assertEquals(0, $this->smarty->clearCompiledTemplate(null, 'other'));

        $this->assertEquals($this->expectFiles($expected), $this->getFiles());
        $this->clearFiles();
    }

    // \Box\Brainy\Brainy::clearCompiledTemplate(null, null, $expired)
    public function testClearExpired() {
        $this->runClearExpired(false);
    }
    public function testSubsClearExpired() {
        $this->runClearExpired(true);
    }
    public function runClearExpired($use_sub_dirs) {
        $this->smarty->use_sub_dirs = $use_sub_dirs;
        $this->clearFiles();
        $this->makeFiles();

        $expected = array('helloworld.tpl#', 'helloworld2.tpl#');
        $this->touchFiles(array_diff(array_keys($this->_files), $expected), -1000);
        $this->assertEquals(10, $this->smarty->clearCompiledTemplate(null, null, 500));

        $this->assertEquals($this->expectFiles($expected), $this->getFiles());
        $this->clearFiles();
    }

    // \Box\Brainy\Brainy::clearCompiledTemplate($template, null, $expired)
    public function testClearTemplateExpired() {
        $this->runClearTemplateExpired(false);
    }
    public function testSubsClearTemplateExpired() {
        $this->runClearTemplateExpired(true);
    }
    public function runClearTemplateExpired($use_sub_dirs) {
        $this->smarty->use_sub_dirs = $use_sub_dirs;
        $this->clearFiles();
        $this->makeFiles();

        $expected = array(
            'helloworld.tpl#', 'helloworld.tpl#compile2',
            'helloworld2.tpl#', 'helloworld2.tpl#compile1', 'helloworld2.tpl#compile2',
            'ambiguous/case1/foobar.tpl#', 'ambiguous/case1/foobar.tpl#compile1', 'ambiguous/case1/foobar.tpl#compile2',
            '[1]ambiguous/case1/foobar.tpl#', '[1]ambiguous/case1/foobar.tpl#compile1', '[1]ambiguous/case1/foobar.tpl#compile2',
        );
        $this->touchFiles(array('helloworld.tpl#compile1'), -1000);
        $this->assertEquals(1, $this->smarty->clearCompiledTemplate("helloworld.tpl", null, 500));

        $this->assertEquals($this->expectFiles($expected), $this->getFiles());
        $this->clearFiles();
    }

    // \Box\Brainy\Brainy::clearCompiledTemplate($template, $cache_id, $expired)
    public function testClearTemplateCacheidExpired() {
        $this->runClearTemplateCacheidExpired(false);
    }
    public function testSubsClearTemplateCacheidExpired() {
        $this->runClearTemplateCacheidExpired(true);
    }
    public function runClearTemplateCacheidExpired($use_sub_dirs) {
        $this->smarty->use_sub_dirs = $use_sub_dirs;
        $this->clearFiles();
        $this->makeFiles();

        $expected = array(
            'helloworld.tpl#', 'helloworld.tpl#compile2',
            'helloworld2.tpl#', 'helloworld2.tpl#compile1', 'helloworld2.tpl#compile2',
            'ambiguous/case1/foobar.tpl#', 'ambiguous/case1/foobar.tpl#compile1', 'ambiguous/case1/foobar.tpl#compile2',
            '[1]ambiguous/case1/foobar.tpl#', '[1]ambiguous/case1/foobar.tpl#compile1', '[1]ambiguous/case1/foobar.tpl#compile2',
        );
        $this->touchFiles(array('helloworld.tpl#compile1', 'helloworld.tpl#compile2'), -1000);
        $this->assertEquals(1, $this->smarty->clearCompiledTemplate("helloworld.tpl", "compile1", 500));

        $this->assertEquals($this->expectFiles($expected), $this->getFiles());
        $this->clearFiles();
    }

    // \Box\Brainy\Brainy::clearCompiledTemplate(null, $cache_id, $expired)
    public function testClearCacheidExpired() {
        $this->runClearCacheidExpired(false);
    }
    public function testSubsClearCacheidExpired() {
        $this->runClearCacheidExpired(true);
    }
    public function runClearCacheidExpired($use_sub_dirs) {
        $this->smarty->use_sub_dirs = $use_sub_dirs;
        $this->clearFiles();
        $this->makeFiles();

        $expected = array(
            'helloworld.tpl#', 'helloworld.tpl#compile2',
            'helloworld2.tpl#', 'helloworld2.tpl#compile1', 'helloworld2.tpl#compile2',
            'ambiguous/case1/foobar.tpl#', 'ambiguous/case1/foobar.tpl#compile1', 'ambiguous/case1/foobar.tpl#compile2',
            '[1]ambiguous/case1/foobar.tpl#', '[1]ambiguous/case1/foobar.tpl#compile1', '[1]ambiguous/case1/foobar.tpl#compile2',
        );
        $this->touchFiles(array('helloworld.tpl#compile1'), -1000);
        $this->assertEquals(1, $this->smarty->clearCompiledTemplate(null, "compile1", 500));

        $this->assertEquals($this->expectFiles($expected), $this->getFiles());
        $this->clearFiles();
    }

    // \Box\Brainy\Brainy::clearCompiledTemplate($template, $cache_id, null)
    public function testClearTemplateCacheid() {
        $this->runClearTemplateCacheid(false);
    }
    public function testSubsClearTemplateCacheid() {
        $this->runClearTemplateCacheid(true);
    }
    public function runClearTemplateCacheid($use_sub_dirs) {
        $this->smarty->use_sub_dirs = $use_sub_dirs;
        $this->clearFiles();
        $this->makeFiles();

        $expected = array(
            'helloworld.tpl#', 'helloworld.tpl#compile2',
            'helloworld2.tpl#', 'helloworld2.tpl#compile1', 'helloworld2.tpl#compile2',
            'ambiguous/case1/foobar.tpl#', 'ambiguous/case1/foobar.tpl#compile1', 'ambiguous/case1/foobar.tpl#compile2',
            '[1]ambiguous/case1/foobar.tpl#', '[1]ambiguous/case1/foobar.tpl#compile1', '[1]ambiguous/case1/foobar.tpl#compile2',
        );
        $this->assertEquals(1, $this->smarty->clearCompiledTemplate("helloworld.tpl", "compile1"));

        $this->assertEquals($this->expectFiles($expected), $this->getFiles());
        $this->clearFiles();
    }

    public function testClearAmbiguousTemplate() {
        $this->runClearAmbiguousTemplate(false);
    }
    public function testSubsAmbiguousTemplate() {
        $this->runClearAmbiguousTemplate(true);
    }
    public function runClearAmbiguousTemplate($use_sub_dirs) {
        $this->smarty->use_sub_dirs = $use_sub_dirs;
        $this->clearFiles();
        $this->makeFiles();

        // TODO: uwe.tews - shouldn't clearCompiledTemplate("foo.tpl") remove "{$template_dir[0]}/foo.tpl" AND "{$template_dir[1]}/foo.tpl"?
        // currently it kills only the first one found (through regular template file identification methods)

        $expected = array(
            'helloworld.tpl#', 'helloworld.tpl#compile1', 'helloworld.tpl#compile2',
            'helloworld2.tpl#', 'helloworld2.tpl#compile1', 'helloworld2.tpl#compile2',
            '[1]ambiguous/case1/foobar.tpl#', '[1]ambiguous/case1/foobar.tpl#compile1', '[1]ambiguous/case1/foobar.tpl#compile2',
        );
        $this->assertEquals(3, $this->smarty->clearCompiledTemplate("ambiguous/case1/foobar.tpl"));

        $this->assertEquals($this->expectFiles($expected), $this->getFiles());
        $this->clearFiles();
    }

}
