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


class TemplateCompiler extends TemplateCompilerBase
{
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
        if (function_exists('mb_internal_encoding') && ((int) ini_get('mbstring.func_overload')) & 2) {
            $mbEncoding = mb_internal_encoding();
            mb_internal_encoding('ASCII');
        } else {
            $mbEncoding = null;
        }

        // get tokens from lexer and parse them
        while ($this->lex->yylex() && !$this->abort_and_recompile) {
            $this->parser->doParse($this->lex->token, $this->lex->value);
        }

        if ($this->abort_and_recompile) {
            // exit here on abort
            return false;
        }
        // finish parsing process
        $this->parser->doParse(0, 0);
        if ($mbEncoding) {
            mb_internal_encoding($mbEncoding);
        }
        // check for unclosed tags
        if (count($this->_tag_stack) > 0) {
            // get stacked info
            list($openTag, $_data) = array_pop($this->_tag_stack);
            $this->trigger_template_error("unclosed {$this->smarty->left_delimiter}" . $openTag . "{$this->smarty->right_delimiter} tag");
        }
        // return compiled code
        // return str_replace(array("? >\n<?php","? ><?php"), array('',''), $this->parser->retvalue);
        return $this->parser->retvalue;
    }

    /**
     * @param string $reason
     * @param Template|null|void $template
     * @return void
     * @throws BrainyStrictModeException
     */
    public function assert_is_not_strict($reason, $template = null)
    {
        parent::assert_is_not_strict($reason, $template);
        if ($this->parser && $this->parser->isStrictMode()) {
            $this->trigger_template_error('Strict Mode: ' . $reason, null, 'BrainyStrictModeException');
        }
    }

}
