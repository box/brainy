<?php

namespace Box\Brainy\Compiler\Constructs;

use \Box\Brainy\Exceptions\SmartyCompilerException;


abstract class BaseConstruct
{

    /**
     * Compiles the opening tag for a function
     * @param  \Box\Brainy\Compiler\TemplateCompiler $compiler A compiler reference
     * @param  array|null  $args     Arguments
     * @param  array|null  $params   Parameters
     * @return mixed
     */
    public static function compileOpen(\Box\Brainy\Compiler\TemplateCompiler $compiler, $args, $params)
    {
        throw new \BadMethodCallException('Not implemented!');
    }

    /**
     * Compiles the closing tag for a function
     * @param  \Box\Brainy\Compiler\TemplateCompiler $compiler A compiler reference
     * @param  array|null  $args     Arguments
     * @param  array|null  $params   Parameters
     * @return mixed
     */
    public static function compileClose(\Box\Brainy\Compiler\TemplateCompiler $compiler, $args, $params)
    {
        throw new \BadMethodCallException('Not implemented!');
    }

    /**
     * Returns an argument from the args array
     * @param  array|null  $args The argument list
     * @param  string $name The argument name
     * @return mixed
     */
    public static function getRequiredArg(array $args, $name)
    {
        if (isset($args[$name])) {
            return $args[$name];
        }
        foreach ($args as $arg) {
            if (!is_array($arg) || !isset($arg[$name])) {
                continue;
            }
            return $arg[$name];
        }
        throw new SmartyCompilerException('Expected argument not found; missing "' . $name . '" attribute.');
    }

    /**
     * Returns an argument from the args array or a default
     * @param  array|null  $args The argument list
     * @param  string $name The argument name
     * @param  mixed|null|void $default The default value
     * @return mixed
     */
    public static function getOptionalArg(array $args, $name, $default = null)
    {
        if (isset($args[$name])) {
            return $args[$name];
        }
        foreach ($args as $arg) {
            if (!is_array($arg) || !isset($arg[$name])) {
                continue;
            }
            return $arg[$name];
        }
        return $default;
    }

    /**
     * Push opening tag name on stack
     *
     * Optionally additional data can be saved on stack
     *
     * @param object $compiler compiler object
     * @param string $openTag  the opening tag's name
     * @param mixed  $data     optional data saved
     */
    public static function openTag($compiler, $openTag, $data = null)
    {
        array_push($compiler->_tag_stack, array($openTag, $data));
    }

    /**
     * Pop closing tag
     *
     * Raise an error if this stack-top doesn't match with expected opening tags
     *
     * @param  \Box\Brainy\Compiler\TemplateCompiler $compiler
     * @param  array|string $expectedTag the expected opening tag names
     * @return mixed        any type the opening tag's name or saved data
     */
    public static function closeTag($compiler, $expectedTag)
    {
        if (count($compiler->_tag_stack) === 0) {
            // wrong nesting of tags
            $compiler->trigger_template_error("unexpected closing tag", $compiler->lex->taglineno);
        }

        // get stacked info
        list($openTag, $data) = array_pop($compiler->_tag_stack);
        // open tag must match with the expected ones
        if (!in_array($openTag, (array) $expectedTag)) {
            // wrong nesting of tags
            $compiler->trigger_template_error("unclosed {$compiler->smarty->left_delimiter}" . $openTag . "{$compiler->smarty->right_delimiter} tag");
            return;
        }

        return is_null($data) ? $openTag : $data;
    }

}
