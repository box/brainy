<?php
/**
 * Smarty Internal Plugin Template
 *
 * This file contains the Smarty template engine
 *
 * @package Brainy
 * @subpackage Template
 * @author Uwe Tews
 */

namespace Box\Brainy\Templates;

use \Box\Brainy\Brainy;


class Template extends TemplateBase
{
    /**
     * $compile_id
     * @var string
     */
    public $compile_id = null;
    /**
     * flag if compiled template is invalid and must be (re)compiled
     * @var bool
     */
    public $mustCompile = null;
    /**
     * special compiled template properties
     * @var array
     */
    public $properties = array('file_dependency' => array(), 'function' => array());
    /**
     * required plugins
     * @var array
     */
    public $required_plugins = array('compiled' => array());
    /**
     * blocks for template inheritance
     * @var array
     */
    public $block_data = array();
    /**
     * internal flag to allow relative path in child template blocks
     * @var bool
     */
    public $allow_relative_path = false;
    /**
     * internal capture runtime stack
     * @var array
     */
    public $_capture_stack = array(0 => array());

    /**
     * Create template data object
     *
     * Some of the global Smarty settings copied to template scope
     * It load the required template resources and cacher plugins
     *
     * @param string                   $template_resource template resource string
     * @param \Box\Brainy\Brainy                   $smarty            Smarty instance
     * @param Template $_parent           back pointer to parent object with variables or null
     * @param mixed                    $_compile_id       compile id or null
     */
    public function __construct($template_resource, $smarty, $_parent = null, $_compile_id = null) {
        parent::__construct($smarty);
        // Smarty parameter
        $this->compile_id = $_compile_id === null ? $this->smarty->compile_id : $_compile_id;
        $this->parent = $_parent;
        // Template resource
        $this->template_resource = $template_resource;
        // copy block data of template inheritance
        if ($this->parent instanceof Template) {
            $this->block_data = $this->parent->block_data;
        }

        $this->smarty->fetchedTemplate($template_resource);
    }

    /**
     * Returns if the current template must be compiled by the Smarty compiler
     *
     * It does compare the timestamps of template source and the compiled templates
     *
     * @return boolean true if the template must be compiled
     */
    public function mustCompile() {
        if (!$this->source->exists) {
            if ($this->parent instanceof Template) {
                $parent_resource = " in '$this->parent->template_resource}'";
            } else {
                $parent_resource = '';
            }
            throw new SmartyException("Unable to load template {$this->source->type} '{$this->source->name}'{$parent_resource}");
        }
        return (!$this->source->uncompiled &&
                ($this->smarty->force_compile ||
                    $this->source->recompiled ||
                    $this->compiled->timestamp === false ||
                    ($this->smarty->compile_check && $this->compiled->timestamp < $this->source->timestamp)
                    )
                );
    }

    /**
     * Compiles the template
     *
     * If the template is not evaluated the compiled template is saved on disk
     */
    public function compileTemplateSource() {
        if (!$this->source->recompiled) {
            $this->properties['file_dependency'] = array();
            if (!$this->source->components) {
                $this->properties['file_dependency'][$this->source->uid] = array($this->source->filepath, $this->source->timestamp, $this->source->type);
            }
        }
        // compile locking
        if ($this->smarty->compile_locking && !$this->source->recompiled) {
            if ($saved_timestamp = $this->compiled->timestamp) {
                touch($this->compiled->filepath);
            }
        }
        // call compiler
        try {
            $code = $this->compiler->compileTemplate($this);
        } catch (Exception $e) {
            // restore old timestamp in case of error
            if ($this->smarty->compile_locking && !$this->source->recompiled && $saved_timestamp) {
                touch($this->compiled->filepath, $saved_timestamp);
            }
            throw $e;
        }
        // compiling succeded
        if (!$this->source->recompiled) {
            // write compiled template
            $_filepath = $this->compiled->filepath;
            if ($_filepath === false) {
                throw new SmartyException('getCompiledFilepath() did not return a destination to save the compiled template to');
            }
            self::writeFile($_filepath, $code, $this->smarty);
            $this->compiled->exists = true;
            $this->compiled->isCompiled = true;
        }
        // release compiler object to free memory
        unset($this->compiler);
    }

