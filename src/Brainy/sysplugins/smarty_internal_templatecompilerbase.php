<?php

/**
 * Smarty Internal Plugin Smarty Template Compiler Base
 *
 * This file contains the basic classes and methods for compiling Smarty templates with lexer/parser
 *
 * @package Brainy
 * @subpackage Compiler
 * @author Uwe Tews
 */

/**
 * Main abstract compiler class
 *
 * @package Brainy
 * @subpackage Compiler
 */
abstract class Smarty_Internal_TemplateCompilerBase
{

    /**
     * suppress generation of merged template code
     *
     * @var bool
     */
    public $suppressMergedTemplates = false;

    /**
     * compile tag objects
     *
     * @var array
     */
    public static $_tag_objects = array();

    /**
     * tag stack
     *
     * @var array
     */
    public $_tag_stack = array();

    /**
     * current template
     *
     * @var Smarty_Internal_Template
     */
    public $template = null;

    /**
     * merged templates
     *
     * @var array
     */
    public $merged_templates = array();

    /**
     * sources which must be compiled
     *
     * @var array
     */
    public $sources = array();

    /**
     * flag that we are inside {block}
     *
     * @var bool
     */
    public $inheritance = false;

    /**
     * flag when compiling inheritance child template
     *
     * @var bool
     */
    public $inheritance_child = false;

    /**
     * uid of templates called by {extends} for recursion check
     *
     * @var array
     */
    public $extends_uid = array();

    /**
     * source line offset for error messages
     *
     * @var int
     */
    public $trace_line_offset = 0;

    /**
     * trace uid
     *
     * @var string
     */
    public $trace_uid = '';

    /**
     * trace file path
     *
     * @var string
     */
    public $trace_filepath = '';
    /**
     * stack for tracing file and line of nested {block} tags
     *
     * @var array
     */
    public $trace_stack = array();

    /**
     * plugins loaded by default plugin handler
     *
     * @var array
     */
    public $default_handler_plugins = array();

    /**
     * saved preprocessed modifier list
     *
     * @var mixed
     */
    public $default_modifier_list = null;

    /**
     * suppress Smarty header code in compiled template
     * @var bool
     */
    public $suppressHeader = false;

    /**
     * suppress template property header code in compiled template
     * @var bool
     */
    public $suppressTemplatePropertyHeader = false;

    /**
     * suppress pre and post filter
     * @var bool
     */
    public $suppressFilter = false;

    /**
     * flag if compiled template file shall we written
     * @var bool
     */
    public $write_compiled_code = true;

    /**
     * flag if currently a template function is compiled
     * @var bool
     */
    public $compiles_template_function = false;

    /**
     * called subfuntions from template function
     * @var array
     */
    public $called_functions = array();

    /**
     * flags for used modifier plugins
     * @var array
     */
    public $modifier_plugins = array();

    /**
     * type of already compiled modifier
     * @var array
     */
    public $known_modifier_type = array();

    /**
     * @var boolean
     */
    public $has_code = false;

    /**
     * method to compile a Smarty template
     *
     * @param  mixed $_content template source
     * @return bool  true if compiling succeeded, false if it failed
     */
    abstract protected function doCompile($_content);

