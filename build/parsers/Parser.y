/*<?php*/ // Commented PHP tag for syntax highlighting

/**
 * Smarty Internal Plugin Templateparser
 *
 * This is the template parser
 *
 *
 * @package Brainy
 * @subpackage Compiler
 * @author Uwe Tews
 * @author Matt Basta
 */

%stack_size 500
%name TP_
%declare_class {class Parser}
%include_class
{
    public $retvalue = 0;
    private $internalError = false;

    private $lex;
    private $compiler;
    private $smarty;
    private $template;
    private $security;
    public $current_buffer;

    private $safe_lookups = 0;
    private $strict_mode = false;
    private $strip = 0;

    public function __construct($lex, $compiler) {
        $this->lex = $lex;
        $this->compiler = $compiler;
        $this->smarty = $this->compiler->smarty;
        $this->template = $this->compiler->template;
        $this->security = isset($this->smarty->security_policy);
        $this->current_buffer = $this->root_buffer = new Helpers\TemplateBuffer();

        $this->safe_lookups = $this->smarty->safe_lookups;
    }

    /**
     * Strips whitespace from a string
     * @param string $string
     * @return string
     */
    protected static function stripString($string) {
        // Replaces whitespace followed by a `<` with null.
        // `     \n     </foo>` -> `</foo>`
        $string = preg_replace('/\s+(?=<\/)/ims', '', $string);
        // `     \n     <foo>` -> ` <foo>`
        // `     \n     &nbsp;` -> ` &nbsp;`
        $string = preg_replace('/\s+(?=[<&])/ims', ' ', $string);
        // Replaces `>` followed by whitespace with `>`
        // `<div>\n  foo` -> `<div>foo`
        $string = preg_replace('/>\s+(?=\S)/ims', '>', $string);
        // Replaces whitespace followed by anything else with a space.
        // `            data-hello="` -> ` data-hello="`
        $string = preg_replace('/\s+(?=\w)/ims', ' ', $string);

        // Is there work to be done at the end of the string?
        if ($string !== rtrim($string)) {
            $string = rtrim($string);
            // If the last non-whitespace character is not a `>`, add a space.
            if (substr($string, -1) !== '>') {
                $string .= ' ';
            }
        }
        return $string;
    }

    /**
     * @return bool
     */
    public function isStrictMode() {
        return $this->strict_mode;
    }

    /**
     * @param string $variable The name of the variable to look up
     * @return string|Wrappers\SafeLookupWrapper
     */
    public function compileVariable($variable)
    {
        switch ($this->safe_lookups) {
            case \Box\Brainy\Brainy::LOOKUP_UNSAFE:
                return '$_smarty_tpl->tpl_vars[' . $variable . ']';
            case \Box\Brainy\Brainy::LOOKUP_SAFE:
                return new Wrappers\SubscriptableSafeLookupWrapper('$_smarty_tpl->tpl_vars', '$_smarty_tpl->tpl_vars', $variable);
            case \Box\Brainy\Brainy::LOOKUP_SAFE_WARN:
                return new Wrappers\SubscriptableWarnSafeLookupWrapper('$_smarty_tpl->tpl_vars', '$_smarty_tpl->tpl_vars', $variable);
        }
    }

    /**
     * @param string $base
     * @param string $variable
     * @return string|Wrappers\SafeLookupWrapper
     */
    public function compileSafeLookupWithBase($base, $variable)
    {
        $unsafeBase = $base instanceof Wrappers\SafeLookupWrapper ? $base->getUnsafeRecursive() : $base;
        switch ($this->safe_lookups) {
            case \Box\Brainy\Brainy::LOOKUP_UNSAFE:
                return $base . '[' . $variable . ']';
            case \Box\Brainy\Brainy::LOOKUP_SAFE:
                return new Wrappers\ArraySafeLookupWrapper($unsafeBase, $base, $variable);
            case \Box\Brainy\Brainy::LOOKUP_SAFE_WARN:
                return new Wrappers\ArrayWarnSafeLookupWrapper($unsafeBase, $base, $variable);
        }
    }
}


