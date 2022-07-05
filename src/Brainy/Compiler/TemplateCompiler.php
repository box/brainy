<?php

/**
 * Smarty Internal Plugin Smarty Template Compiler Base
 *
 * This file contains the basic classes and methods for compiling Smarty templates with lexer/parser
 *
 * @package    Brainy
 * @subpackage Compiler
 * @author     Uwe Tews
 */

namespace Box\Brainy\Compiler;

use \Box\Brainy\Brainy;
use \Box\Brainy\Exceptions\SmartyCompilerException;
use \Box\Brainy\Templates\Template;

class TemplateCompiler
{

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
     * @var boolean
     */
    public $has_code = false;


    /**
     * Lexer object
     *
     * @var \Box\Brainy\Compiler\Lexer
     */
    public $lex;

    /**
     * Parser object
     *
     * @var \Box\Brainy\Compiler\Parser
     */
    public $parser;

    /**
     * Smarty object
     *
     * @var \Box\Brainy\Brainy
     */
    public $smarty;


    /**
     * @see getUniqueVarName()
     * @var integer
     */
    private $idInc = 0;


    /**
     * @param \Box\Brainy\Brainy $smarty global instance
     */
    public function __construct(\Box\Brainy\Brainy $smarty)
    {
        $this->smarty = $smarty;
    }

    /**
     * Returns a unique variable name, sans dollar sign.
     * @return string Unique variable name
     */
    public function getUniqueVarName()
    {
        return 'tv' . $this->idInc++;
    }

    /**
     * method to compile a Smarty template
     *
     * @param  mixed $_content template source
     * @return bool  true if compiling succeeded, false if it failed
     */
    protected function doCompile($_content)
    {
        // init the lexer/parser to compile the template
        $this->lex = new Lexer($_content, $this);
        $this->parser = new Parser($this->lex, $this);

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

        $output = $this->parser->retvalue;
        unset($this->lex, $this->parser);
        return $output;
    }

    /**
     * Method to compile a Smarty template
     *
     * @param  Template $template template object to compile
     * @return bool             true if compiling succeeded, false if it failed
     */
    public function compileTemplate(Template $template)
    {
        // save template object in compiler class
        $this->template = $template;
        // template header code
        $template_header = '';
        if (!$this->suppressHeader) {
            $template_header .= "<?php\n";
        }

        $this->template->properties['file_dependency'][$this->template->source->uid] = array($this->template->source->filepath, $this->template->source->timestamp, $this->template->source->type);

        $compiledCode = $this->doCompile($this->template->source->getContent());

        // free memory
        unset($this->template);

        $code = $template_header . $template->createTemplateCodeFrame($compiledCode);

        return $code;
    }

