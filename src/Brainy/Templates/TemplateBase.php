<?php
/**
 * @package Brainy
 * @author Matt Basta
 * @author Uwe Tews
 */

namespace Box\Brainy\Templates;

use Box\Brainy\Brainy;
use Box\Brainy\Exceptions\BrainyStrictModeException;
use Box\Brainy\Exceptions\SmartyException;


class TemplateBase extends TemplateData
{
    /**
     * Template resource
     * @var string
     */
    public $template_resource = null;
    /**
     * Flag indicating that the template is running in strict mode
     * @var bool
     */
    public $strict_mode = false;
    /**
     * Global smarty instance
     * @var Smarty
     */
    public $smarty = null;


    /**
     * @param \Box\Brainy\Brainy $brainyInstance
     */
    public function __construct($brainyInstance)
    {
        $this->smarty = &$brainyInstance;
        $this->tpl_vars = $brainyInstance->tpl_vars;
    }


    /**
     * @param string $reason
     * @return void
     * @throws BrainyStrictModeException
     */
    public function assert_is_not_strict($reason)
    {
        if (Brainy::$strict_mode || $this->strict_mode) {
            throw new BrainyStrictModeException('Strict Mode: ' . $reason);
        }
    }

    /**
     * Renders and returns a template.
     *
     * This returns the template output instead of displaying it.
     *
     * @param  string|void $template         the resource handle of the template file or template object
     * @param  mixed|void  $cache_id         no-op
     * @param  mixed|void  $compile_id       compile id to be used with this template
     * @param  object|void $parent           next higher level of Brainy variables
     * @param  bool|void   $display          noop
     * @param  bool|void   $merge_tpl_vars   if true parent template variables merged in to local scope
     * @return string rendered template output
     */
    public function fetch($template = null, $cache_id = null, $compile_id = null, $parent = null, $display = false, $merge_tpl_vars = true) {
        if ($template === null && $this instanceof Template) {
            $template = $this;
        }
        if ($cache_id !== null && is_object($cache_id)) {
            $parent = $cache_id;
            $cache_id = null;
        }
        if ($parent === null && ($this instanceof Brainy || is_string($template))) {
            $parent = $this;
        }
        // create template object if necessary
        $_template = ($template instanceof Template)
            ? $template
            : $this->smarty->createTemplate($template, $cache_id, $compile_id, $parent, false);

        // merge all variable scopes into template
        if ($merge_tpl_vars) {
            // save local variables
            $save_tpl_vars = $_template->tpl_vars;
            $ptr_array = array($_template);
            $ptr = $_template;
            while (isset($ptr->parent)) {
                $ptr_array[] = $ptr = $ptr->parent;
            }
            $ptr_array = array_reverse($ptr_array);
            $parent_ptr = reset($ptr_array);
            $tpl_vars = $parent_ptr->tpl_vars;
            while ($parent_ptr = next($ptr_array)) {
                if (!empty($parent_ptr->tpl_vars)) {
                    $tpl_vars = array_merge($tpl_vars, $parent_ptr->tpl_vars);
                }
            }
            if (!empty(Brainy::$global_tpl_vars)) {
                $tpl_vars = array_merge(Brainy::$global_tpl_vars, $tpl_vars);
            }
            $_template->tpl_vars = $tpl_vars;
        }

        // dummy local smarty variable
        $_template->tpl_vars['smarty'] = new Variable();

        // must reset merge template date
        $_template->smarty->merged_templates_func = array();
        // get rendered template
        // checks if template exists
        if (!$_template->source->exists) {
            $parent_resource = '';
            if ($_template->parent instanceof Template) {
                $parent_resource = " in '{$_template->parent->template_resource}'";
            }
            throw new SmartyException("Unable to load template {$_template->source->type} '{$_template->source->name}'{$parent_resource}");
        }

        // read from cache or render
        if ($_template->source->uncompiled) {
            try {
                ob_start();
                $_template->source->renderUncompiled($_template);
            } catch (Exception $e) {
                ob_get_clean();
                throw $e;
            }
        } elseif ($_template->source->recompiled) {
            $_smarty_tpl = $_template;
            $code = $_template->compiler->compileTemplate($_template);
            try {
                ob_start();
                eval('?>' . $code);  // The closing PHP bit accounts for the opening PHP tag at the top of the compiled file
                unset($code);
            } catch (Exception $e) {
                ob_get_clean();
                throw $e;
            }
        } else {
            $_smarty_tpl = $_template;
            if (!$_template->compiled->exists || ($_template->smarty->force_compile && !$_template->compiled->isCompiled)) {
                $_template->compileTemplateSource();
                require $_template->compiled->filepath;
                $_template->compiled->loaded = true;
                $_template->compiled->isCompiled = true;
            }
            if (!$_template->compiled->loaded) {
                require $_template->compiled->filepath;
                if ($_template->mustCompile()) {
                    // recompile and load again
                    $_template->compileTemplateSource();
                    require $_template->compiled->filepath;
                    $_template->compiled->isCompiled = true;
                }
                $_template->compiled->loaded = true;
            } else {
                $_template->decodeProperties($_template->compiled->_properties, false);
            }
            try {
                ob_start();
                if (empty($_template->properties['unifunc']) || !is_callable($_template->properties['unifunc'])) {
                    throw new SmartyException("Invalid compiled template for '{$_template->template_resource}'");
                }
                array_unshift($_template->_capture_stack, array());
                //
                // render compiled template
                //
                call_user_func($_template->properties['unifunc'], $_template);
                // any unclosed {capture} tags ?
                if (isset($_template->_capture_stack[0][0])) {
                    throw new SmartyException("Not matching {capture} open/close in \"{$this->template_resource}\"");
                }
                array_shift($_template->_capture_stack);
            } catch (Exception $e) {
                ob_get_clean();
                // if (isset($code)) echo $code;
                throw $e;
            }
        }
        $output = ob_get_clean();

        if (!$_template->source->recompiled && empty($_template->properties['file_dependency'][$_template->source->uid])) {
            $_template->properties['file_dependency'][$_template->source->uid] = array($_template->source->filepath, $_template->source->timestamp, $_template->source->type);
        }
        if ($_template->parent instanceof Template) {
            $_template->parent->properties['file_dependency'] = array_merge($_template->parent->properties['file_dependency'], $_template->properties['file_dependency']);
            foreach ($_template->required_plugins as $code => $tmp1) {
                foreach ($tmp1 as $name => $tmp) {
                    foreach ($tmp as $type => $data) {
                        $_template->parent->required_plugins[$code][$name][$type] = $data;
                    }
                }
            }
        }

        if ($merge_tpl_vars) {
            // restore local variables
            $_template->tpl_vars = $save_tpl_vars;
        }

        return $output;
    }

    /**
     * Renders the template.
     *
     * This displays the contents of a template. To return the contents of a
     * template into a variable, use the fetch() method instead.
     *
     * As an optional second and third parameter, you can pass a cache ID and
     * compile ID.
     *
     * A fourth parameter can be passed which passes the parent scope that the
     * template should use.
     *
     * @param string $template   the resource handle of the template file or template object
     * @param mixed  $cache_id   no-op
     * @param mixed  $compile_id compile id to be used with this template
     * @param object $parent     next higher level of Brainy variables
     * @return void
     */
    public function display($template = null, $cache_id = null, $compile_id = null, $parent = null) {
        echo $this->fetch($template, null, $compile_id, $parent);
    }

    /**
     * preg_replace callback to convert camelcase getter/setter to underscore property names
     *
     * @param  string $match match string
     * @return string replacemant
     */
    private function replaceCamelcase($match) {
        return "_" . strtolower($match[1]);
    }

}