    /**
     * Method to compile a Smarty template
     *
     * @param  Smarty_Internal_Template $template template object to compile
     * @return bool             true if compiling succeeded, false if it failed
     */
    public function compileTemplate(Smarty_Internal_Template $template) {
        // save template object in compiler class
        $this->template = $template;
        $save_source = $this->template->source;
        // template header code
        $template_header = '';
        if (!$this->suppressHeader) {
            $template_header .= "<?php /* Brainy version " . Smarty::SMARTY_VERSION . ", created on " . strftime("%Y-%m-%d %H:%M:%S") . "\n";
            $template_header .= "         compiled from \"" . $this->template->source->filepath . "\" */\n";
        }

        if (empty($this->template->source->components)) {
            $this->sources = array($template->source);
        } else {
            // we have array of inheritance templates by extends: resource
            $this->sources = array_reverse($template->source->components);
        }
        $loop = 0;
        // the $this->sources array can get additional elements while compiling by the {extends} tag
        while ($this->template->source = array_shift($this->sources)) {
            $this->smarty->_current_file = $this->template->source->filepath;
            $no_sources = count($this->sources);
            if ($loop || $no_sources) {
                $this->template->properties['file_dependency'][$this->template->source->uid] = array($this->template->source->filepath, $this->template->source->timestamp, $this->template->source->type);
            }
            $loop++;
            $this->inheritance_child = (bool) $no_sources;
            do {
                $_compiled_code = '';
                // flag for aborting current and start recompile
                $this->abort_and_recompile = false;
                // get template source
                $_content = $this->template->source->content;
                if ($_content != '') {
                    // run prefilter if required
                    if ((isset($this->smarty->autoload_filters['pre']) || isset($this->smarty->registered_filters['pre'])) && !$this->suppressFilter) {
                        $_content = Smarty_Internal_Filter_Handler::runFilter('pre', $_content, $template);
                    }
                    // call compiler
                    $_compiled_code = $this->doCompile($_content);
                }
            } while ($this->abort_and_recompile);
        }

        // restore source
        $this->template->source = $save_source;
        unset($save_source);
        $this->smarty->_current_file = $this->template->source->filepath;
        // free memory
        unset($this->parser->root_buffer, $this->parser->current_buffer, $this->parser, $this->lex, $this->template);
        self::$_tag_objects = array();
        // return compiled code to template object
        $merged_code = '';
        if (!$this->suppressMergedTemplates && !empty($this->merged_templates)) {
            foreach ($this->merged_templates as $code) {
                $merged_code .= $code;
            }
        }
        // run postfilter if required on compiled template code
        if ((isset($this->smarty->autoload_filters['post']) || isset($this->smarty->registered_filters['post'])) && !$this->suppressFilter && $_compiled_code !== '') {
            $_compiled_code = Smarty_Internal_Filter_Handler::runFilter('post', $_compiled_code, $template);
        }
        if ($this->suppressTemplatePropertyHeader) {
            $code = $_compiled_code . $merged_code;
        } else {
            $code = $template_header . $template->createTemplateCodeFrame($_compiled_code) . $merged_code;
        }
        // unset content because template inheritance could have replace source with parent code
        unset ($template->source->content);

        return $code;
    }