%token_prefix TP_

%parse_accept
{
    $this->internalError = false;
    $this->retvalue = $this->_retvalue;
}

%syntax_error
{
    $this->internalError = true;
    $this->yymajor = $yymajor;
    $this->compiler->trigger_template_error();
}

%stack_overflow
{
    $this->internalError = true;
    $this->compiler->trigger_template_error("Stack overflow in template parser");
}

%left VERT.
%left COLON.

start(res) ::= strictmode(strict) generic_template. {
    res = strict . $this->root_buffer->toSmartyPHP();
}

strictmode(res) ::= SETSTRICT(foo). {
    $this->strict_mode = true;
    res = "/* strict mode */\n\$_smarty_tpl->strict_mode = true;\n";
}
strictmode(res) ::= . {
    res = '';
}


generic_template ::= template.
generic_template ::= extended_template.


extended_template ::= extended_template_header(h) extended_template_body(b). {

    $this->current_buffer->appendSubtree(new Helpers\Tag(b));

    $header = Constructs\ConstructInclude::compileOpen($this->compiler, h);
    $header .= "\$_smarty_tpl->tpl_vars['smarty']['blocks'] = array();\n"; // Clear existing blocks when starting a new template chain
    $this->current_buffer->appendSubtree(new Helpers\Tag($header));
}
extended_template_header(res) ::= LDELEXTENDS(lde) attributes(a) RDEL. {
    res = a;
}

extended_template_body(res) ::= extended_template_body_element(e). {
    res = e ?: '';
}
extended_template_body(res) ::= extended_template_body(base) extended_template_body_element(e). {
    res = base . (e ?: '');
}
extended_template_body_element(res) ::= COMMENT. {
    res = null;
}
extended_template_body_element(res) ::= STRIPON. {
    $this->strip++;
    res = null;
}
extended_template_body_element(res) ::= STRIPOFF. {
    if (!$this->strip) {
        $this->compiler->trigger_template_error('Unbalanced {strip} tags');
    }
    $this->strip--;
    res = null;
}
extended_template_body_element(res) ::= extended_template_block(b). {
    res = b;
}

extended_template_body_element(res) ::= TEXT(t). {
    if (trim(t) !== '') {
        $this->trigger_template_error('Unexpected string in template with {extends}: ' . t);
    }
    res = null;
}

extended_template_block(res) ::= nonterminal_template_block_head(head) template_block_content(content) nonterminal_template_block_close(foot). {
    res = head . content->toSmartyPHP() . foot;
}
extended_template_block(res) ::= nonterminal_template_block_head(head) nonterminal_template_block_close(foot). {
    res = head . foot;
}
nonterminal_template_block_head(res) ::= LDELBLOCK attributes(a) RDEL. {
    res = Constructs\ConstructBlockNonterminal::compileOpen($this->compiler, a);
}
nonterminal_template_block_close(res) ::= CLOSEBLOCK. {
    res = Constructs\ConstructBlockNonterminal::compileClose($this->compiler, array());
}
template_block_content(res) ::= template_element(e). {
    res = new Helpers\TemplateBuffer();
    if (e) {
        res->appendSubtree(e);
    }
}
template_block_content(res) ::= template_block_content(content) template_element(e). {
    res = content;
    if (e) {
        res->appendSubtree(e);
    }
}


template_block(res) ::= terminal_template_block_head(head) terminal_template_block_close(foot). {
    res = head . foot;
}
template_block(res) ::= terminal_template_block_head(head) template_block_content(content) terminal_template_block_close(foot). {
    res = head . content->toSmartyPHP() . foot;
}
terminal_template_block_head(res) ::= LDELBLOCK attributes(a) RDEL. {
    res = Constructs\ConstructBlockTerminal::compileOpen($this->compiler, a);
}
terminal_template_block_close(res) ::= CLOSEBLOCK. {
    res = Constructs\ConstructBlockTerminal::compileClose($this->compiler, array());
}


