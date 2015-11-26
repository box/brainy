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
use \Box\Brainy\Templates\Template;
use \Box\Brainy\Templates\TemplateSource;


class ResourceFile extends Resource
{
    /**
     * populate Source Object with meta data from Resource
     *
     * @param TemplateSource   $source    source object
     * @param Template $_template template object
     */
    public function populate(TemplateSource $source, Template $_template = null)
    {
        $source->filepath = $this->buildFilepath($source, $_template);
        if ($source->filepath === false) {
            return;
        }

        if (is_object($source->smarty->security_policy)) {
            $source->smarty->security_policy->isTrustedResourceDir($source->filepath);
        }

        $source->uid = sha1($source->filepath);
        if ($source->smarty->compile_check && !isset($source->timestamp)) {
            $source->timestamp = $this->getFileTime($source->filepath);
            $source->exists = (bool) $source->timestamp;
        }
    }

    /**
     * populate Source Object with timestamp and exists from Resource
     *
     * @param TemplateSource $source source object
     */
    public function populateTimestamp(TemplateSource $source)
    {
        $source->timestamp = $this->getFileTime($source->filepath);
        $source->exists = (bool) $source->timestamp;
    }

    /**
     * Load template's source from file into current template object
     *
     * @param  TemplateSource $source source object
     * @return string                 template source
     * @throws SmartyException        if source cannot be loaded
     */
    public function getContent(TemplateSource $source)
    {
        if ($source->timestamp) {
            return file_get_contents($source->filepath);
        }
        throw new SmartyException("Unable to read template {$source->type} '{$source->name}'");
    }

    /**
     * Normalize Paths "foo/../bar" to "bar"
     *
     * @param  string  $path path to normalize
     * @param  boolean $ds    respect windows directory separator
     * @return string  normalized path
     */
    protected function normalizePath($path, $ds = true)
    {
        if ($ds) {
            // don't we all just love windows?
            $path = str_replace('\\', '/', $path);
        }

        $offset = 0;

        // resolve simples
        $path = preg_replace('#/\.' . DIRECTORY_SEPARATOR . '(\.' . DIRECTORY_SEPARATOR . ')*#', DIRECTORY_SEPARATOR, $path);
        // resolve parents
        while (true) {
            $parent = strpos($path, DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR, $offset);
            if (!$parent) {
                break;
            } elseif ($path[$parent - 1] === '.') {
                $offset = $parent + 3;
                continue;
            }

            $pos = strrpos($path, DIRECTORY_SEPARATOR, $parent - strlen($path) - 1);
            if ($pos === false) {
                // don't we all just love windows?
                $pos = $parent;
            }

            $path = substr_replace($path, '', $pos, $parent + 3 - $pos);
        }

        return $path;
    }

    /**
     * Determine basename for compiled filename
     *
     * @param  TemplateSource $source source object
     * @return string                 resource's basename
     */
    public function getBasename(TemplateSource $source)
    {
        $_file = $source->name;
        if (($_pos = strpos($_file, ']')) !== false) {
            $_file = substr($_file, $_pos + 1);
        }

        return basename($_file);
    }

    /**
     * @param string $path
     * @return int|bool
     */
    protected function getFileTime($path)
    {
        try {
            return filemtime($path);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param  TemplateSource $source source object
     * @param  string                 $file   file name
     * @return bool                   true if file exists
     */
    protected function fileExists(TemplateSource $source, $file)
    {
        $source->timestamp = is_file($file) ? $this->getFileTime($file) : false;
        return $source->exists = (bool) $source->timestamp;
    }

    /**
     * build template filepath by traversing the template_dir array
     *
     * @param  TemplateSource   $source    source object
     * @param  Template $tpl template object
     * @return string                   fully qualified filepath
     * @throws SmartyException          if default template handler is registered but not callable
     */
    protected function buildFilepath(TemplateSource $source, Template $tpl = null)
    {
        $file = $source->name;
        $tplDirs = $source->smarty->getTemplateDir();
        $_file_exact_match = false;

        // go relative to a given template?
        $_file_is_dotted = $file[0] == '.' && ($file[1] == '.' || $file[1] == DIRECTORY_SEPARATOR);
        if ($tpl && $tpl->parent instanceof Template && $_file_is_dotted) {
            if ($tpl->parent->source->type != 'file') {
                throw new SmartyException("Template '{$file}' cannot be relative to template of resource type '{$tpl->parent->source->type}'");
            }
            $file = dirname($tpl->parent->source->filepath) . DIRECTORY_SEPARATOR . $file;
            $_file_exact_match = true;
            if (!preg_match('/^([\/\\\\]|[a-zA-Z]:[\/\\\\])/', $file)) {
                // the path gained from the parent template is relative to the current working directory
                // as expansions (like include_path) have already been done
                $file = getcwd() . DIRECTORY_SEPARATOR . $file;
            }
        }

        // resolve relative path
        if (!preg_match('/^([\/\\\\]|[a-zA-Z]:[\/\\\\])/', $file)) {
            // don't we all just love windows?
            $_path = str_replace('\\', '/', $file);
            $_path = DIRECTORY_SEPARATOR . trim($file, '/');
            $_was_relative = true;
        } else {
            // don't we all just love windows?
            $_path = str_replace('\\', '/', $file);
        }
        $_path = $this->normalizePath($_path, false);
        if (DIRECTORY_SEPARATOR != '/') {
            // don't we all just love windows?
            $_path = str_replace('/', '\\', $_path);
        }

        // revert to relative
        if (isset($_was_relative)) {
            $_path = substr($_path, 1);
        }

        // this is only required for directories
        $file = rtrim($_path, DIRECTORY_SEPARATOR);

        // files relative to a template only get one shot
        if ($_file_exact_match) {
            return $this->fileExists($source, $file) ? $file : false;
        }

        // Indexed template dirs: [2]foo/bar.tpl
        if (preg_match('#^\[(?P<key>[^\]]+)\](?P<file>.+)$#', $file, $match)) {
            $tplDir = null;
            // try string indexes
            if (isset($tplDirs[$match['key']])) {
                $tplDir = $tplDirs[$match['key']];
            } elseif (is_numeric($match['key'])) {
                // try numeric index
                $tplDir = $tplDirs[(int) $match['key']];
            }

            if ($tplDir) {
                $_filepath = $tplDir . $match['file'];
                if ($this->fileExists($source, $_filepath)) {
                    return $_filepath;
                }
            }
        }

        // relative file name?
        if (!preg_match('/^([\/\\\\]|[a-zA-Z]:[\/\\\\])/', $file)) {
            foreach ($tplDirs as $dir) {
                $path = $this->normalizePath($dir . $file);
                if ($this->fileExists($source, $path)) {
                    return $path;
                }
            }
        }

        // try absolute filepath
        if ($this->fileExists($source, $file)) {
            return $file;
        }

        // give up
        return false;
    }

}
