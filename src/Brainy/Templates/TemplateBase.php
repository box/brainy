<?php
/**
 * @package Brainy
 * @author Matt Basta
 * @author Uwe Tews
 */

namespace Box\Brainy\Templates;

use \Box\Brainy\Brainy;
use \Box\Brainy\Exceptions\BrainyStrictModeException;
use \Box\Brainy\Exceptions\SmartyException;


class TemplateBase
{
    use TemplateData;


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
     * @param bool|void $useRootScope Whether to clone data from the root scope
     */
    public function __construct(\Box\Brainy\Brainy $brainyInstance, $useRootScope = false)
    {
        $this->smarty = &$brainyInstance;
        if ($useRootScope || Brainy::$default_assign_scope === Brainy::SCOPE_ROOT) {
            $this->tpl_vars = &$brainyInstance->tpl_vars;
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
     * @return string rendered template output
     */
    public function fetch($template = null, $cache_id = null, $compile_id = null)
    {
        ob_start();
        try {
            $this->display($template, null, $compile_id);
        } catch (Exception $e) {
            ob_end_clean();
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
    public function display($template = null, $cache_id = null, $compile_id = null)
    {
        $this->setUpTemplateData();

        if ($template === null && $this instanceof Template) {
            $template = $this;
        }
        // create template object if necessary
        if (!($template instanceof Template)) {
            $template = $this->smarty->createTemplate($template, $cache_id, $compile_id, $this);
        }

        // dummy local smarty variable
        if (!isset($template->tpl_vars['smarty'])) {
            $template->tpl_vars['smarty'] = new Variable(array('blocks' => array()));
        }

        if (!empty(Brainy::$global_tpl_vars)) {
            foreach (Brainy::$global_tpl_vars as $key => $value) {
                $template->tpl_vars[$key] = &$value;
            }
        }

        // get rendered template
        // checks if template exists
        if (!$template->source->exists) {
            $parent_resource = '';
            if ($template->parent instanceof Template) {
                $parent_resource = " in '{$template->parent->template_resource}'";
            }
            throw new SmartyException("Unable to load template {$template->source->type} '{$template->source->name}'{$parent_resource}");
        }

        $_smarty_tpl = $template;
        // read from cache or render
        if ($template->source->recompiled) { // recompiled === 'eval'
            $code = $template->compileTemplateSource();
            eval('?>' . $code);  // The closing PHP bit accounts for the opening PHP tag at the top of the compiled file
            unset($code);
        } else {
            if (!$template->compiled->exists || ($template->smarty->force_compile && !$template->compiled->isCompiled)) {
                $template->compileTemplateSource();
            }
            if (!$template->compiled->loaded) {
                $template->compiled->load($template);
            } else {
                $template->decodeProperties($template->compiled->_properties, false);
            }
            if (empty($template->properties['unifunc']) || !is_callable($template->properties['unifunc'])) {
                throw new SmartyException("Invalid compiled template for '{$template->template_resource}': no unifunc found");
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

    /**
     * Hook to allow subclasses to initialize their data structures.
     * @return void
     */
    protected function setUpTemplateData()
    {}

}