// single template element
template ::= template_element(e). {
    if (e !== null) {
        $this->current_buffer->appendSubtree(e);
    }
}

// loop of elements
template ::= template template_element(e). {
    if (e !== null) {
        $this->current_buffer->appendSubtree(e);
    }
}

// empty template
template ::= .

// Smarty tag
template_element(res) ::= smartytag(st) RDEL. {
    if ($this->compiler->has_code && !is_object(st)) {
        res = new Helpers\Tag((string) st);
    } elseif ($this->compiler->has_code) {
        res = st;
    } else {
        res = null;
    }
}

template_element(res) ::= template_block(b). {
    res = new Helpers\Tag(b);
}
// comments
template_element(res) ::= COMMENT(c). {
    res = null;
}

// Literal
template_element(res) ::= literal(l). {
    res = new Helpers\Text(l);
}

// template text
template_element(res) ::= TEXT(o). {
    if ($this->strip) {
        res = new Helpers\Text(self::stripString(o));
    } else {
        res = new Helpers\Text(o);
    }
}

// strip on
template_element ::= STRIPON. {
    $this->strip++;
}
// strip off
template_element ::= STRIPOFF. {
    if (!$this->strip) {
        $this->compiler->trigger_template_error('Unbalanced {strip} tags');
    }
    $this->strip--;
}


literal(res) ::= LITERALSTART literal_elements(l) LITERALEND. {
    res = l;
}

literal_elements(res) ::= literal_elements(l1) literal_element(l2). {
    res = l1 . l2;
}

literal_elements(res) ::= . {
    res = '';
}

literal_element(res) ::= literal(l). {
    res = l;
}

// TODO: needed?
literal_element(res) ::= LITERAL(l). {
    res = l;
}


//
// output tags start here
//

// output with optional attributes
smartytag(res) ::= LDEL expr(e). {
    $this->compiler->assertNoEnforcedModifiers(e instanceof Wrappers\StaticWrapper);
    if (e instanceof Wrappers\StaticWrapper) {
        e = (string) e;
    }
    $this->compiler->has_code = true;
    res = Constructs\ConstructPrintExpression::compileOpen(
        $this->compiler,
        array('value' => e, 'modifierlist' => array())
    );
}


//
// Smarty tags start here
//
smartytag(res) ::= LDEL variable(vi) EQUAL expr(e). {
    $base = vi;
    if ($base instanceof Wrappers\SafeLookupWrapper) {
        $base = $base->getUnsafeRecursive();
    }
    res = $base . ' = (' . e . ');';
}

smartytag(res) ::= LDEL DOLLAR ID(i) EQUAL value(e). {
    $this->compiler->has_code = true;
    res = Constructs\ConstructAssign::compileOpen(
        $this->compiler,
        array('value' => e, 'var' => "'" . i . "'")
    );
}

smartytag(res) ::= LDEL DOLLAR ID(i) EQUAL expr(e). {
    $this->compiler->has_code = true;
    res = Constructs\ConstructAssign::compileOpen(
        $this->compiler,
        array('value' => e, 'var' => "'" . i . "'")
    );
}

