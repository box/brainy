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
    public function __construct($brainyInstance, $useRootScope = false)
    {
        $this->smarty = &$brainyInstance;
        if ($useRootScope) {
            $this->tpl_vars = $brainyInstance->tpl_vars;
        } else {
            $this->cloneDataFrom($brainyInstance);
        }
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
    public function fetch($template = null, $cache_id = null, $compile_id = null) {
        ob_start();
        try {
            $this->display($template, null, $compile_id);
        } catch (Exception $e) {
            ob_get_clean();
            throw $e;
        }
        return ob_get_clean();
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
     * @param string|null|void $template   the resource handle of the template file or template object
     * @param mixed|null|void  $cache_id   no-op
     * @param string|null|void  $compile_id compile id to be used with this template
     * @return void
     */
    public function display($template = null, $cache_id = null, $compile_id = null) {
        if ($template === null && $this instanceof Template) {
            $template = $this;
        }
        // create template object if necessary
        if (!($template instanceof Template)) {
            $template = $this->smarty->createTemplate($template, $cache_id, $compile_id, $this);
        }

        // dummy local smarty variable
        $template->tpl_vars['smarty'] = new Variable(array());

        // must reset merge template date
        $template->smarty->mergedtemplates_func = array();
        // get rendered template
        // checks if template exists
        if (!$template->source->exists) {
            $parent_resource = '';
            if ($template->parent instanceof Template) {
                $parent_resource = " in '{$template->parent->template_resource}'";
            }
            throw new SmartyException("Unable to load template {$template->source->type} '{$template->source->name}'{$parent_resource}");
        }

        // read from cache or render
        if ($template->source->recompiled) { // recompiled === 'eval'
            $_smarty_tpl = $template;
            $code = $template->compiler->compileTemplate($template);
            eval('?>' . $code);  // The closing PHP bit accounts for the opening PHP tag at the top of the compiled file
            unset($code);
        } else {
            $_smarty_tpl = $template;
            if (!$template->compiled->exists || ($template->smarty->force_compile && !$template->compiled->isCompiled)) {
                $template->compileTemplateSource();
            }
            if (!$template->compiled->loaded) {
                $template->compiled->load($template);
            } else {
                $template->decodeProperties($template->compiled->_properties, false);
            }
            if (empty($template->properties['unifunc']) || !is_callable($template->properties['unifunc'])) {
                throw new SmartyException("Invalid compiled template for '{$template->template_resource}'");
            }

            // render compiled template
            call_user_func($template->properties['unifunc'], $template);
        }

        if (!$template->source->recompiled && empty($template->properties['file_dependency'][$template->source->uid])) {
            $template->properties['file_dependency'][$template->source->uid] = array($template->source->filepath, $template->source->timestamp, $template->source->type);
        }
        if ($template->parent instanceof Template) {
            $template->parent->properties['file_dependency'] = array_merge($template->parent->properties['file_dependency'], $template->properties['file_dependency']);
            foreach ($template->required_plugins as $code => $tmp1) {
                foreach ($tmp1 as $name => $tmp) {
                    foreach ($tmp as $type => $data) {
                        $template->parent->required_plugins[$code][$name][$type] = $data;
                    }
                }
            }
        }
    }


    /**
     * Assigns $value to the variale $var.
     *
     * @param  string $var the template variable name
     * @param  mixed $value the value to assign
     * @param  int $scope the scope to associate with the Smarty_Variable
     * @see TemplateData::assignSingleVar()
     * @return void
     */
    public function setVariable($var, $value, $scope = -1)
    {
        // Pass-through
        $this->assignSingleVar($var, $value, $scope);
    }

}