    /**
     * Compile Tag
     *
     * This is a call back from the lexer/parser
     * It executes the required compile plugin for the Smarty tag
     *
     * @param  string $tag       tag name
     * @param  array  $args      array with tag attributes
     * @param  array  $parameter array with compilation parameter
     * @return string compiled   code
     */
    public function compileTag($tag, $args, $parameter = array(), $param2 = null, $param3 = null) {
        // $args contains the attributes parsed and compiled by the lexer/parser
        // assume that tag does compile into code, but creates no HTML output
        $this->has_code = true;
        $this->has_output = false;
        // log tag/attributes
        if (isset($this->smarty->get_used_tags) && $this->smarty->get_used_tags) {
            $this->template->used_tags[] = array($tag, $args);
        }
        // compile the smarty tag (required compile classes to compile the tag are autoloaded)
        $_output = $this->callTagCompiler($tag, $args, $parameter, $param2, $param3);

        if ($_output === false && isset($this->smarty->template_functions[$tag])) {
            // template defined by {template} tag
            $args['_attr']['name'] = "'" . $tag . "'";
            $_output = $this->callTagCompiler('call', $args, $parameter);
        }

        if ($_output !== false) {
            if ($_output !== true) {
                // did we get compiled code
                if ($this->has_code) {
                    // Does it create output?
                    if ($this->has_output) {
                        $_output .= "\n";
                    }
                    // return compiled code
                    return $_output;
                }
            }
            // tag did not produce compiled code
            return null;
        }
        // map_named attributes
        if (isset($args['_attr'])) {
            foreach ($args['_attr'] as $key => $attribute) {
                if (is_array($attribute)) {
                    $args = array_merge($args, $attribute);
                }
            }
        }
        // not an internal compiler tag
        if (strlen($tag) < 6 || substr($tag, -5) !== 'close') {

            if (isset($this->smarty->registered_plugins[Smarty::PLUGIN_COMPILER][$tag])) {
                $new_args = array();
                foreach ($args as $key => $mixed) {
                    if (is_array($mixed)) {
                        $new_args = array_merge($new_args, $mixed);
                    } else {
                        $new_args[$key] = $mixed;
                    }
                }
                $function = $this->smarty->registered_plugins[Smarty::PLUGIN_COMPILER][$tag][0];
                if (!is_array($function)) {
                    return $function($new_args, $this);
                } elseif (is_object($function[0])) {
                    return $this->smarty->registered_plugins[Smarty::PLUGIN_COMPILER][$tag][0][0]->$function[1]($new_args, $this);
                } else {
                    return call_user_func_array($function, array($new_args, $this));
                }
            }
            if (isset($this->smarty->registered_plugins[Smarty::PLUGIN_FUNCTION][$tag])) {
                return $this->callTagCompiler('private_registered_' . Smarty::PLUGIN_FUNCTION, $args, $parameter, $tag);
            }
            if (isset($this->smarty->registered_plugins[Smarty::PLUGIN_BLOCK][$tag])) {
                return $this->callTagCompiler('private_registered_' . Smarty::PLUGIN_BLOCK, $args, $parameter, $tag);
            }

            // check plugins from plugins folder
            foreach ($this->smarty->plugin_search_order as $plugin_type) {
                if ($plugin_type == Smarty::PLUGIN_COMPILER &&
                    $this->smarty->loadPlugin('smarty_compiler_' . $tag, true) &&
                    (!isset($this->smarty->security_policy) || $this->smarty->security_policy->isTrustedTag($tag, $this))) {

                    $plugin = 'smarty_compiler_' . $tag;
                    if (is_callable($plugin)) {
                        // convert arguments format for old compiler plugins
                        $new_args = array();
                        foreach ($args as $key => $mixed) {
                            if (is_array($mixed)) {
                                $new_args = array_merge($new_args, $mixed);
                            } else {
                                $new_args[$key] = $mixed;
                            }
                        }

                        return $plugin($new_args, $this->smarty);
                    }
                    if (class_exists($plugin, false)) {
                        $plugin_object = new $plugin;
                        if (method_exists($plugin_object, 'compile')) {
                            return $plugin_object->compile($args, $this);
                        }
                    }
                    throw new SmartyException("Plugin \"{$tag}\" not callable");

                }

                $function = $this->getPlugin($tag, $plugin_type);
                if ($function && (!isset($this->smarty->security_policy) || $this->smarty->security_policy->isTrustedTag($tag, $this))) {
                    return $this->callTagCompiler('private_' . $plugin_type . '_plugin', $args, $parameter, $tag, $function);
                }
            }
            if (is_callable($this->smarty->default_plugin_handler_func)) {
                $found = false;
                // look for already resolved tags
                foreach ($this->smarty->plugin_search_order as $plugin_type) {
                    if (isset($this->default_handler_plugins[$plugin_type][$tag]) ||
                            $this->getPluginFromDefaultHandler($tag, $plugin_type)) {
                        $found = true;
                        break;
                    }
                }
                if ($found) {
                    // if compiler function plugin call it now
                    if ($plugin_type == Smarty::PLUGIN_COMPILER) {
                        $new_args = array();
                        foreach ($args as $mixed) {
                            $new_args = array_merge($new_args, $mixed);
                        }
                        $function = $this->default_handler_plugins[$plugin_type][$tag][0];
                        if (!is_array($function)) {
                            return $function($new_args, $this);
                        } elseif (is_object($function[0])) {
                            return $this->default_handler_plugins[$plugin_type][$tag][0][0]->$function[1]($new_args, $this);
                        } else {
                            return call_user_func_array($function, array($new_args, $this));
                        }
                    } else {
                        return $this->callTagCompiler('private_registered_' . $plugin_type, $args, $parameter, $tag);
                    }
                }
            }
            $this->trigger_template_error("unknown compiler tag \"" . $tag . "\"", $this->lex->taglineno);

        }

        // compile closing tag of block function
        $base_tag = substr($tag, 0, -5);
        // registered block tag ?
        if (isset($this->smarty->registered_plugins[Smarty::PLUGIN_BLOCK][$base_tag]) || isset($this->default_handler_plugins[Smarty::PLUGIN_BLOCK][$base_tag])) {
            return $this->callTagCompiler('private_registered_block', $args, $parameter, $tag);
        }
        // block plugin?
        if ($function = $this->getPlugin($base_tag, Smarty::PLUGIN_BLOCK)) {
            return $this->callTagCompiler('private_block_plugin', $args, $parameter, $tag, $function);
        }
        // registered compiler plugin ?
        if (isset($this->smarty->registered_plugins[Smarty::PLUGIN_COMPILER][$tag])) {
            // if compiler function plugin call it now
            $args = array();
            $function = $this->smarty->registered_plugins[Smarty::PLUGIN_COMPILER][$tag][0];
            if (!is_array($function)) {
                return $function($args, $this);
            } elseif (is_object($function[0])) {
                return $this->smarty->registered_plugins[Smarty::PLUGIN_COMPILER][$tag][0][0]->$function[1]($args, $this);
            } else {
                return call_user_func_array($function, array($args, $this));
            }
        }
        if ($this->smarty->loadPlugin('smarty_compiler_' . $tag, true)) {
            $plugin = 'smarty_compiler_' . $tag;
            if (is_callable($plugin)) {
                return $plugin($args, $this->smarty);
            }
            if (class_exists($plugin, false)) {
                $plugin_object = new $plugin;
                if (method_exists($plugin_object, 'compile')) {
                    return $plugin_object->compile($args, $this);
                }
            }
            throw new SmartyException("Plugin \"{$tag}\" not callable");
        }
        $this->trigger_template_error("unknown tag \"" . $tag . "\"", $this->lex->taglineno);
    }

