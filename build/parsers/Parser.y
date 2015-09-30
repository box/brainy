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
    const Err1 = "Security error: Call to private object member not allowed";
    const Err2 = "Security error: Call to dynamic object member not allowed";
    // states whether the parse was successful or not
    public $successful = true;
    public $retvalue = 0;
    public static $prefix_number = 0;
    private $lex;
    private $internalError = false;
    private $strip = 0;

    private $safe_lookups = 0;
    private $strict_mode = false;

    public function __construct($lex, $compiler) {
        $this->lex = $lex;
        $this->compiler = $compiler;
        $this->smarty = $this->compiler->smarty;
        $this->template = $this->compiler->template;
        $this->compiler->has_variable_string = false;
        $this->security = isset($this->smarty->security_policy);
        $this->block_nesting_level = 0;
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
     * @param string|void $value The member of the SmartyVariable to access
     * @return string|Wrappers\SafeLookupWrapper
     */
    public function compileVariable($variable, $value = 'value')
    {
        $unsafe = '$_smarty_tpl->tpl_vars[' . $variable . ']->' . $value;
        if ($this->safe_lookups === 0) { // Unsafe lookups
            return $unsafe;
        }
        $safe = '\Box\Brainy\Runtime\Lookups::safeVarLookup($_smarty_tpl->tpl_vars, '. $variable .', ' . $this->safe_lookups . ')->' . $value;
        return new Wrappers\SafeLookupWrapper($unsafe, $safe);
    }

    /**
     * @param string $base
     * @param string $variable
     * @return string|Wrappers\SafeLookupWrapper
     */
    public function compileSafeLookupWithBase($base, $variable)
    {
        $unsafe = $base . '[' . $variable . ']';
        if ($this->safe_lookups === 0) { // Unsafe lookups
            return $unsafe;
        }
        $safe = '\Box\Brainy\Runtime\Lookups::safeArrayLookup(' . $base . ', '. $variable .', ' . $this->safe_lookups . ')';
        return new Wrappers\SafeLookupWrapper($unsafe, $safe);
    }
}


%token_prefix TP_