// tag with optional Smarty2 style attributes
smartytag(res) ::= LDEL ID(i) attributes(a). {
    $this->compiler->has_code = true;
    switch (i) {
        case 'assign':
            res = Constructs\ConstructAssign::compileOpen($this->compiler, a);
            break;
        case 'break':
            res = Constructs\ConstructBreak::compileOpen($this->compiler, a);
            break;
        case 'call':
            res = Constructs\ConstructCall::compileOpen($this->compiler, a);
            break;
        case 'capture':
            res = Constructs\ConstructCapture::compileOpen($this->compiler, a);
            break;
        case 'continue':
            res = Constructs\ConstructContinue::compileOpen($this->compiler, a);
            break;
        case 'else':
            res = Constructs\ConstructElse::compileOpen($this->compiler, a);
            break;
        case 'foreachelse':
            res = Constructs\ConstructForEachElse::compileOpen($this->compiler, a);
            break;
        case 'forelse':
            res = Constructs\ConstructForElse::compileOpen($this->compiler, a);
            break;
        case 'function':
            res = Constructs\ConstructFunction::compileOpen($this->compiler, a);
            break;
        case 'include':
            res = Constructs\ConstructInclude::compileOpen($this->compiler, a);
            break;
        case 'ldelim':
            res = new Helpers\Text($this->compiler->smarty->left_delimiter);
            break;
        case 'rdelim':
            res = new Helpers\Text($this->compiler->smarty->right_delimiter);
            break;
        default:
            res = $this->compiler->compileTag(i, a);
    }
}


// {if}, {elseif} and {while} tag
smartytag(res) ::= LDELIF(i) expr(ie). {
    $tag = trim(substr(i, $this->lex->ldel_length));
    $this->compiler->has_code = true;
    switch ($tag) {
        case 'if':
            res = Constructs\ConstructIf::compileOpen($this->compiler, array('cond' => ie));
            break;
        case 'elseif':
            res = Constructs\ConstructElseIf::compileOpen($this->compiler, array('cond' => ie));
            break;
        case 'while':
            res = Constructs\ConstructWhile::compileOpen($this->compiler, array('cond' => ie));
            break;
    }
}

smartytag(res) ::= LDELFOR statements(st) SEMICOLON optspace expr(ie) SEMICOLON optspace DOLLAR varvar(v2) foraction(e2) attributes(a). {
    $this->compiler->has_code = true;
    res = Constructs\ConstructFor::compileOpen(
        $this->compiler,
        array_merge(
            a,
            array(
                array('start' => st),
                array('ifexp' => ie),
                array('var' => v2),
                array('step' => e2)
            )
        )
    );
}

foraction(res) ::= EQUAL expr(e). {
    res = '=' . e;
}

foraction(res) ::= INCDEC(e). {
    res = e;
}

smartytag(res) ::= LDELFOR statement(st) TO expr(v) attributes(a). {
    $this->compiler->has_code = true;
    res = Constructs\ConstructFor::compileOpen(
        $this->compiler,
        array_merge(
            a,
            array(array('start' => st), array('to' => v))
        )
    );
}

smartytag(res) ::= LDELFOR statement(st) TO expr(v) STEP expr(v2) attributes(a). {
    $this->compiler->has_code = true;
    res = Constructs\ConstructFor::compileOpen(
        $this->compiler,
        array_merge(
            a,
            array(array('start' => st), array('to' => v), array('step' => v2))
        )
    );
}

// {foreach foo=x bar=y} tag
smartytag(res) ::= LDELFOREACH attributes(a). {
    $this->compiler->has_code = true;
    res = Constructs\ConstructForEach::compileOpen($this->compiler, a);
}

// {foreach [1, 2, 3] as $val foo=x bar=y} tag
smartytag(res) ::= LDELFOREACH SPACE expr(e) AS DOLLAR varvar(v0) attributes(a). {
    $this->compiler->has_code = true;
    res = Constructs\ConstructForEach::compileOpen(
        $this->compiler,
        array_merge(a, array(array('from' => e), array('item' => v0)))
    );
}

// {foreach [0 => 1, 1 => 2, 2 => 3] as $key => $var foo=x bar=y} tag
smartytag(res) ::= LDELFOREACH SPACE expr(e) AS DOLLAR varvar(v1) APTR DOLLAR varvar(v0) attributes(a). {
    $this->compiler->has_code = true;
    res = Constructs\ConstructForEach::compileOpen(
        $this->compiler,
        array_merge(
            a,
            array(
                array('from' => e),
                array('item' => v0),
                array('key' => v1),
            )
        )
    );
}


