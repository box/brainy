<?php
/**
* Smarty PHPunit tests for File resources
*
* @package PHPunit
* @author Uwe Tews
*/

namespace Box\Brainy\Tests;


class FileResourceTest extends Smarty_TestCase
{
    protected function relative($path) {
        $path = str_replace( dirname(__FILE__), '.', $path );
        if (DIRECTORY_SEPARATOR == "\\") {
            $path = str_replace( "\\", "/", $path );
        }

        return $path;
    }

    public function testGetTemplateFilepath() {
        $tpl = $this->smarty->createTemplate('helloworld.tpl');
        $this->assertEquals(realpath('test/templates/helloworld.tpl'), str_replace('\\','/',$tpl->source->filepath));
    }

    public function testTemplateFileExists1() {
        $tpl = $this->smarty->createTemplate('helloworld.tpl');
        $this->assertTrue($tpl->source->exists);
    }
    public function testTemplateFileExists2() {
        $this->assertTrue($this->smarty->templateExists('helloworld.tpl'));
    }

    public function testTemplateFileNotExists1() {
        $tpl = $this->smarty->createTemplate('notthere.tpl');
        $this->assertFalse($tpl->source->exists);
    }
    public function testTemplateFileNotExists2() {
        $this->assertFalse($this->smarty->templateExists('notthere.tpl'));
    }
    public function testTemplateFileNotExists3() {
        try {
            $result = $this->smarty->fetch('notthere.tpl');
        } catch (Exception $e) {
            $this->assertContains('Unable to load template file \'notthere.tpl\'', $e->getMessage());

            return;
        }
        $this->fail('Exception for not existing template is missing');
    }

    public function testGetTemplateTimestamp() {
        $tpl = $this->smarty->createTemplate('helloworld.tpl');
        $this->assertTrue(is_integer($tpl->source->timestamp));
        $this->assertEquals(10, strlen($tpl->source->timestamp));
    }

    public function testGetTemplateSource() {
        $tpl = $this->smarty->createTemplate('helloworld.tpl');
        $this->assertEquals('hello world', $tpl->source->content);
    }

    public function testUsesCompiler() {
        $tpl = $this->smarty->createTemplate('helloworld.tpl');
        $this->assertFalse($tpl->source->uncompiled);
    }

    public function testIsEvaluated() {
        $tpl = $this->smarty->createTemplate('helloworld.tpl');
        $this->assertFalse($tpl->source->recompiled);
    }

    public function testGetCompiledFilepath() {
        $tpl = $this->smarty->createTemplate('helloworld.tpl');
        $expected = './compiled/'.sha1($this->smarty->getTemplateDir(0) . 'helloworld.tpl').'.file.helloworld.tpl.php';
        $this->assertEquals($expected, $this->relative($tpl->compiled->filepath));
    }
    public function testGetCompiledTimestampPrepare() {
        $tpl = $this->smarty->createTemplate('helloworld.tpl');
        // create dummy compiled file
        file_put_contents($tpl->compiled->filepath, '<?php ?>');
        touch($tpl->compiled->filepath, $tpl->source->timestamp);
        $this->assertTrue(file_exists($tpl->compiled->filepath));
    }

    public function testMustCompileAtForceCompile() {
        $this->smarty->force_compile = true;
        $tpl = $this->smarty->createTemplate('helloworld.tpl');
        $this->assertTrue($tpl->mustCompile());
    }

    public function testMustCompileTouchedSource() {
        $this->smarty->force_compile = false;
        $tpl = $this->smarty->createTemplate('helloworld.tpl');
        touch($tpl->source->filepath);
        // reset cache for this test to work
        unset($tpl->source->timestamp);
        $this->assertTrue($tpl->mustCompile());
        // clean up for next tests
        $this->smarty->clearCompiledTemplate();
    }

    public function testCompileTemplateFile() {
        $tpl = $this->smarty->createTemplate('helloworld.tpl');
        $tpl->compileTemplateSource();
        $this->assertTrue($tpl->compiled !== null);
    }