%parse_accept
{
    $this->successful = !$this->internalError;
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

// complete template
start(res) ::= strictmode(strict) template. {
    res = strict . $this->root_buffer->to_smarty_php();
}

strictmode(res) ::= SETSTRICT(foo). {
    $this->strict_mode = true;
    res = "/* strict mode */\n\$_smarty_tpl->strict_mode = true;\n";
}
strictmode(res) ::= . {
    res = '';
}


// single template element
template ::= template_element(e). {
    if (e !== null) {
        $this->current_buffer->append_subtree(e);
    }
}

// loop of elements
template ::= template template_element(e). {
    if (e !== null) {
        $this->current_buffer->append_subtree(e);
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
    $this->compiler->has_variable_string = false;
    $this->block_nesting_level = count($this->compiler->_tag_stack);
}

// comments
template_element(res) ::= COMMENT(c). {
    res = null;
}

// Literal
template_element(res) ::= literal(l). {
    res = new Helpers\Text($this, l);
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
template_element ::= STRIPON(d). {
    $this->strip++;
}
// strip off
template_element ::= STRIPOFF(d). {
    if (!$this->strip) {
        $this->compiler->trigger_template_error('Unbalanced {strip} tags');
    }
    $this->strip--;
}
// process source of inheritance child block
template_element ::= BLOCKSOURCE(s). {
// if ($this->strip) {
    //     SMARTY_INTERNAL_COMPILE_BLOCK::blockSource($this->compiler, self::stripString(s));
// } else {
    //     SMARTY_INTERNAL_COMPILE_BLOCK::blockSource($this->compiler, s);
    // }
}

literal(res) ::= LITERALSTART LITERALEND. {
    res = '';
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

literal_element(res) ::= LITERAL(l). {
    res = l;
}


//
// output tags start here
//

// output with optional attributes
smartytag(res) ::= LDEL value(e). {
    $this->compiler->assert_no_enforced_modifiers(e instanceof Wrappers\StaticWrapper);
    if (e instanceof Wrappers\StaticWrapper) {
        e = (string) e;
    }
    $this->compiler->has_code = true;
    res = Constructs\ConstructPrintExpression::compileOpen(
        $this->compiler,
        array('value' => e),
        array()
    );
}

smartytag(res) ::= LDEL value(e) modifierlist(l) attributes(a). {
    $this->compiler->assert_expected_modifier(l, e instanceof Wrappers\StaticWrapper);
    if (e instanceof Wrappers\StaticWrapper) {
        e = (string) e;
    }
    $this->compiler->has_code = true;
    res = Constructs\ConstructPrintExpression::compileOpen(
        $this->compiler,
        array('value' => e, 'modifierlist' => l),
        a
    );
}

smartytag(res) ::= LDEL value(e) attributes(a). {
    $this->compiler->assert_no_enforced_modifiers(e instanceof Wrappers\StaticWrapper);
    if (e instanceof Wrappers\StaticWrapper) {
        e = (string) e;
    }
    $this->compiler->has_code = true;
    res = Constructs\ConstructPrintExpression::compileOpen(
        $this->compiler,
        array('value' => e),
        a
    );
}

smartytag(res) ::= LDEL expr(e) modifierlist(l) attributes(a). {
    $this->compiler->assert_expected_modifier(l, e instanceof Wrappers\StaticWrapper);
    if (e instanceof Wrappers\StaticWrapper) {
        e = (string) e;
    }
    $this->compiler->has_code = true;
    res = Constructs\ConstructPrintExpression::compileOpen(
        $this->compiler,
        array('value' => e, 'modifierlist' => l),
        a
    );
}

smartytag(res) ::= LDEL expr(e) attributes(a). {
    $this->compiler->assert_no_enforced_modifiers(e instanceof Wrappers\StaticWrapper);
    if (e instanceof Wrappers\StaticWrapper) {
        e = (string) e;
    }
    $this->compiler->has_code = true;
    res = Constructs\ConstructPrintExpression::compileOpen(
        $this->compiler,
        array('value' => e),
        a
    );
}

//
// Smarty tags start here
//
smartytag(res) ::= LDEL variable(vi) EQUAL expr(e). {
    res = vi . ' = (' . e . ');';
}

smartytag(res) ::= LDEL DOLLAR ID(i) EQUAL value(e). {
    $this->compiler->has_code = true;
    res = Constructs\ConstructAssign::compileOpen(
        $this->compiler,
        array('value' => e, 'var' => "'" . i . "'"),
        null
    );
}

smartytag(res) ::= LDEL DOLLAR ID(i) EQUAL expr(e). {
    $this->compiler->has_code = true;
    res = Constructs\ConstructAssign::compileOpen(
        $this->compiler,
        array('value' => e, 'var' => "'" . i . "'"),
        null
    );
}

// tag with optional Smarty2 style attributes
smartytag(res) ::= LDEL ID(i) attributes(a). {
    $this->compiler->has_code = true;
    switch (i) {
        case 'assign':
            res = Constructs\ConstructAssign::compileOpen($this->compiler, a, null);
            break;
        case 'include':
            res = Constructs\ConstructInclude::compileOpen($this->compiler, a, null);
            break;
        default:
            res = $this->compiler->compileTag(i, a);
    }
}

smartytag(res) ::= LDEL ID(i). {
    $this->compiler->has_code = true;
    switch (i) {
        case 'foreachelse':
            res = Constructs\ConstructForEachElse::compileOpen($this->compiler, null, null);
            break;
        case 'forelse':
            res = Constructs\ConstructForElse::compileOpen($this->compiler, null, null);
            break;
        case 'else':
            res = Constructs\ConstructElse::compileOpen($this->compiler, null, null);
            break;
        case 'ldelim':
            res = new Helpers\Text($this->compiler->smarty->left_delimiter);
            break;
        case 'rdelim':
            res = new Helpers\Text($this->compiler->smarty->right_delimiter);
            break;
        default:
            res = $this->compiler->compileTag(i, array());
    }
}

// tag with modifier and optional Smarty2 style attributes
smartytag(res) ::= LDEL ID(i) modifierlist(l)attributes(a). {
    res = 'ob_start();\necho ' . $this->compiler->compileTag(i, a) . 'echo ';
    $this->compiler->has_code = true;
    res .= Constructs\ConstructModifier::compileOpen($this->compiler, array(
        'value' => 'ob_get_clean()',
        'modifierlist' => l,
    ), null);
}


// {if}, {elseif} and {while} tag
smartytag(res) ::= LDELIF(i) expr(ie). {
    $tag = trim(substr(i, $this->lex->ldel_length));
    $this->compiler->has_code = true;
    switch ($tag) {
        case 'if':
            res = Constructs\ConstructIf::compileOpen($this->compiler, array('cond' => ie), null);
            break;
        case 'elseif':
            res = Constructs\ConstructElseIf::compileOpen($this->compiler, array('cond' => ie), null);
            break;
        case 'while':
            res = Constructs\ConstructWhile::compileOpen($this->compiler, array('cond' => ie), null);
            break;
    }
}

smartytag(res) ::= LDELIF(i) expr(ie) attributes(a). {
    $tag = trim(substr(i, $this->lex->ldel_length));
    $this->compiler->has_code = true;
    switch ($tag) {
        case 'if':
            res = Constructs\ConstructIf::compileOpen($this->compiler, array('cond' => ie), a);
            break;
        case 'elseif':
            res = Constructs\ConstructElseIf::compileOpen($this->compiler, array('cond' => ie), a);
            break;
        case 'while':
            res = Constructs\ConstructWhile::compileOpen($this->compiler, array('cond' => ie), a);
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
        ),
        null
    );
}

foraction(res) ::= EQUAL expr(e). {
    res = '='.e;
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
        ),
        null
    );
}

smartytag(res) ::= LDELFOR statement(st) TO expr(v) STEP expr(v2) attributes(a). {
    $this->compiler->has_code = true;
    res = Constructs\ConstructFor::compileOpen(
        $this->compiler,
        array_merge(
            a,
            array(array('start' => st), array('to' => v), array('step' => v2))
        ),
        null
    );
}

// {foreach} tag
smartytag(res) ::= LDELFOREACH attributes(a). {
    $this->compiler->has_code = true;
    res = Constructs\ConstructForEach::compileOpen($this->compiler, a, null);
}

// {foreach $array as $var} tag
smartytag(res) ::= LDELFOREACH SPACE value(v1) AS DOLLAR varvar(v0) attributes(a). {
    $this->compiler->has_code = true;
    res = Constructs\ConstructForEach::compileOpen(
        $this->compiler,
        array_merge(a, array(array('from' => v1), array('item' => v0))),
        null
    );
}

smartytag(res) ::= LDELFOREACH SPACE value(v1) AS DOLLAR varvar(v2) APTR DOLLAR varvar(v0) attributes(a). {
    $this->compiler->has_code = true;
    res = Constructs\ConstructForEach::compileOpen(
        $this->compiler,
        array_merge(
            a,
            array(
                array('from' => v1),
                array('item' => v0),
                array('key' => v2),
            )
        ),
        null
    );
}

smartytag(res) ::= LDELFOREACH SPACE expr(e) AS DOLLAR varvar(v0) attributes(a). {
    $this->compiler->has_code = true;
    res = Constructs\ConstructForEach::compileOpen(
        $this->compiler,
        array_merge(a, array(array('from' => v1), array('item' => v0))),
        null
    );
}

smartytag(res) ::= LDELFOREACH SPACE expr(e) AS DOLLAR varvar(v1) APTR DOLLAR varvar(v0) attributes(a). {
    $this->compiler->has_code = true;
    res = Constructs\ConstructForEach::compileOpen(
        $this->compiler,
        array_merge(
            a,
            array(
                array('from' => v1),
                array('item' => v0),
                array('key' => v2),
            )
        ),
        null
    );
}


// {$smarty.block.child} or {$smarty.block.parent}
smartytag(res) ::= LDEL SMARTYBLOCKCHILDPARENT(i). {
    $j = strrpos(i,'.');
    if (i[$j+1] == 'c') {
// {$smarty.block.child}
        // res = SMARTY_INTERNAL_COMPILE_BLOCK::compileChildBlock($this->compiler);
    } else {
// {$smarty.block.parent}
        // res = SMARTY_INTERNAL_COMPILE_BLOCK::compileParentBlock($this->compiler);
    }
}


// end of block tag  {/....}
smartytag(res) ::= LDELSLASH ID(i). {
    switch (i) {
        case 'if':
            res = Constructs\ConstructIf::compileClose($this->compiler, null, null);
            break;
        case 'for':
            res = Constructs\ConstructFor::compileClose($this->compiler, null, null);
            break;
        case 'foreach':
            res = Constructs\ConstructForEach::compileClose($this->compiler, null, null);
            break;
        default:
            res = $this->compiler->compileTag(i.'close',array());
    }
}

smartytag(res) ::= LDELSLASH ID(i) modifierlist(l). {
    switch (i) {
        case 'if':
            res = Constructs\ConstructIf::compileClose($this->compiler, null, null);
            break;
        case 'for':
            res = Constructs\ConstructFor::compileClose($this->compiler, null, null);
            break;
        case 'foreach':
            res = Constructs\ConstructForEach::compileClose($this->compiler, null, null);
            break;
        default:
            res = $this->compiler->compileTag(i.'close',array(),array('modifier_list'=>l));
    }
}

//
//Attributes of Smarty tags
//
// list of attributes
attributes(res) ::= attributes(a1) attribute(a2). {
    res = a1;
    res[] = a2;
}

// single attribute
attributes(res) ::= attribute(a). {
    res = array(a);
}

// no attributes
attributes(res) ::= . {
    res = array();
}

// attribute
attribute(res) ::= SPACE ID(v) EQUAL ID(id). {
    if (preg_match('~^true$~i', id)) {
        res = array(v=>'true');
    } elseif (preg_match('~^false$~i', id)) {
        res = array(v=>'false');
    } elseif (preg_match('~^null$~i', id)) {
        res = array(v=>'null');
    } else {
        res = array(v=>"'".id."'");
    }
}

attribute(res) ::= ATTR(v) expr(e). {
    res = array(trim(v," =\n\r\t")=>e);
}

attribute(res) ::= ATTR(v) value(e). {
    res = array(trim(v," =\n\r\t")=>e);
}

attribute(res) ::= SPACE ID(v). {
    res = "'".v."'";
}

attribute(res) ::= SPACE expr(e). {
    res = e;
}

attribute(res) ::= SPACE value(v). {
    res = v;
}

attribute(res) ::= SPACE INTEGER(i) EQUAL expr(e). {
    res = array(i=>e);
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
    $this->compiler->assert_is_not_strict('Variable variable assignment is not supported in strict mode', $this);
    res = array('var' => v, 'value'=>e);
}

statement(res) ::= variablebase(vi) EQUAL expr(e). {
    res = array('var' => vi, 'value'=>e);
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
    res = Wrappers\StaticWrapper::static_if_all(e . trim(m) . v, array(e, v));
}

expr(res) ::= expr(e) UNIMATH(m) value(v). {
    res = Wrappers\StaticWrapper::static_if_all(e . trim(m) . v, array(e, v));
}

// bit operation
expr(res) ::= expr(e) ANDSYM(m) value(v). {
    res = Wrappers\StaticWrapper::static_if_all(e . trim(m) . v, array(e, v));
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
    ), null);
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