    /**
     * lazy loads internal compile plugin for tag and calls the compile method
     *
     * compile objects cached for reuse.
     * class name format:  Smarty_Internal_Compile_TagName
     * plugin filename format: Smarty_Internal_Tagname.php
     *
     * @param  string $tag    tag name
     * @param  array $args   list of tag attributes
     * @param  mixed $param1 optional parameter
     * @param  mixed $param2 optional parameter
     * @param  mixed $param3 optional parameter
     * @return string compiled code
     */
    public function callTagCompiler($tag, $args, $param1 = null, $param2 = null, $param3 = null) {
        // re-use object if already exists
        if (isset(self::$_tag_objects[$tag])) {
            // compile this tag
            return self::$_tag_objects[$tag]->compile($args, $this, $param1, $param2, $param3);
        }
        // lazy load internal compiler plugin
        $class_name = 'Smarty_Internal_Compile_' . $tag;
        // echo $class_name . "\n";
        if ($this->smarty->loadPlugin($class_name, true)) {
            // check if tag allowed by security
            if (!isset($this->smarty->security_policy) || $this->smarty->security_policy->isTrustedTag($tag, $this)) {
                // use plugin if found
                self::$_tag_objects[$tag] = new $class_name;
                self::$_tag_objects[$tag]->template = $this->template;
                // compile this tag
                return self::$_tag_objects[$tag]->compile($args, $this, $param1, $param2, $param3);
            }
        }
        // no internal compile plugin for this tag
        return false;
    }