// end of tag  {/....}
smartytag(res) ::= LDELSLASH ID(i). {
    switch (i) {
        case 'capture':
            res = Constructs\ConstructCapture::compileClose($this->compiler, null);
            break;
        case 'for':
            res = Constructs\ConstructFor::compileClose($this->compiler, null);
            break;
        case 'foreach':
            res = Constructs\ConstructForEach::compileClose($this->compiler, null);
            break;
        case 'function':
            res = Constructs\ConstructFunction::compileClose($this->compiler, null);
            break;
        case 'if':
            res = Constructs\ConstructIf::compileClose($this->compiler, null);
            break;
        case 'while':
            res = Constructs\ConstructWhile::compileClose($this->compiler, null);
            break;
        default:
            res = $this->compiler->compileTag(i . 'close', array());
    }
}

//
//Attributes of Smarty tags
//

attributes(res) ::= . {
    res = array();
}

attributes(res) ::= attributes(a1) attribute(a2). {
    res = a1;
    res[] = a2;
}

attributes(res) ::= attribute(a). {
    res = array(a);
}

attribute(res) ::= SPACE ID(v) EQUAL expr(e). {
    res = array(v => e);
}

attribute(res) ::= SPACE INTEGER(i) EQUAL expr(e). {
    res = array(i => e);
}

attribute(res) ::= SPACE expr(e). {
    res = e;
}



//
// statement
//
statements(res) ::= statement(s). {
    res = array(s);
}

statements(res) ::= statements(s1) COMMA statement(s). {
    s1[]=s;
    res = s1;
}

statement(res) ::= DOLLAR varvar(v) EQUAL expr(e). {
    res = array('var' => v, 'value'=>e);
}

statement(res) ::= OPENP statement(st) CLOSEP. {
    res = st;
}


//
// expressions
//

// single value
expr(res) ::= value(v). {
    res = v;
}

// ternary
expr(res) ::= ternary(v). {
    res = v;
}

// arithmetic expression
expr(res) ::= expr(e) MATH(m) value(v). {
    res = Wrappers\StaticWrapper::staticIfAll(e . trim(m) . v, array(e, v));
}

expr(res) ::= expr(e) UNIMATH(m) value(v). {
    res = Wrappers\StaticWrapper::staticIfAll(e . trim(m) . v, array(e, v));
}

// bit operation
expr(res) ::= expr(e) ANDSYM(m) value(v). {
    res = Wrappers\StaticWrapper::staticIfAll(e . trim(m) . v, array(e, v));
}

// array
expr(res) ::= array(a). {
    res = a;
}

// modifier
expr(res) ::= expr(e) modifierlist(l). {
    $this->compiler->has_code = true;
    res = Constructs\ConstructModifier::compileOpen($this->compiler, array(
        'value' => e,
        'modifierlist' => l,
    ));
}

// if expression
// simple expression
expr(res) ::= expr(e1) ifcond(c) expr(e2). {
    res = new Wrappers\StaticWrapper(e1.c.e2);
}

expr(res) ::= expr(e1) ISIN array(a).  {
    res = new Wrappers\StaticWrapper('in_array('.e1.','.a.')');
}

expr(res) ::= expr(e1) ISIN value(v).  {
    res = new Wrappers\StaticWrapper('in_array('.e1.',(array)'.v.')');
}

expr(res) ::= expr(e1) lop(o) expr(e2).  {
    res = new Wrappers\StaticWrapper(e1 . o . e2);
}

expr(res) ::= expr(e1) ISDIVBY expr(e2). {
    res = new Wrappers\StaticWrapper('!('.e1.' % '.e2.')');
}

expr(res) ::= expr(e1) ISEVEN. {
    res = new Wrappers\StaticWrapper('!(1 & '.e1.')');
}

expr(res) ::= expr(e1) ISODD.  {
    res = new Wrappers\StaticWrapper('(1 & '.e1.')');
}

//
// ternary
//