expr(res) ::= expr(e1) ISNOTDIVBY expr(e2).  {
    $this->compiler->assert_is_not_strict('`is not div by` is not supported in strict mode', $this);
    res = new Wrappers\StaticWrapper('('.e1.' % '.e2.')');
}

expr(res) ::= expr(e1) ISEVEN. {
    res = new Wrappers\StaticWrapper('!(1 & '.e1.')');
}

expr(res) ::= expr(e1) ISNOTEVEN.  {
    $this->compiler->assert_is_not_strict('`is not even` is not supported in strict mode', $this);
    res = new Wrappers\StaticWrapper('(1 & '.e1.')');
}

expr(res) ::= expr(e1) ISEVENBY expr(e2).  {
    $this->compiler->assert_is_not_strict('`is even by` is not supported in strict mode', $this);
    res = new Wrappers\StaticWrapper('!(1 & '.e1.' / '.e2.')');
}

expr(res) ::= expr(e1) ISNOTEVENBY expr(e2). {
    $this->compiler->assert_is_not_strict('`is not even by` is not supported in strict mode', $this);
    res = new Wrappers\StaticWrapper('(1 & '.e1.' / '.e2.')');
}

expr(res) ::= expr(e1) ISODD.  {
    res = new Wrappers\StaticWrapper('(1 & '.e1.')');
}