    public function testGetRenderedTemplate() {
        $tpl = $this->smarty->createTemplate('helloworld.tpl');
        $this->assertEquals('hello world', $tpl->fetch());
    }

    public function testRelativeInclude() {
        $result = $this->smarty->fetch('relative.tpl');
        $this->assertContains('hello world', $result);
    }

    public function testRelativeIncludeSub() {
        $result = $this->smarty->fetch('sub/relative.tpl');
        $this->assertContains('hello world', $result);
    }

    public function testRelativeIncludeFail() {
        try {
            $this->smarty->fetch('relative_sub.tpl');
        } catch (Exception $e) {
            $this->assertContains(htmlentities("Unable to load template"), $e->getMessage());

            return;
        }
        $this->fail('Exception for unknown relative filepath has not been raised.');
    }

    public function testRelativeIncludeFailOtherDir() {
        $this->smarty->addTemplateDir('test/templates_2');
        try {
            $this->smarty->fetch('relative_notexist.tpl');
        } catch (Exception $e) {
            $this->assertContains("Unable to load template", $e->getMessage());

            return;
        }
        $this->fail('Exception for unknown relative filepath has not been raised.');
    }

    public function testRelativeFetch() {
        $this->smarty->setTemplateDir(array(
            dirname(__FILE__) . '/does-not-exist/',
            dirname(__FILE__) . '/templates/sub/',
        ));
        $this->smarty->security_policy = null;
        $this->assertEquals('hello world', $this->smarty->fetch('./relative.tpl'));
        $this->assertEquals('hello world', $this->smarty->fetch('../helloworld.tpl'));
    }