    /**
     * Check for plugins and return function name
     *
     * @param  string $pugin_name  name of plugin or function
     * @param  string $plugin_type type of plugin
     * @return string call name of function
     */
    public function getPlugin($plugin_name, $plugin_type) {
        $function = null;
        if (isset($this->template->required_plugins['compiled'][$plugin_name][$plugin_type])) {
            $function = $this->template->required_plugins['compiled'][$plugin_name][$plugin_type]['function'];
        }
        if (isset($function)) {
            if ($plugin_type == 'modifier') {
                $this->modifier_plugins[$plugin_name] = true;
            }

            return $function;
        }
        // loop through plugin dirs and find the plugin
        $function = 'smarty_' . $plugin_type . '_' . $plugin_name;
        $file = $this->smarty->loadPlugin($function, false);
        if ($file === true) {
            return $function;
        }

        if (is_string($file)) {
            $this->template->required_plugins['compiled'][$plugin_name][$plugin_type]['file'] = $file;
            $this->template->required_plugins['compiled'][$plugin_name][$plugin_type]['function'] = $function;
            if ($plugin_type == 'modifier') {
                $this->modifier_plugins[$plugin_name] = true;
            }

            return $function;
        }
        if (is_callable($function)) {
            // plugin function is defined in the script
            return $function;
        }

        return false;
    }

    /**
     * Check for plugins by default plugin handler
     *
     * @param  string $tag         name of tag
     * @param  string $plugin_type type of plugin
     * @return boolean true if found
     */
    public function getPluginFromDefaultHandler($tag, $plugin_type) {
        $callback = null;
        $script = null;
        $cacheable = true;
        $result = call_user_func_array(
            $this->smarty->default_plugin_handler_func, array($tag, $plugin_type, $this->template, &$callback, &$script, &$cacheable)
        );
        if ($result) {
            if ($script !== null) {
                if (is_file($script)) {
                    $this->template->required_plugins['compiled'][$tag][$plugin_type]['file'] = $script;
                    $this->template->required_plugins['compiled'][$tag][$plugin_type]['function'] = $callback;
                    include_once $script;
                } else {
                    $this->trigger_template_error("Default plugin handler: Returned script file \"{$script}\" for \"{$tag}\" not found");
                }
            }
            if (!is_string($callback) && !(is_array($callback) && is_string($callback[0]) && is_string($callback[1]))) {
                $this->trigger_template_error("Default plugin handler: Returned callback for \"{$tag}\" must be a static function name or array of class and function name");
            }
            if (is_callable($callback)) {
                $this->default_handler_plugins[$plugin_type][$tag] = array($callback, true, array());

                return true;
            } else {
                $this->trigger_template_error("Default plugin handler: Returned callback for \"{$tag}\" not callable");
            }
        }

        return false;
    }

    /**
     *  push current file and line offset on stack for tracing {block} source lines
     *
     * @param string $file new filename
     * @param string $uid uid of file
     * @param string $debug false debug end_compile shall not be called
     * @param int $line line offset to source
     */
    public function pushTrace($file, $uid, $line, $debug = true) {
        array_push($this->trace_stack, array($this->smarty->_current_file, $this->trace_filepath, $this->trace_uid, $this->trace_line_offset));
        $this->trace_filepath = $this->smarty->_current_file = $file;
        $this->trace_uid = $uid;
        $this->trace_line_offset = $line;
    }

    /**
     *  restore file and line offset
     *
     */
    public function popTrace() {
        $r = array_pop($this->trace_stack);
        $this->smarty->_current_file = $r[0];
        $this->trace_filepath = $r[1];
        $this->trace_uid = $r[2];
        $this->trace_line_offset = $r[3];
    }