expr(res) ::= expr(e1) ISNOTODD. {
    $this->compiler->assert_is_not_strict('`is not odd` is not supported in strict mode', $this);
    res = new Wrappers\StaticWrapper('!(1 & '.e1.')');
}

expr(res) ::= expr(e1) ISODDBY expr(e2). {
    $this->compiler->assert_is_not_strict('`is odd by` is not supported in strict mode', $this);
    res = new Wrappers\StaticWrapper('(1 & '.e1.' / '.e2.')');
}

expr(res) ::= expr(e1) ISNOTODDBY expr(e2).  {
    $this->compiler->assert_is_not_strict('`is not odd by` is not supported in strict mode', $this);
    res = new Wrappers\StaticWrapper('!(1 & '.e1.' / '.e2.')');
}

//
// ternary
//
ternary(res) ::= OPENP expr(v) CLOSEP  QMARK DOLLAR ID(e1) COLON  expr(e2). {
    res = v.' ? '. $this->compileVariable("'".e1."'") . ' : '.e2;
}

ternary(res) ::= OPENP expr(v) CLOSEP  QMARK  expr(e1) COLON  expr(e2). {
    res = v.' ? '.e1.' : '.e2;
}

// value
value(res) ::= variable(v). {
    res = v;
}

// +/- value
value(res) ::= UNIMATH(m) value(v). {
    res = Wrappers\StaticWrapper::static_concat(m, v);
}

