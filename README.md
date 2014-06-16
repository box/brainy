![Brainy](https://gitenterprise.inside-box.net/mbasta/brainy/raw/fixes/documentation/brainy.png)

Brainy is a replacement for the popular [Smarty](http://www.smarty.net/)
templating language. It is a fork from the Smarty 3 trunk.


## Why Brainy?

- Brainy generates cleaner and faster code than Smarty by default.
- Brainy removes features that are infrequently used and increase code bloat.
  Brainy's feature set promotes best practices and discourages hacks.
- Brainy is safer than Smarty in that it removes support for features like
  `eval` and injecting arbitrary PHP into a template.


## Minimum Requirements

- PHP 5.3 or higher
- HHVM 3.1.x or higher

Unlike Smarty 3, PHP 5.2.x is not a supported platform. Use Brainy with old
versions of PHP at your own risk.

Note that HHVM support is currently experimental, though most common behaviors
work as expected.


## Differences from Smarty

While Brainy will work as a drop-in replacement for Smarty in most
applications, there are some differences that may make it difficult to switch.


### Incompatibilities

- Inline and arbitrary PHP is disallowed for security reasons.
  - PHP tags: `<?php ?>`
  - Shorthand PHP tags: `<? ?>`
  - ASP tags: `<% %>`
  - PHP blocks: `{php}`
  - `{eval}`
  - `{include_php}`
- Backticks in template strings no longer function like curly braces in PHP.
- Caching backends are removed (MySQL, Memcached).
- `nocache` is always set to `true` and cannot be disabled.
- Some other features are removed:
  - `{fetch}` is removed as it can result in unforseen performance and security
    issues.
  - `{debug}` is removed as it can reveal sensitive information.
  - URL-based debugging
- Whitespace surrounding tags is not always treated the same as in Smarty.
- PHP template resources are removed: `$smarty->fetch('php:my_php_file.php')`

Additionally, undefined variables do not throw errors (similar to Smarty 2's
behavior). For example:

```php
{if $foo}{$bar}{/if}
```

If either `$foo` or `$bar` are undefined, the template will simply return an
empty string. In Smarty 3, the behavior is to throw an undefined index error.


### Added Features

- `stripwhitespace` filter: This functions similarly to `trimwhitespace`, but does not strip HTML comments and does not account for HTML elements like `<pre>` or `<textarea>`. For many applications, these features are not necessary and using `stripwhitespace` instead of `trimwhitespace` can greatly improve performance.


## Where is Brainy headed?

- **Phase 1**: Provide a clean, drop-in replacement for Smarty that generates
  cleaner code and increases code quality.
- **Phase 2**: Provide a backwards-compatible interface for allowing templates
  to compile asynchronously.
- **Phase 3**: Allow templates to be compiled to [Hack](http://hacklang.org/)
  and add full async support.
