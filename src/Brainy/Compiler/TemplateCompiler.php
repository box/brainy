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

namespace Box\Brainy\Compiler;

use \Box\Brainy\Brainy;
use \Box\Brainy\Exceptions\SmartyCompilerException;
use \Box\Brainy\Templates\Template;


class TemplateCompiler
{

    /**
     * suppress generation of merged template code
     *
     * @var bool
     */
    public $suppressMergedTemplates = false;

    /**
     * tag stack
     *
     * @var array
     */
    public $_tag_stack = array();

    /**
     * current template
     *
     * @var Template
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
     * type of already compiled modifier
     * @var array
     */
    public $known_modifier_type = array();

    /**
     * @var boolean
     */
    public $has_code = false;


    /**
     * Lexer object
     *
     * @var object
     */
    public $lex;

    /**
     * Parser object
     *
     * @var object
     */
    public $parser;

    /**
     * Smarty object
     *
     * @var object
     */
    public $smarty;

    /**
     * array of vars which can be compiled in local scope
     *
     * @var array
     */
    public $local_var = array();

    /**
     * @param \Box\Brainy\Brainy $smarty       global instance
     */
    public function __construct(\Box\Brainy\Brainy $smarty) {
        $this->smarty = $smarty;
    }

    /**
     * method to compile a Smarty template
     *
     * @param  mixed $_content template source
     * @return bool  true if compiling succeeded, false if it failed
     */
    protected function doCompile($_content) {
        // init the lexer/parser to compile the template
        $this->lex = new Lexer($_content, $this);
        $this->parser = new Parser($this->lex, $this);
        if ($this->inheritance_child) {
            // start state on child templates
            $this->lex->yypushstate(Lexer::CHILDBODY);
        }

        // get tokens from lexer and parse them
        while ($this->lex->yylex()) {
            $this->parser->doParse($this->lex->token, $this->lex->value);
        }

        // finish parsing process
        $this->parser->doParse(0, 0);

        // check for unclosed tags
        if (count($this->_tag_stack)) {
            // get stacked info
            list($openTag, $_data) = array_pop($this->_tag_stack);
            $this->trigger_template_error("unclosed {$this->smarty->left_delimiter}" . $openTag . "{$this->smarty->right_delimiter} tag");
        }

        return $this->parser->retvalue;
    }

    /**
     * Method to compile a Smarty template
     *
     * @param  Template $template template object to compile
     * @return bool             true if compiling succeeded, false if it failed
     */
    public function compileTemplate(Template $template) {
        // save template object in compiler class
        $this->template = $template;
        $save_source = $this->template->source;
        // template header code
        $template_header = '';
        if (!$this->suppressHeader) {
            $template_header .= "<?php\n/* Brainy version " . Brainy::SMARTY_VERSION . ", created on " . strftime("%Y-%m-%d %H:%M:%S");
            $template_header .= " compiled from \"" . $this->template->source->filepath . "\" */\n";
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
            $no_sources = count($this->sources);
            if ($loop || $no_sources) {
                $this->template->properties['file_dependency'][$this->template->source->uid] = array($this->template->source->filepath, $this->template->source->timestamp, $this->template->source->type);
            }
            $loop++;
            $this->inheritance_child = (bool) $no_sources;
            $_compiled_code = '';
            // get template source
            if ($this->template->source->content) {
                $_compiled_code = $this->doCompile($this->template->source->content);
            }
        }

        // restore source
        $this->template->source = $save_source;
        unset($save_source);
        // free memory
        unset($this->parser->root_buffer, $this->parser->current_buffer, $this->parser, $this->lex, $this->template);
        // return compiled code to template object
        $merged_code = '';
        if (!$this->suppressMergedTemplates && !empty($this->merged_templates)) {
            foreach ($this->merged_templates as $code) {
                $merged_code .= $code;
            }
        }

        if ($this->suppressTemplatePropertyHeader) {
            $code = $_compiled_code . $merged_code;
        } else {
            $code = $template_header . $template->createTemplateCodeFrame($_compiled_code) . $merged_code;
        }
        // unset content because template inheritance could have replace source with parent code
        unset($template->source->content);

        return $code;
    }