// logical negation
value(res) ::= NOT value(v). {
    res = Wrappers\StaticWrapper::static_concat('!', v);
}

value(res) ::= TYPECAST(t) value(v). {
    res = t . v;
}

value(res) ::= variable(v) INCDEC(o). {
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
    res = Wrappers\StaticWrapper::static_if_all("(". e .")", array(e));
}

// singele quoted string
value(res) ::= SINGLEQUOTESTRING(t). {
    res = new Wrappers\StaticWrapper(t);
}

// double quoted string
value(res) ::= doublequoted_with_quotes(s). {
    res = new Wrappers\StaticWrapper(s);
}

value(res) ::= value(v) modifierlist(l). {
    $this->compiler->has_code = true;
    res = Constructs\ConstructModifier::compileOpen($this->compiler, array(
        'value' => v,
        'modifierlist' => l,
    ), null);
}


//
// variables
//

variable(res) ::= variableinternal(base). {
    res = base;
}

variablebase(res) ::= DOLLAR varvar(v). {
    res = v;
}

variableinternal(res) ::= variableinternal(a1) indexdef(a2). {
    res = $this->compileSafeLookupWithBase(a1, a2);
}

// FIXME: This is a hack to make $smarty.foreach.foo work. :(
variableinternal(res) ::= variablebase(base) indexdef(a) indexdef(b). {
    if (base != '\'smarty\'') {
        res = $this->compileSafeLookupWithBase($this->compileVariable(base), a);
        res = $this->compileSafeLookupWithBase(res, b);
    } else {
        switch (Decompile::decompileString(a)) {
            case 'foreach':
                res = new Wrappers\StaticWrapper("\$_smarty_tpl->tpl_vars['smarty']->value['foreach'][" . b . "]");
                break;
            case 'capture':
                res = new Wrappers\StaticWrapper("\$_smarty_tpl->tpl_vars['smarty']->value['capture'][" . b . "]");
                break;
            default:
                $this->compiler->trigger_template_error('$smarty.' . trim(a, "'") . ' is invalid');
        }
    }
}

variableinternal(res) ::= variablebase(base) indexdef(a). {
    if (base !== '\'smarty\'') {
        res = $this->compileSafeLookupWithBase($this->compileVariable(base), a);
    } else {
        switch (Decompile::decompileString(a)) {
            case 'now':
                res = new Wrappers\StaticWrapper('time()');
                break;
            case 'template':
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
            default:
                $this->compiler->trigger_template_error('$smarty.' . trim(a, "'") . ' is invalid');
        }
    }
}

variableinternal(res) ::= variablebase(v). {
    res = $this->compileVariable(v);
}

variableinternal(res) ::= variableinternal(a1) objectelement(a2). {
    res = a1 . a2;
}
// variable with property
variableinternal(res) ::= DOLLAR varvar(v) AT ID(p). {
    res = $this->compileVariable(v, p);
}

// single index definition
// Smarty2 style index
indexdef(res) ::= DOT DOLLAR varvar(v).  {
    $this->compiler->assert_is_not_strict('Variable indicies with dot syntax is not supported in strict mode', $this);
    res = $this->compileVariable(v);
}

indexdef(res) ::= DOT DOLLAR varvar(v) AT ID(p). {
    $this->compiler->assert_is_not_strict('Variable indicies with dot syntax is not supported in strict mode', $this);
    res = $this->compileVariable(v).'->'.p;
}

indexdef(res) ::= DOT ID(i). {
    res = "'". i ."'";
}

indexdef(res) ::= DOT INTEGER(n). {
    res = n;
}

indexdef(res) ::= DOT LDEL expr(e) RDEL. {
    $this->compiler->assert_is_not_strict('Dot syntax with expressions is not supported in strict mode', $this);
    res = e;
}

// PHP style index
indexdef(res) ::= OPENB expr(e) CLOSEB. {
    res = e;
}

