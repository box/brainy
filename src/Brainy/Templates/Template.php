<?php
/**
 * Smarty Internal Plugin Template
 *
 * This file contains the Smarty template engine
 *
 * @package    Brainy
 * @subpackage Template
 * @author     Uwe Tews
 */

namespace Box\Brainy\Templates;

use \Box\Brainy\Brainy;
use \Box\Brainy\Exceptions\SmartyException;

class Template extends TemplateBase
{
    /**
     * $compile_id
     * @var string
     */
    public $compile_id = null;
    /**
     * special compiled template properties
     * @var array
     */
    public $properties = array('file_dependency' => array());
    /**
     * required plugins
     * @var array
     */
    public $required_plugins = array('compiled' => array());

    /**
     * Full template resource
     * @var string
     */
    public $template_resource;


    public $source;
    public $compiled;

    /**
     * Create template data object
     *
     * Some of the global Smarty settings copied to template scope
     * It load the required template resources and cacher plugins
     *
     * @param string    $tplResource  template resource string
     * @param Brainy    $brainy       Brainy instance
     * @param Template  $parent       back pointer to parent object with variables or null
     * @param mixed     $compileID    compile id or null
     * @param bool|void $suppressData Prevents data from being copied into the variable scope
     */
    public function __construct($tplResource, $brainy, $parent = null, $compileID = null, $suppressData = false)
    {
        if ($parent === null || !$suppressData) {
            parent::__construct($brainy);
        } else {
            // Copied from the TemplateBase constructor
            $this->smarty = $brainy;
        }
        $this->compile_id = $compileID ?: $this->smarty->compile_id;
        $this->parent = $parent;
        // Template resource
        $this->template_resource = $tplResource;

        $this->smarty->fetchedTemplate($tplResource);

        $this->source = \Box\Brainy\Resources\Resource::source($this, $brainy, $this->template_resource);
        $this->compiled = $this->source->getCompiled($this);

        if (!$suppressData && $this->parent) {
            if (is_array($this->parent)) {
                $this->applyDataFrom($this->parent, false);
            } else {
                $this->cloneDataFrom($this->parent, false);
            }
        }
    }

    /**
     * Returns if the current template must be compiled by the Smarty compiler
     *
     * It does compare the timestamps of template source and the compiled templates
     *
     * @return boolean true if the template must be compiled
     */
    public function mustCompile()
    {
        if (!$this->source->exists) {
            if ($this->parent instanceof Template) {
                $parent_resource = " in '$this->parent->template_resource}'";
            } else {
                $parent_resource = '';
            }
            throw new SmartyException(
                "Unable to load template {$this->source->type} '{$this->source->name}'{$parent_resource}"
            );
        }
        return ($this->smarty->force_compile ||
                    $this->source->recompiled ||
                    $this->compiled->timestamp === false ||
                    ($this->smarty->compile_check && $this->compiled->timestamp < $this->source->timestamp)
                );
    }

    /**
     * Compiles the template
     * @return string The compiled template source
     */
    public function compileTemplateSource()
    {
        if (!$this->source->recompiled) {
            $this->properties['file_dependency'] = array();
            $this->properties['file_dependency'][$this->source->uid] = array(
                $this->source->filepath,
                $this->source->timestamp,
                $this->source->type
            );
        }
        // compile locking
        if ($this->smarty->compile_locking && !$this->source->recompiled) {
            if ($saved_timestamp = $this->compiled->timestamp) {
                touch($this->compiled->filepath);
            }
        }
        // call compiler
        $compiler = null;
        try {
            $compiler = new \Box\Brainy\Compiler\TemplateCompiler($this->smarty);
            $code = $compiler->compileTemplate($this);
            unset($compiler);
        } catch (Exception $e) {
            // restore old timestamp in case of error
            if ($this->smarty->compile_locking && !$this->source->recompiled && $saved_timestamp) {
                touch($this->compiled->filepath, $saved_timestamp);
            }
            if ($compiler) {
                unset($compiler);
            }
            throw $e;
        }
        // compiling succeded
        if (!$this->source->recompiled) {
            // write compiled template
            $_filepath = $this->compiled->filepath;
            if ($_filepath === false) {
                throw new SmartyException(
                    'getCompiledFilepath() did not return a destination to save the compiled template to'
                );
            }
            self::writeFile($_filepath, $code, $this->smarty);
            $this->compiled->exists = true;
            $this->compiled->isCompiled = true;
        }

        return $code;
    }