    /**
     * @param  string $tag       tag name
     * @param  array  $args      array with tag attributes
     * @param  array  $parameter array with compilation parameter
     * @return string compiled   code
     */
    public function compileTag($tag, $args, $parameter = array()) {
        // $args contains the attributes parsed and compiled by the lexer/parser
        // assume that tag does compile into code, but creates no HTML output
        $this->has_code = true;
        $this->has_output = false;

        if (isset($this->smarty->template_functions[$tag])) {
            $_output = false;
            // template defined by {template} tag
            $args['_attr']['name'] = "'" . $tag . "'";
            $_output = $this->callTagCompiler('call', $args, $parameter);
            if ($_output !== false) {
                if ($_output === true) {
                    // tag did not produce compiled code
                    return null;
                }
                // Does it create output?
                if ($this->has_output) {
                    $_output .= "\n";
                }
                // return compiled code
                return $_output;
            }
        }

        if (isset($this->smarty->security_policy) &&
            !$this->smarty->security_policy->isTrustedTag($tag, $this)) {
            $this->trigger_template_error("Use of disallowed tag: \"{$tag}\"", $this->lex->taglineno);
            return; // unreachable
        }

        // map_named attributes
        if (isset($args['_attr'])) {
            foreach ($args['_attr'] as $key => $attribute) {
                if (!is_array($attribute)) {
                    continue;
                }
                $args = array_merge($args, $attribute);
            }
        }

        if (\Box\Brainy\Runtime\PluginLoader::loadPlugin(Brainy::PLUGIN_FUNCTION, $tag, $this->smarty)) {
            return (
                'echo ' .
                \Box\Brainy\Runtime\PluginLoader::getPluginFunction(Brainy::PLUGIN_FUNCTION, $tag) .
                '(' . $this->formatStaticArgs($args) . ', $_smarty_tpl)' .
                ";\n"
            );
        }


        if (strlen($tag) < 6 || substr($tag, -5) !== 'close') {

            if (isset($this->smarty->registered_plugins[Brainy::PLUGIN_COMPILER][$tag])) {
                $function = $this->smarty->registered_plugins[Brainy::PLUGIN_COMPILER][$tag];
                return call_user_func($function, $this->formatPluginArgs($args), $this);
            }

            // check plugins from plugins folder
            if (\Box\Brainy\Runtime\PluginLoader::loadPlugin(Brainy::PLUGIN_COMPILER, $tag, $this->smarty)) {
                $plugin = \Box\Brainy\Runtime\PluginLoader::getPluginFunction(Brainy::PLUGIN_COMPILER, $tag);
                if (!is_callable($plugin)) {
                    throw new SmartyException("Plugin \"{$tag}\" not callable");
                }
                return call_user_func($plugin, $this->formatPluginArgs($args), $this->smarty);
            }

            $this->trigger_template_error("unknown compiler tag \"{$tag}\"", $this->lex->taglineno);

        }

        // registered compiler plugin ?
        if (isset($this->smarty->registered_plugins[Brainy::PLUGIN_COMPILER][$tag])) {
            $function = $this->smarty->registered_plugins[Brainy::PLUGIN_COMPILER][$tag];
            return call_user_func($function, array(), $this);
        }
        if (\Box\Brainy\Runtime\PluginLoader::loadPlugin(Brainy::PLUGIN_COMPILER, $tag, $this->smarty)) {
            $plugin = \Box\Brainy\Runtime\PluginLoader::getPluginFunction(Brainy::PLUGIN_COMPILER, $tag);
            if (!is_callable($plugin)) {
                throw new SmartyException("Plugin \"{$tag}\" not callable");
            }
            return call_user_func($plugin, $args, $this->smarty);
        }

        $this->trigger_template_error("unknown tag \"$tag\"", $this->lex->taglineno);
    }