// variable variable names

// single identifier element
varvar(res) ::= varvarele(v). {
    res = v;
}

// sequence of identifier elements
varvar(res) ::= varvar(v1) varvarele(v2). {
    res = v1.'.'.v2;
}

// fix sections of element
varvarele(res) ::= ID(s). {
    res = '\''.s.'\'';
}

// variable sections of element
varvarele(res) ::= LDEL expr(e) RDEL. {
    $this->compiler->assert_is_not_strict('Variable variables are not supported in strict mode', $this);
    res = '('.e.')';
}

//
// objects
//

// variable
objectelement(res)::= PTR ID(i). {
    if ($this->security && substr(i, 0, 1) == '_') {
        $this->compiler->trigger_template_error(self::Err1);
    }
    res = '->'.i;
}

objectelement(res)::= PTR DOLLAR varvar(v). {
    $this->compiler->assert_is_not_strict('Variable method calls are not supported in strict mode', $this);
    if ($this->security) {
        $this->compiler->trigger_template_error(self::Err2);
    }
    res = '->{'.$this->compileVariable(v).'}';
}

objectelement(res)::= PTR LDEL expr(e) RDEL. {
    $this->compiler->assert_is_not_strict('Variable method calls are not supported in strict mode', $this);
    if ($this->security) {
        $this->compiler->trigger_template_error(self::Err2);
    }
    res = '->{'.e.'}';
}

objectelement(res)::= PTR ID(ii) LDEL expr(e) RDEL. {
    $this->compiler->assert_is_not_strict('Variable method calls are not supported in strict mode', $this);
    if ($this->security) {
        $this->compiler->trigger_template_error(self::Err2);
    }
    res = '->{\''.ii.'\'.'.e.'}';
}

// method
objectelement(res)::= PTR method(f).  {
    res = '->'.f;
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
        $isset_par = str_replace("')->value", "',null,true,false)->value", $par);
        res = f . "(". $isset_par .")";

    } elseif (in_array($func_name, array('empty', 'reset', 'current', 'end', 'prev', 'next'))) {

        if ($func_name !== 'empty') {
            $this->compiler->assert_is_not_strict($func_name . ' is not allowed in strict mode', $this);
        }

        if (count($combined_params) != 1) {
            $this->compiler->trigger_template_error('Illegal number of paramer in "' . $func_name . '()"');
        }
        if ($func_name == 'empty') {
            res = $func_name.'('.str_replace("')->value", "',null,true,false)->value",$combined_params[0]).')';
        } else {
            res = $func_name.'('.$combined_params[0].')';
        }
    } else {
        res = f . "(". $par .")";
    }
}

//
// method
//
method(res) ::= ID(f) OPENP params(p) CLOSEP. {
    if ($this->security && substr(f,0,1) == '_') {
        $this->compiler->trigger_template_error(self::Err1);
    }
    res = f . "(". implode(',',p) .")";
}

// function/method parameter
// multiple parameters
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
    $this->compiler->assert_is_not_strict('@ is not allowed in templates', $this);
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
    $this->compiler->assert_is_not_strict('XOR is not supported in strict mode', $this);
    res = ' XOR ';
}

//
// ARRAY element assignment
//
array(res) ::=  OPENB arrayelements(a) CLOSEB.  {
    res = 'array('.a.')';
}

arrayelements(res) ::=  arrayelement(a).  {
    res = a;
}

arrayelements(res) ::=  arrayelements(a1) COMMA arrayelement(a).  {
    res = a1.','.a;
}

arrayelements ::=  .  {
    return;
}

arrayelement(res) ::=  value(e1) APTR expr(e2). {
    res = e1.'=>'.e2;
}

arrayelement(res) ::=  ID(i) APTR expr(e2). {
    res = '\''.i.'\'=>'.e2;
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
    res = s->to_smarty_php();
}


doublequoted(res) ::= doublequoted(o1) doublequotedcontent(o2). {
    o1->append_subtree(o2);
    res = o1;
}

doublequoted(res) ::= doublequotedcontent(o). {
    res = new Helpers\DoubleQuoted($this);
    res->append_subtree(o);
}

doublequotedcontent(res) ::=  DOLLARID(i). {
    res = new Helpers\Expression('(string)' . $this->compileVariable("'" . substr(i, 1) . "'"));
}

doublequotedcontent(res) ::=  LDEL variable(v) RDEL. {
    res = new Helpers\Expression('(string)' . v);
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