    /**
     * Template code runtime function to get subtemplate content
     *
     * @param string  $template       the resource handle of the template file
     * @param mixed   $compile_id     compile id to be used with this template
     * @param array   $vars           optional  variables to assign
     * @param int     $parent_scope   scope in which {include} should execute
     * @return string template content
     */
    public function getSubTemplate($template, $compile_id, $data, $parent_scope) {
        // already in template cache?
        $_templateId = $this->smarty->joined_template_dir . '#' . $template . $compile_id;

        if (isset($_templateId[150])) {
            $_templateId = sha1($_templateId);
        }
        if (isset($this->smarty->template_objects[$_templateId])) {
            // clone cached template object because of possible recursive call
            $tpl = clone $this->smarty->template_objects[$_templateId];
            $tpl->parent = $this;
        } else {
            $tpl = new Template($template, $this->smarty, $this, $compile_id);
        }
        // get variables from calling scope
        if ($parent_scope == Brainy::SCOPE_LOCAL) {
            $tpl->tpl_vars = $this->tpl_vars;
            $tpl->tpl_vars['smarty'] = clone $this->tpl_vars['smarty'];
        } elseif ($parent_scope == Brainy::SCOPE_PARENT) {
            $tpl->tpl_vars = &$this->tpl_vars;
        } elseif ($parent_scope == Brainy::SCOPE_GLOBAL) {
            $tpl->tpl_vars = &Brainy::$global_tpl_vars;
        } elseif (($scope_ptr = $this->getScopePointer($parent_scope)) == null) {
            $tpl->tpl_vars = &$this->tpl_vars;
        } else {
            $tpl->tpl_vars = &$scope_ptr->tpl_vars;
        }
        if ($data) {
            // set up variable values
            foreach ($data as $_key => $_val) {
                $tpl->tpl_vars[$_key] = new Variable($_val);
            }
        }

        return $tpl->fetch(null, null, null, null, false, false, true);
    }

    /**
     * Template code runtime function to set up an inline subtemplate
     *
     * @param string  $template       the resource handle of the template file
     * @param mixed   $compile_id     compile id to be used with this template
     * @param array   $vars           optional  variables to assign
     * @param int     $parent_scope   scope in which {include} should execute
     * @returns string template content
     */
    public function setupInlineSubTemplate($template, $compile_id, $data, $parent_scope) {
        $tpl = new Template($template, $this->smarty, $this, $compile_id);
        // get variables from calling scope
        if ($parent_scope == Brainy::SCOPE_LOCAL) {
            $tpl->tpl_vars = $this->tpl_vars;
            $tpl->tpl_vars['smarty'] = clone $this->tpl_vars['smarty'];
        } elseif ($parent_scope == Brainy::SCOPE_PARENT) {
            $tpl->tpl_vars = &$this->tpl_vars;
        } elseif ($parent_scope == Brainy::SCOPE_GLOBAL) {
            $tpl->tpl_vars = &Brainy::$global_tpl_vars;
        } elseif (($scope_ptr = $this->getScopePointer($parent_scope)) == null) {
            $tpl->tpl_vars = &$this->tpl_vars;
        } else {
            $tpl->tpl_vars = &$scope_ptr->tpl_vars;
        }
        if (!empty($data)) {
            // set up variable values
            foreach ($data as $_key => $_val) {
                $tpl->tpl_vars[$_key] = new Variable($_val);
            }
        }

        return $tpl;
    }