    /**
     * @param  string $tag       tag name
     * @param  array  $args      array with tag attributes
     * @param  array  $parameter array with compilation parameter
     * @return string compiled   code
     */
    public function compileTag($tag, $args, $parameter = array())
    {
        // $args contains the attributes parsed and compiled by the lexer/parser
        // assume that tag does compile into code, but creates no HTML output
        $this->has_code = true;


        if (isset($this->smarty->security_policy)
            && !$this->smarty->security_policy->isTrustedTag($tag, $this)
        ) {
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

        if (isset($this->smarty->registered_plugins[Brainy::PLUGIN_FUNCTION][$tag])) {
            $function = $this->smarty->registered_plugins[Brainy::PLUGIN_FUNCTION][$tag];
            return (
                'echo ' . $function .
                '(' . $this->formatStaticArgs($args, false) . ', $_smarty_tpl)' .
                ";\n"
            );
        }

        if (\Box\Brainy\Runtime\PluginLoader::loadPlugin(Brainy::PLUGIN_FUNCTION, $tag, $this->smarty)) {
            return (
                '\Box\Brainy\Runtime\PluginLoader::loadPlugin(\Box\Brainy\Brainy::PLUGIN_FUNCTION, ' . var_export($tag, true) . ", \$_smarty_tpl->smarty);\n" .
                'echo ' .
                \Box\Brainy\Runtime\PluginLoader::getPluginFunction(Brainy::PLUGIN_FUNCTION, $tag) .
                '(' . $this->formatStaticArgs($args, false) . ', $_smarty_tpl)' .
                ";\n"
            );
        }


        if (isset($this->smarty->registered_plugins[Brainy::PLUGIN_COMPILER][$tag])) {
            $function = $this->smarty->registered_plugins[Brainy::PLUGIN_COMPILER][$tag];
            return call_user_func($function, $this->formatPluginArgs($args), $this);
        }

        if (\Box\Brainy\Runtime\PluginLoader::loadPlugin(Brainy::PLUGIN_COMPILER, $tag, $this->smarty)) {
            $plugin = \Box\Brainy\Runtime\PluginLoader::getPluginFunction(Brainy::PLUGIN_COMPILER, $tag);
            if (!is_callable($plugin)) {
                throw new \Box\Brainy\Exceptions\SmartyException("Plugin \"{$tag}\" not callable");
            }
            return call_user_func($plugin, $this->formatPluginArgs($args), $this);
        }

        // Try treating it as a call
        array_unshift($args, array('name' => var_export($tag, true)));
        return Constructs\ConstructCall::compileOpen($this, $args);
    }

    /**
     * Formats args for old compiler plugins
     * @param  array     $args
     * @param  bool|void $export Whether to use var_export or to manually construct an array
     * @return string
     */
    private function formatStaticArgs($args, $export = true)
    {
        $args = $this->formatPluginArgs($args);
        if ($export) {
            return var_export($args, true);
        }

        $output = 'array(';
        $first = true;
        foreach ($args as $k => $v) {
            if ($first) {
                $first = false;
            } else {
                $output .= ', ';
            }
            $output .= var_export($k, true);
            $output .= ' => ';
            $output .= $v;
        }
        $output .= ')';
        return $output;
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
                foreach ($mixed as $k => $val) {
                    if ($val instanceof Wrappers\StaticWrapper) {
                        $new_args[$k] = (string) $val;
                    } else {
                        $new_args[$k] = $val;
                    }
                }
            } elseif ($mixed instanceof Wrappers\StaticWrapper) {
                $new_args[$key] = (string) $mixed;
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
     * @param  string      $args            individual error message or null
     * @param  int         $line            line-number
     * @param  string|void $exception_class The name of the exception class to raise
     * @throws SmartyCompilerException when an unexpected token is found
     */
    public function trigger_template_error($args = null, $line = null, $exception_class = '\Box\Brainy\Exceptions\SmartyCompilerException')
    {
        // get template source line which has error
        if (!isset($line)) {
            $line = $this->lex->line;
        } else {
            $line = intval($line);
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
     * @see    Brainy::$enforce_expression_modifiers
     * @return void
     * @throws SmartyCompilerException
     */
    public function triggerExpressionModifiersError()
    {
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
     * @see    Brainy::$enforce_expression_modifiers
     * @param  bool|void $static When true, the expression is static.
     * @return void
     */
    public function assertNoEnforcedModifiers($static = false)
    {
        if (!empty(Brainy::$enforce_expression_modifiers)) {
            if ($static) {
                return;
            }
            $this->triggerExpressionModifiersError();
        }
    }

    /**
     * @param string             $reason
     * @param Template|null|void $template
     * @return void
     * @throws BrainyStrictModeException
     */
    public function assertIsNotStrict($reason, $template = null)
    {
        if (Brainy::$strict_mode || $template && $template->isStrictMode()) {
            $this->trigger_template_error('Strict Mode: ' . $reason, null, '\Box\Brainy\Exceptions\BrainyStrictModeException');
        }
        if ($this->parser && $this->parser->isStrictMode()) {
            $this->trigger_template_error('Strict Mode: ' . $reason, null, '\Box\Brainy\Exceptions\BrainyStrictModeException');
        }
    }

    /**
     * Asserts that a tag is open
     * @param  string $name Name of the tag
     * @return mixed Data associated with the tag
     */
    public function assertIsInTag($name)
    {
        foreach (array_reverse($this->_tag_stack) as $tag) {
            list($tagName, $data) = $tag;
            if ($tagName === $name) {
                return $data;
            }
        }
        $this->trigger_template_error('Expected to be inside {' . $name . '}, but was not');
    }
}