    /**
     * Formats args for old compiler plugins
     * @param  array $args
     * @return array
     */
    private function formatStaticArgs($args)
    {
        $params = array();
        foreach ($args as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $key => $value) {
                    $params[] = "'$key' => $value";
                }
            } elseif (is_int($key)) {
                $params[] = "$key => $value";
            } else {
                $params[] = "'$key' => $value";
            }
        }
        return 'array(' . implode(', ', $params) . ')';
    }

    /**
     * Formats args for old compiler plugins
     * @param  array $args
     * @return array
     */
    private function formatPluginArgs($args)
    {
        $new_args = array();
        foreach ($args as $key => $mixed) {
            if (is_array($mixed)) {
                $new_args = array_merge($new_args, $mixed);
            } else {
                $new_args[$key] = $mixed;
            }
        }
        return $new_args;
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
    public function trigger_template_error($args = null, $line = null, $exception_class = '\Box\Brainy\Exceptions\SmartyCompilerException') {
        // get template source line which has error
        if (!isset($line)) {
            $line = $this->lex->line;
        }
        $match = preg_split("/\n/", $this->lex->data);
        $error_text = 'Syntax error in template "' . ($this->template->source->filepath) . '" on line ' . ($line + $this->lex->line)  . ' "' . trim(preg_replace('![\t\r\n]+!', ' ', $match[$line - 1])) . '" ';
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
     * Show an error related to Brainy::$enforce_expression_modifiers
     *
     * @see Brainy::$enforce_expression_modifiers
     * @return void
     * @throws SmartyCompilerException
     */
    public function trigger_expression_modifiers_error() {
        $this->trigger_template_error(
            'Modifier Enforcement: All expressions must be suffixed with one of the following modifiers: ' .
            implode(',', Brainy::$enforce_expression_modifiers),
            null,
            '\Box\Brainy\Exceptions\BrainyModifierEnforcementException'
        );
    }

    /**
     * Show an error related to Brainy::$enforce_expression_modifiers
     *
     * @see Brainy::$enforce_expression_modifiers
     * @param bool|void $static When true, the expression is static.
     * @return void
     */
    public function assert_no_enforced_modifiers($static = false) {
        if (!empty(Brainy::$enforce_expression_modifiers)) {
            if ($static && !Brainy::$enforce_modifiers_on_static_expressions) {
                return;
            }
            $this->trigger_expression_modifiers_error();
        }
    }

    /**
     * Accepts a modifier list. If the last modifier is not acceptable for the
     * modifier enforcement, an error will be thrown.
     *
     * @see Brainy::$enforce_expression_modifiers
     * @param string $modifier_list
     * @param bool|void $static When true, the expression is static.
     * @return void
     */
    public function assert_expected_modifier($modifier_list, $static = false) {
        if (empty(Brainy::$enforce_expression_modifiers)) {
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
                if (!($attr instanceof StaticWrapper)) {
                    $static = false;
                }
            }
        }

        // Ignore purely static values.
        if ($static && !Brainy::$enforce_modifiers_on_static_expressions) {
            return;
        }
        // Throw an error if the final modifier is not acceptable.
        if (!in_array($last_modifier, Brainy::$enforce_expression_modifiers)) {
            $this->trigger_expression_modifiers_error();
        }
    }

    /**
     * @param string $reason
     * @param Template|null|void $template
     * @return void
     * @throws BrainyStrictModeException
     */
    public function assert_is_not_strict($reason, $template = null)
    {
        if (Brainy::$strict_mode || $template && $template->isStrictMode()) {
            $this->trigger_template_error('Strict Mode: ' . $reason, null, '\Box\Brainy\Exceptions\BrainyStrictModeException');
        }
        if ($this->parser && $this->parser->isStrictMode()) {
            $this->trigger_template_error('Strict Mode: ' . $reason, null, '\Box\Brainy\Exceptions\BrainyStrictModeException');
        }
    }

}