    protected function _relativeMap($map, $cwd=null) {
        foreach ($map as $file => $result) {
            $this->smarty->clearCompiledTemplate();
            if ($result === null) {
                try {
                    $this->smarty->fetch($file);
                    if ($cwd !== null) {
                        chdir($cwd);
                    }

                    $this->fail('Exception expected for ' . $file);

                    return;
                } catch (SmartyException $e) {
                    // this was expected to fail
                }
            } else {
                try {
                    $_res = $this->smarty->fetch($file);
                    $this->assertEquals($result, $_res, $file);
                } catch (Exception $e) {
                    if ($cwd !== null) {
                        chdir($cwd);
                    }

                    throw $e;
                }
            }
        }

        if ($cwd !== null) {
            chdir($cwd);
        }
    }
    public function testRelativity() {
        $this->smarty->security_policy = null;

        $cwd = getcwd();
        $dn = dirname(__FILE__);

        $this->smarty->setCompileDir($dn . '/compiled/');
        $this->smarty->setTemplateDir(array(
            $dn . '/templates/relativity/theory/',
        ));

        $map = array(
            'foo.tpl' => 'theory',
            './foo.tpl' => 'theory',
            '././foo.tpl' => 'theory',
            '../foo.tpl' => 'relativity',
            '.././foo.tpl' => 'relativity',
            './../foo.tpl' => 'relativity',
            'einstein/foo.tpl' => 'einstein',
            './einstein/foo.tpl' => 'einstein',
            '../theory/einstein/foo.tpl' => 'einstein',
            'test/templates/relativity/relativity.tpl' => 'relativity',
            './test/templates/relativity/relativity.tpl' => 'relativity',
        );

        $this->_relativeMap($map);

        $this->smarty->setTemplateDir(array(
            'test/templates/relativity/theory/',
        ));

        $map = array(
            'foo.tpl' => 'theory',
            './foo.tpl' => 'theory',
            '././foo.tpl' => 'theory',
            '../foo.tpl' => 'relativity',
            '.././foo.tpl' => 'relativity',
            './../foo.tpl' => 'relativity',
            'einstein/foo.tpl' => 'einstein',
            './einstein/foo.tpl' => 'einstein',
            '../theory/einstein/foo.tpl' => 'einstein',
            'test/templates/relativity/relativity.tpl' => 'relativity',
            './test/templates/relativity/relativity.tpl' => 'relativity',
        );

        $this->_relativeMap($map);
    }
    public function testRelativityPrecedence() {
        $this->smarty->security_policy = null;

        $cwd = getcwd();
        $dn = dirname(__FILE__);

        $this->smarty->setCompileDir($dn . '/compiled/');
        $this->smarty->setTemplateDir(array(
            $dn . '/templates/relativity/theory/einstein/',
        ));

        $map = array(
            'foo.tpl' => 'einstein',
            './foo.tpl' => 'einstein',
            '././foo.tpl' => 'einstein',
            '../foo.tpl' => 'theory',
            '.././foo.tpl' => 'theory',
            './../foo.tpl' => 'theory',
            '../../foo.tpl' => 'relativity',
        );

        $newdest = $dn . '/templates/relativity/theory/';
        chdir($newdest);
        $this->_relativeMap($map, $cwd);

        $map = array(
            '../theory.tpl' => 'theory',
            './theory.tpl' => 'theory',
            '../../relativity.tpl' => 'relativity',
            '../relativity.tpl' => 'relativity',
            './einstein.tpl' => 'einstein',
            'einstein/einstein.tpl' => 'einstein',
            './einstein/einstein.tpl' => 'einstein',
        );

        chdir($dn . '/templates/relativity/theory/');
        $this->_relativeMap($map, $cwd);
    }
    public function testRelativityRelRel() {
        $this->smarty->security_policy = null;

        $cwd = getcwd();
        $dn = dirname(__FILE__);

        $this->smarty->setCompileDir($dn . '/compiled/');
        $this->smarty->setTemplateDir(array(
            '../..',
        ));

        $map = array(
            'foo.tpl' => 'relativity',
            './foo.tpl' => 'relativity',
            '././foo.tpl' => 'relativity',
        );

        $newdest = $dn . '/templates/relativity/theory/einstein';
        chdir($newdest);
        $this->_relativeMap($map, $cwd);

        $map = array(
            'relativity.tpl' => 'relativity',
            './relativity.tpl' => 'relativity',
            'theory/theory.tpl' => 'theory',
            './theory/theory.tpl' => 'theory',
        );

        $newdest = $dn . '/templates/relativity/theory/einstein/';
        chdir($newdest);
        $this->_relativeMap($map, $cwd);

        $map = array(
            'foo.tpl' => 'theory',
            './foo.tpl' => 'theory',
            'theory.tpl' => 'theory',
            './theory.tpl' => 'theory',
            'einstein/einstein.tpl' => 'einstein',
            './einstein/einstein.tpl' => 'einstein',
            '../theory/einstein/einstein.tpl' => 'einstein',
            '../relativity.tpl' => 'relativity',
            './../relativity.tpl' => 'relativity',
            '.././relativity.tpl' => 'relativity',
        );

        $this->smarty->setTemplateDir(array('..'));
        chdir($dn . '/templates/relativity/theory/einstein/');
        $this->_relativeMap($map, $cwd);
    }
    public function testRelativityRelRel1() {
        $this->smarty->security_policy = null;

        $cwd = getcwd();
        $dn = dirname(__FILE__);

        $this->smarty->setCompileDir($dn . '/compiled/');
        $this->smarty->setTemplateDir(array(
            '..',
        ));

        $map = array(
            'foo.tpl' => 'theory',
            './foo.tpl' => 'theory',
            'theory.tpl' => 'theory',
            './theory.tpl' => 'theory',
            'einstein/einstein.tpl' => 'einstein',
            './einstein/einstein.tpl' => 'einstein',
            '../theory/einstein/einstein.tpl' => 'einstein',
            '../relativity.tpl' => 'relativity',
            './../relativity.tpl' => 'relativity',
            '.././relativity.tpl' => 'relativity',
        );

        chdir($dn . '/templates/relativity/theory/einstein/');
        $this->_relativeMap($map, $cwd);
    }

}