    /**
     * Create code frame for compiled and cached templates
     *
     * @param  string $content optional template content
     * @return string
     */
    public function createTemplateCodeFrame($content = '')
    {
        $plugins_string = '';
        // include code for plugins
        if (!empty($this->required_plugins['compiled'])) {
            foreach ($this->required_plugins['compiled'] as $tmp) {
                foreach ($tmp as $data) {
                    $file = addslashes($data['file']);
                    if (is_Array($data['function'])) {
                        $func = $data['function'];
                        $plugins_string .= "if (!is_callable(array('{$func[0]}', '{$func[1]}'))) ";
                        $plugins_string .= "include '{$file}';\n";
                    } else {
                        $plugins_string .= "if (!is_callable('{$data['function']}')) include '{$file}';\n";
                    }
                }
            }
        }
        // build property code
        $output = '';
        $this->properties['version'] = Brainy::SMARTY_VERSION;
        if (!isset($this->properties['unifunc'])) {
            $this->properties['unifunc'] = 'content_' . str_replace(array('.',','), '_', uniqid('', true));
        }
        if (!$this->source->recompiled) {
            $decode = "\$_smarty_tpl->decodeProperties(" . var_export($this->properties, true) . ')';
            $output .= 'if (' . $decode . ' && !is_callable(\'' . $this->properties['unifunc'] . "')) {\n";

            // Output a proper PHPDoc for Augmented Types users.
            $output .= <<<'PHPDOC'
/**
 * @param \Box\Brainy\Templates\TemplateBase $_smarty_tpl The smarty template instance
 * @return void
 */

PHPDOC;

            $output .= 'function ' . $this->properties['unifunc'] . "(\$_smarty_tpl) {\n";
        }

        $output .= $plugins_string;
        $output .= $content;
        if (!$this->source->recompiled) {
            $output .= "\n}\n}\n";
        }

        return $output;
    }

    /**
     * This function is executed automatically when a compiled or cached template file is included
     *
     * @param  array $properties special template properties
     * @return bool  flag if compiled or cache file is valid
     */
    public function decodeProperties($properties)
    {
        if (isset($properties['file_dependency'])) {
            foreach ($properties['file_dependency'] as $key => $val) {
                $this->properties['file_dependency'][$key] = $val;
            }
        }
        $this->properties['version'] = isset($properties['version']) ? $properties['version'] : '';
        $this->properties['unifunc'] = $properties['unifunc'];
        // check file dependencies at compiled code
        if ($this->properties['version'] != Brainy::SMARTY_VERSION) {
            return false;
        }

        if ($this->smarty->compile_check
            && empty($this->compiled->properties)
            && !$this->compiled->isCompiled
            && !empty($this->properties['file_dependency'])
        ) {

            foreach ($this->properties['file_dependency'] as $_file_to_check) {
                if ($_file_to_check[2] == 'file') {
                    if ($this->source->filepath == $_file_to_check[0] && isset($this->source->timestamp)) {
                        // do not recheck current template
                        $mtime = $this->source->timestamp;
                    } else {
                        // file and php types can be checked without loading the respective resource handlers
                        $mtime = @filemtime($_file_to_check[0]);
                    }
                } elseif ($_file_to_check[2] == 'string') {
                    continue;
                } else {
                    $source = \Box\Brainy\Resources\Resource::source(null, $this->smarty, $_file_to_check[0]);
                    $mtime = $source->timestamp;
                }
                if (!$mtime || $mtime > $_file_to_check[1]) {
                    return false;
                }
            }
        }
        // store data in reusable Smarty_Template_Compiled
        $this->compiled->properties = $properties;

        return true;
    }

    /**
     * Get parent or root of template parent chain
     *
     * @param  int $scope pqrent or root scope
     * @return mixed object
     */
    public function getScopePointer($scope)
    {
        if ($scope == Brainy::SCOPE_PARENT && !empty($this->parent)) {
            return $this->parent;
        }
        if ($scope == Brainy::SCOPE_ROOT && !empty($this->parent)) {
            $ptr = $this->parent;
            while (!empty($ptr->parent)) {
                $ptr = $ptr->parent;
            }

            return $ptr;
        }

        return null;
    }


    /**
     * Writes file in a safe way to disk
     *
     * @param  string $_filepath complete filepath
     * @param  string $_contents file content
     * @param  Smarty $smarty    smarty instance
     * @return boolean true
     */
    public static function writeFile($_filepath, $_contents, Brainy $smarty)
    {
        if ($smarty->_file_perms !== null) {
            $old_umask = umask(0);
        }

        $_dirpath = dirname($_filepath);
        // if subdirs, create dir structure
        if ($_dirpath !== '.' && !file_exists($_dirpath)) {
            mkdir($_dirpath, $smarty->_dir_perms === null ? 0777 : $smarty->_dir_perms, true);
        }

        // write to tmp file, then move to overt file lock race condition
        $_tmp_file = $_dirpath . DIRECTORY_SEPARATOR . str_replace(array('.',','), '_', uniqid('wrt', true));
        if (!file_put_contents($_tmp_file, $_contents)) {
            throw new SmartyException("unable to write file {$_tmp_file}");
        }

        /*
         * Windows' rename() fails if the destination exists,
         * Linux' rename() properly handles the overwrite.
         * Simply unlink()ing a file might cause other processes
         * currently reading that file to fail, but linux' rename()
         * seems to be smart enough to handle that for us.
         */
        try {
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                // remove original file
                unlink($_filepath);
                // rename tmp file
                $success = rename($_tmp_file, $_filepath);
            } else {
                // rename tmp file
                $success = rename($_tmp_file, $_filepath);
                if (!$success) {
                    // remove original file
                    unlink($_filepath);
                    // rename tmp file
                    $success = rename($_tmp_file, $_filepath);
                }
            }
        } catch (Exception $e) {
            $success = false;
        }

        if (!$success) {
            throw new SmartyException("unable to write file {$_filepath}");
        }

        if ($smarty->_file_perms !== null) {
            // set file permissions
            chmod($_filepath, $smarty->_file_perms);
            umask($old_umask);
        }

        return true;
    }
}