ternary(res) ::= OPENP expr(v) CLOSEP QMARK expr(e1) COLON expr(e2). {
    res = Wrappers\StaticWrapper::staticIfAll(v . ' ? ' . e1 . ' : ' . e2, array(e1, e2));
}

// value
value(res) ::= variable(v). {
    res = v;
}

// +/- value
value(res) ::= UNIMATH(m) value(v). {
    res = Wrappers\StaticWrapper::staticConcat(m, v);
}

// logical negation
value(res) ::= NOT value(v). {
    res = Wrappers\StaticWrapper::staticConcat('!', v);
}

value(res) ::= TYPECAST(t) value(v). {
    res = t . v;
}

value(res) ::= value(v) INCDEC(o). {
    res = v . o;
}

// numeric
value(res) ::= INTEGER(n). {
    res = new Wrappers\StaticWrapper(n);
}

value(res) ::= INTEGER(n1) DOT INTEGER(n2). {
    res = new Wrappers\StaticWrapper(n1.'.'.n2);
}

// ID, true, false, null
value(res) ::= ID(id). {
    if (preg_match('~^true$~i', id)) {
        res = new Wrappers\StaticWrapper('true');
    } elseif (preg_match('~^false$~i', id)) {
        res = new Wrappers\StaticWrapper('false');
    } elseif (preg_match('~^null$~i', id)) {
        res = new Wrappers\StaticWrapper('null');
    } else {
        res = new Wrappers\StaticWrapper(var_export(id, true));
    }
}

// function call
value(res) ::= function(f). {
    res = f;
}

// expression
value(res) ::= OPENP expr(e) CLOSEP. {
    res = Wrappers\StaticWrapper::staticIfAll("(". e .")", array(e));
}

// singele quoted string
value(res) ::= SINGLEQUOTESTRING(t). {
    res = new Wrappers\StaticWrapper(t);
}

// double quoted string
value(res) ::= doublequoted_with_quotes(s). {
    res = new Wrappers\StaticWrapper(s);
}


//
// variables
//

variable(res) ::= DOLLAR varvar(v). {
    if (v === "'smarty'") {
        res = new Wrappers\SmartyVarLookupWrapper();
    } else {
        res = $this->compileVariable(v);
    }
}

variable(res) ::= variable(a1) indexdef(a2). {
    if (a1 instanceof Wrappers\SmartyVarLookupWrapper) {
        $decompiled = Decompile::decompileString(a2);
        switch ($decompiled) {
            case 'now':
                res = new Wrappers\StaticWrapper('time()');
                break;
            case 'template':
                $this->compiler->assertIsNotStrict('$smarty.template is not supported in strict mode', $this);
                res = new Wrappers\StaticWrapper('basename($_smarty_tpl->source->filepath)');
                break;
            case 'version':
                res = new Wrappers\StaticWrapper(var_export(\Box\Brainy\Brainy::SMARTY_VERSION, true));
                break;
            case 'ldelim':
                res = new Wrappers\StaticWrapper(var_export($this->compiler->smarty->left_delimiter, true));
                break;
            case 'rdelim':
                res = new Wrappers\StaticWrapper(var_export($this->compiler->smarty->right_delimiter, true));
                break;
            case 'foreach':
            case 'capture':
            case 'block':
                res = new Wrappers\SmartyVarPoisonWrapper($decompiled);
                break;
            default:
                $this->compiler->trigger_template_error('$smarty[' . a2 . '] is invalid');
        }

    } elseif (a1 instanceof Wrappers\SmartyVarPoisonWrapper) {
        if (a1->type === 'block') {
            $decompiled = Decompile::decompileString(a2);
            switch ($decompiled) {
                case 'child':
                    $data = $this->compiler->assertIsInTag('block');
                    $childBlockVar = $data['childVar'];

                    res = "($childBlockVar ? $childBlockVar(\$_smarty_tpl) ?: '' : '')";
                    break;
                default:
                    $this->compiler->trigger_template_error('$smarty.block[' . a2 . '] is invalid');
            }
        } else {
            // foreach and capture
            res = new Wrappers\StaticWrapper("\$_smarty_tpl->tpl_vars['smarty'][" . var_export(a1->type, true) . "][" . a2 . "]");
        }

    } else {
        res = $this->compileSafeLookupWithBase(a1, a2);
    }
}

