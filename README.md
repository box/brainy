[![Project Status](http://opensource.box.com/badges/active.svg)](http://opensource.box.com/badges)

![Brainy](logo.png "Brainy")
# Brainy

Brainy is a replacement for the popular [Smarty](http://www.smarty.net/)
templating language. It is a fork from the Smarty 3 trunk.

Brainy is still very new and it's likely that you will encounter some issues.
Please report any problems that you encounter.


## Why Brainy?

- Brainy generates cleaner and faster code than Smarty by default.
- Brainy has security defaults that align better with [best practices](http://www.phptherightway.com/#security).
- Brainy removes features that are infrequently used and increase code bloat.
- Brainy is safer than Smarty in that it removes support for features like
  `eval` and injecting arbitrary PHP into a template. Brainy uses less PHP magic.


## Getting Started

Check out the [Getting Started](https://github.com/box/brainy/wiki/Getting-Started)
page on the wiki.


### Minimum Requirements

- PHP 5.3.2 or higher
- (optionally) HHVM 3.1.x or higher

Unlike Smarty 3, PHP 5.2.x is not a supported platform. Use Brainy with old
versions of PHP at your own risk.

Note that HHVM support is currently experimental, though most common behaviors
work as expected.


### Differences from Smarty

While Brainy will work as a drop-in replacement for Smarty in most
applications, there are some differences that may make it difficult to switch.

A complete list of differences is listed [on the wiki](https://github.com/box/brainy/wiki/Differences-from-Smarty).


## Contributing to Brainy

For information on how to set up a local dev environment and run the tests,
see the wiki page on [Hacking on Brainy](https://github.com/box/brainy/wiki/Hacking-on-Brainy).


### Where is Brainy headed?

See [the project roadmap](https://github.com/box/brainy/wiki/Roadmap)
for information on upcoming releases.


### Requested Contributions

If you're interested in helping out, pull requests for the following tasks will be warmly welcomed:

- Convert all non-public methods to use camel case.
- Add proper PHPDoc annotations to all functions and methods.
- Remove magic and bad practices:
  - `__call`, `__get`, etc.
  - Eliminate dead code
  - `@` error suppression
  - etc.
- Remove workarounds for unsupported versions of PHP.
- Help identify and resolve potential security issues, or find ways to help developers avoid security issues.
- Performance optimization of generated code

At the time of writing, the project has approximately 68% line coverage.


## Support

Need to contact us directly? Email oss@box.com and be sure to include the name
of this project in the subject.


## Copyright and License

Copyright 2014-2015 Box, Inc. All rights reserved.

Copyright 2002 – 2014 New Digital Group, Inc.

This library is licensed under the GNU Lesser Public License. A copy of the
license should have [been provided](LICENSE.md).