    /**
     * Create code frame for compiled and cached templates
     *
     * @param  string $content optional template content
     * @param  bool   $cache   flag for cache file
     * @return string
     */
    public function createTemplateCodeFrame($content = '', $cache = false) {
        $plugins_string = '';
        // include code for plugins
        if (!empty($this->required_plugins['compiled'])) {
            foreach ($this->required_plugins['compiled'] as $tmp) {
                foreach ($tmp as $data) {
                    $file = addslashes($data['file']);
                    if (is_Array($data['function'])) {
                        $plugins_string .= "if (!is_callable(array('{$data['function'][0]}','{$data['function'][1]}'))) include '{$file}';\n";
                    } else {
                        $plugins_string .= "if (!is_callable('{$data['function']}')) include '{$file}';\n";
                    }
                }
            }
        }
        // build property code
        $output = '';
        if (!$this->source->recompiled) {
            $output = "/*%%SmartyHeaderCode%%*/\n";
        }
        if ($cache) {
            // remove compiled code of{function} definition
            unset($this->properties['function']);
        }
        $this->properties['version'] = Brainy::SMARTY_VERSION;
        if (!isset($this->properties['unifunc'])) {
            $this->properties['unifunc'] = 'content_' . str_replace(array('.',','), '_', uniqid('', true));
        }
        if (!$this->source->recompiled) {
            $output .= "\$_valid = \$_smarty_tpl->decodeProperties(" . var_export($this->properties, true) . ',' . ($cache ? 'true' : 'false') . "); /*/%%SmartyHeaderCode%%*/\n";
            $output .= 'if ($_valid && !is_callable(\'' . $this->properties['unifunc'] . "')) {\n";

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
     * - Decode saved properties from compiled template and cache files
     * - Check if compiled or cache file is valid
     *
     * @param  array $properties special template properties
     * @param  bool  $cache      flag if called from cache file
     * @return bool  flag if compiled or cache file is valid
     */
    public function decodeProperties($properties, $cache = false)
    {
        if (isset($properties['file_dependency'])) {
            $this->properties['file_dependency'] = array_merge($this->properties['file_dependency'], $properties['file_dependency']);
        }
        if (!empty($properties['function'])) {
            $this->properties['function'] = array_merge($this->properties['function'], $properties['function']);
            $this->smarty->template_functions = array_merge($this->smarty->template_functions, $properties['function']);
        }
        $this->properties['version'] = isset($properties['version']) ? $properties['version'] : '';
        $this->properties['unifunc'] = $properties['unifunc'];
        // check file dependencies at compiled code
        if ($this->properties['version'] != Brainy::SMARTY_VERSION) {
            return false;
        }

        if (((!$cache && $this->smarty->compile_check && empty($this->compiled->_properties) && !$this->compiled->isCompiled) || $cache && ($this->smarty->compile_check === true || $this->smarty->compile_check === Brainy::COMPILECHECK_ON)) && !empty($this->properties['file_dependency'])) {
            foreach ($this->properties['file_dependency'] as $_file_to_check) {
                if ($_file_to_check[2] == 'file' || $_file_to_check[2] == 'php') {
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
        $this->compiled->_properties = $properties;

        return true;
    }

    /**
     * Get parent or root of template parent chain
     *
     * @param  int   $scope pqrent or root scope
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
     * set Smarty property in template context
     *
     * @param string $property_name property name
     * @param mixed  $value         value
     */
    public function __set($property_name, $value)
    {
        switch ($property_name) {
            case 'source':
            case 'compiled':
            case 'compiler':
                $this->$property_name = $value;

                return;

            // FIXME: routing of template -> smarty attributes
            default:
                if (property_exists($this->smarty, $property_name)) {
                    $this->smarty->$property_name = $value;

                    return;
                }
        }

        throw new SmartyException("invalid template property '$property_name'.");
    }

    /**
     * get Smarty property in template context
     *
     * @param string $property_name property name
     */
    public function __get($property_name)
    {
        switch ($property_name) {
            case 'source':
                if (strlen($this->template_resource) == 0) {
                    throw new SmartyException('Missing template name');
                }
                $this->source = \Box\Brainy\Resources\Resource::source($this);
                // cache template object under a unique ID
                // do not cache eval resources
                if ($this->source->type != 'eval') {
                    $_templateId = $this->smarty->joined_template_dir . '#' . $this->template_resource . $this->compile_id;

                    if (isset($_templateId[150])) {
                        $_templateId = sha1($_templateId);
                    }
                    $this->smarty->template_objects[$_templateId] = $this;
                }

                return $this->source;

            case 'compiled':
                $this->compiled = $this->source->getCompiled($this);
                return $this->compiled;

            case 'compiler':
                $this->compiler = new \Box\Brainy\Compiler\TemplateCompiler($this->smarty);
                return $this->compiler;

            // FIXME: routing of template -> smarty attributes
            default:
                if (property_exists($this->smarty, $property_name)) {
                    return $this->smarty->$property_name;
                }
        }

        throw new SmartyException("template property '$property_name' does not exist.");
    }

    /**
     * Writes file in a safe way to disk
     *
     * @param  string  $_filepath complete filepath
     * @param  string  $_contents file content
     * @param  Smarty  $smarty    smarty instance
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
        } catch(Exception $e) {
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