variable(res) ::= variable(a1) objectelement(a2). {
    res = a1 . a2;
}

// single index definition
// Smarty2 style index
indexdef(res) ::= DOT DOLLAR varvar(v).  {
    $this->compiler->assertIsNotStrict('Variable indicies with dot syntax is not supported in strict mode', $this);
    res = $this->compileVariable(v);
}

indexdef(res) ::= DOT ID(i). {
    res = var_export(i, true);
}

indexdef(res) ::= DOT INTEGER(n). {
    res = n;
}

indexdef(res) ::= DOT LDEL expr(e) RDEL. {
    $this->compiler->assertIsNotStrict('Dot syntax with expressions is not supported in strict mode', $this);
    res = e;
}

// PHP style index
indexdef(res) ::= OPENB expr(e) CLOSEB. {
    res = e;
}

// variable variable names

// single identifier element
varvar(res) ::= ID(s). {
    res = '\''.s.'\'';
}

// sequence of identifier elements
varvar(res) ::= LDEL expr(e) RDEL. {
    $this->compiler->assertIsNotStrict('Variable variables are not supported in strict mode', $this);
    res = '('.e.')';
}


//
// objects
//

// variable
objectelement(res)::= PTR ID(i). {
    if ($this->security && substr(i, 0, 1) == '_') {
        $this->compiler->trigger_template_error('Call to private object member "' . i . '" not allowed');
    }
    res = '->'.i;
}

// method
objectelement(res)::= PTR ID(f) OPENP params(p) CLOSEP.  {
    if ($this->security && substr(f, 0, 1) == '_') {
        $this->compiler->trigger_template_error('Call to private object member "' . f . '" not allowed');
    }
    res = '->' . f . "(" . implode(',', p) . ")";
}


//
// function
//
function(res) ::= ID(f) OPENP params(p) CLOSEP. {
    if ($this->security && !$this->smarty->security_policy->isTrustedPhpFunction(f, $this->compiler)) {
        $this->compiler->trigger_template_error('Cannot use untrusted function: ' . f);
    }
    if (!(strcasecmp(f, 'isset') === 0 || strcasecmp(f, 'empty') === 0 || strcasecmp(f, 'array') === 0 || is_callable(f))) {
        $this->compiler->trigger_template_error("unknown function \"" . f . "\"");
    }

    $func_name = strtolower(f);

    $is_language_construct = $func_name === 'isset' || $func_name === 'empty';
    $combined_params = array();
    foreach (p as $param) {
        if ($is_language_construct && $param instanceof Wrappers\SafeLookupWrapper) {
            $combined_params[] = $param->getUnsafe();
            continue;
        }
        $combined_params[] = $param;
    }
    $par = implode(',', $combined_params);

    if ($func_name == 'isset') {
        if (count($combined_params) !== 1) {
            $this->compiler->trigger_template_error('Illegal number of paramer in "isset()"');
        }
        $isset_par = str_replace("')", "',null,true,false)", $par);
        res = f . "(". $isset_par .")";

    } elseif (in_array($func_name, array('empty', 'reset', 'current', 'end', 'prev', 'next'))) {

        if ($func_name !== 'empty') {
            $this->compiler->assertIsNotStrict($func_name . ' is not allowed in strict mode', $this);
        }

        if (count($combined_params) != 1) {
            $this->compiler->trigger_template_error('Illegal number of paramer in "' . $func_name . '()"');
        }
        if ($func_name == 'empty') {
            res = $func_name.'('.str_replace("')", "',null,true,false)",$combined_params[0]).')';
        } else {
            res = $func_name.'('.$combined_params[0].')';
        }
    } else {
        res = f . "(". $par .")";
    }
}