    /**
     * display compiler error messages without dying
     *
     * If parameter $args is empty it is a parser detected syntax error.
     * In this case the parser is called to obtain information about expected tokens.
     *
     * If parameter $args contains a string this is used as error message
     *
     * @param  string $args individual error message or null
     * @param  string $line line-number
     * @param  string|void $exception_class The name of the exception class to raise
     * @throws SmartyCompilerException when an unexpected token is found
     */
    public function trigger_template_error($args = null, $line = null, $exception_class = 'SmartyCompilerException') {
        // get template source line which has error
        if (!isset($line)) {
            $line = $this->lex->line;
        }
        $match = preg_split("/\n/", $this->lex->data);
        $error_text = 'Syntax error in template "' . ($this->template->source->filepath) . '" on line ' . ($line + $this->trace_line_offset)  . ' "' . trim(preg_replace('![\t\r\n]+!', ' ', $match[$line - 1])) . '" ';
        if (isset($args)) {
            // individual error message
            $error_text .= $args;
        } else {
            // expected token from parser
            $error_text .= ' - Unexpected "' . $this->lex->value . '"';
            if (count($this->parser->yy_get_expected_tokens($this->parser->yymajor)) <= 4) {
                foreach ($this->parser->yy_get_expected_tokens($this->parser->yymajor) as $token) {
                    $exp_token = $this->parser->yyTokenName[$token];
                    if (isset($this->lex->smarty_token_names[$exp_token])) {
                        // token type from lexer
                        $expect[] = '"' . $this->lex->smarty_token_names[$exp_token] . '"';
                    } else {
                        // otherwise internal token name
                        $expect[] = $this->parser->yyTokenName[$token];
                    }
                }
                $error_text .= ', expected one of: ' . implode(' , ', $expect);
            }
        }
        $e = new $exception_class($error_text);
        $e->line = $line;
        $e->source = trim(preg_replace('![\t\r\n]+!', ' ', $match[$line - 1]));
        $e->desc = $args;
        $e->template = $this->template->source->filepath;
        throw $e;
    }

    /**
     * Show an error related to Smarty::$enforce_expression_modifiers
     *
     * @see Smarty::$enforce_expression_modifiers
     * @return void
     * @throws SmartyCompilerException
     */
    public function trigger_expression_modifiers_error() {
        $this->trigger_template_error(
            'Modifier Enforcement: All expressions must be suffixed with one of the following modifiers: ' .
            implode(',', Smarty::$enforce_expression_modifiers),
            null,
            'BrainyModifierEnforcementException'
        );
    }

    /**
     * Show an error related to Smarty::$enforce_expression_modifiers
     *
     * @see Smarty::$enforce_expression_modifiers
     * @param bool|void $static When true, the expression is static.
     * @return void
     */
    public function assert_no_enforced_modifiers($static = false) {
        if (!empty(Smarty::$enforce_expression_modifiers)) {
            if ($static && !Smarty::$enforce_modifiers_on_static_expressions) {
                return;
            }
            $this->trigger_expression_modifiers_error();
        }
    }

    /**
     * Accepts a modifier list. If the last modifier is not acceptable for the
     * modifier enforcement, an error will be thrown.
     *
     * @see Smarty::$enforce_expression_modifiers
     * @param string $modifier_list
     * @param bool|void $static When true, the expression is static.
     * @return void
     */
    public function assert_expected_modifier($modifier_list, $static = false) {
        if (empty(Smarty::$enforce_expression_modifiers)) {
            return;
        }
        $last_modifier = end($modifier_list)[0];
        reset($modifier_list);

        // Test to see whether the modifier list is static.
        foreach ($modifier_list as $modifier) {
            // Ignore modifiers with no attributes.
            if (count($modifier) === 1) {
                continue;
            }
            $modifier_attributes = array_slice($modifier, 1);
            foreach ($modifier_attributes as $attr) {
                if (!($attr instanceof BrainyStaticWrapper)) {
                    $static = false;
                }
            }
        }

        // Ignore purely static values.
        if ($static && !Smarty::$enforce_modifiers_on_static_expressions) {
            return;
        }
        // Throw an error if the final modifier is not acceptable.
        if (!in_array($last_modifier, Smarty::$enforce_expression_modifiers)) {
            $this->trigger_expression_modifiers_error();
        }
    }

    /**
     * @param string $reason
     * @param Smarty_Internal_Template|null|void $template
     * @return void
     * @throws BrainyStrictModeException
     */
    public function assert_is_not_strict($reason, $template = null)
    {
        if (Smarty::$strict_mode || $template && $template->isStrictMode()) {
            $this->trigger_template_error('Strict Mode: ' . $reason, null, 'BrainyStrictModeException');
        }
    }

}
