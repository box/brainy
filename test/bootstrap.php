<?php

namespace Box\Brainy\Tests;

require __DIR__ . '/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;

class Smarty_TestCase extends TestCase
{
    protected $smarty = null;
    protected $smartyBC = null;

    public function setUp(): void
    {
        $this->smarty = new \Box\Brainy\Brainy();
        $this->setUpInstance($this->smarty);
        $this->smartyBC = new \Box\Brainy\SmartyBC();
        $this->setUpInstance($this->smartyBC);

        \Box\Brainy\Brainy::$default_assign_scope = \Box\Brainy\Brainy::SCOPE_LOCAL;
        \Box\Brainy\Brainy::$enforce_expression_modifiers = array();
        \Box\Brainy\Brainy::$global_tpl_vars = array();
        \Box\Brainy\Resources\Resource::reset();

        $this->clearFiles();
    }

    protected function setUpInstance($smarty)
    {
        $smarty->setTemplateDir(realpath('test' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR));
        $smarty->setCompileDir(realpath('test' . DIRECTORY_SEPARATOR . 'compiled' . DIRECTORY_SEPARATOR));
        $smarty->setPluginsDir(BRAINY_PLUGINS_DIR);
        $smarty->tpl_vars = array();
        $smarty->force_compile = false;
        $smarty->auto_literal = true;
        $smarty->registered_plugins = array();
        $smarty->default_modifiers = array();
        $smarty->escape_html = false;
        $smarty->use_sub_dirs = false;
        $smarty->security_policy = null;
        $smarty->left_delimiter = '{';
        $smarty->right_delimiter = '}';
        $smarty->enableSecurity();
        $smarty->compile_id = null;
        $smarty->safe_lookups = \Box\Brainy\Brainy::LOOKUP_UNSAFE;
    }

    protected function clearFiles()
    {
        $directory = realpath($this->smarty->getCompileDir());

        $di = new \RecursiveDirectoryIterator($directory);
        $it = new \RecursiveIteratorIterator($di, \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($it as $file) {
            $_file = $file->__toString();

            if (preg_match("#[\\\\/]\.#", $_file)) {
                continue;
            }

            if ($file->isDir()) {
                rmdir($_file);
            } else {
                unlink($_file);
            }

        }
    }

}


class _object_toString
{
    protected $string = null;
    public function __construct($string) {
        $this->string = (string) $string;
    }

    public function __toString() {
        return $this->string;
    }
}

class _object_noString
{
    protected $string = null;
    public function __construct($string) {
        $this->string = (string) $string;
    }
}

ini_set('date.timezone', 'UTC');