// function/method parameter
// multiple parameters
// TODO: could this allow a trailing comma in the signature?
params(res) ::= params(p) COMMA expr(e). {
    res = array_merge(p,array(e));
}

// single parameter
params(res) ::= expr(e). {
    res = array(e);
}

// kein parameter
params(res) ::= . {
    res = array();
}

//
// modifier
//
modifierlist(res) ::= modifierlist(l) modifier(m) modparameters(p). {
    res = array_merge(l,array(array_merge(m,p)));
}

modifierlist(res) ::= modifier(m) modparameters(p). {
    res = array(array_merge(m,p));
}

modifier(res) ::= VERT AT ID(m). {
    $this->compiler->assertIsNotStrict('@ is not allowed in templates', $this);
    res = array(m);
}

modifier(res) ::= VERT ID(m). {
    res =  array(m);
}

// multiple parameter
modparameters(res) ::= modparameters(mps) modparameter(mp). {
    res = array_merge(mps,mp);
}

// no parameter
modparameters(res) ::= . {
    res = array();
}

// parameter expression
modparameter(res) ::= COLON value(mp). {
    res = array(mp);
}

modparameter(res) ::= COLON array(mp). {
    res = array(mp);
}


// if conditions and operators
ifcond(res) ::= EQUALS. {
    res = '==';
}

ifcond(res) ::= NOTEQUALS. {
    res = '!=';
}

ifcond(res) ::= GREATERTHAN. {
    res = '>';
}

ifcond(res) ::= LESSTHAN. {
    res = '<';
}

ifcond(res) ::= GREATEREQUAL. {
    res = '>=';
}

ifcond(res) ::= LESSEQUAL. {
    res = '<=';
}

ifcond(res) ::= IDENTITY. {
    res = '===';
}

ifcond(res) ::= NONEIDENTITY. {
    res = '!==';
}

ifcond(res) ::= MOD. {
    res = '%';
}

lop(res) ::= LAND. {
    res = '&&';
}

lop(res) ::= LOR. {
    res = '||';
}

lop(res) ::= LXOR. {
    $this->compiler->assertIsNotStrict('XOR is not supported in strict mode', $this);
    res = ' XOR ';
}

//
// ARRAY element assignment
//
array(res) ::=  OPENB arrayelements(a) CLOSEB.  {
    res = 'array('.a.')';
}

arrayelements(res) ::=  arrayelements(a1) COMMA arrayelement(a).  {
    res = a1.','.a;
}
arrayelements(res) ::=  arrayelement(a).  {
    res = a;
}
arrayelements ::=  . {
    return;
}

arrayelement(res) ::=  expr(e1) APTR expr(e2). {
    res = e1.'=>'.e2;
}

arrayelement(res) ::=  expr(e). {
    res = e;
}


//
// double qouted strings
//
doublequoted_with_quotes(res) ::= QUOTE QUOTE. {
    res = "''";
}

doublequoted_with_quotes(res) ::= QUOTE doublequoted(s) QUOTE. {
    res = s->toSmartyPHP();
}


doublequoted(res) ::= doublequoted(o1) doublequotedcontent(o2). {
    o1->appendSubtree(o2);
    res = o1;
}

doublequoted(res) ::= doublequotedcontent(o). {
    res = new Helpers\DoubleQuoted($this);
    res->appendSubtree(o);
}

doublequotedcontent(res) ::=  DOLLARID(i). {
    res = new Helpers\Expression('(string)' . $this->compileVariable("'" . substr(i, 1) . "'"));
}

doublequotedcontent(res) ::=  LDEL expr(e) RDEL. {
    res = new Helpers\Expression('(string)(' . e . ')');
}

doublequotedcontent(res) ::=  TEXT(o). {
    res = new Helpers\DoubleQuotedContent(o);
}


//
// optional space
//
optspace(res) ::= SPACE(s).  {
    res = s;
}

optspace(res) ::= . {
    res = '';
}
