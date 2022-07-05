[![Build Status](https://travis-ci.org/box/brainy.svg?branch=master)](https://travis-ci.org/box/brainy)
[![Latest Stable Version](https://poser.pugx.org/box/brainy/v/stable)](https://packagist.org/packages/box/brainy)
[![Project Status](http://opensource.box.com/badges/active.svg)](http://opensource.box.com/badges)
[![Total Downloads](https://poser.pugx.org/box/brainy/downloads)](https://packagist.org/packages/box/brainy)
[![License](https://poser.pugx.org/box/brainy/license)](https://packagist.org/packages/box/brainy)

![Brainy](logo.png "Brainy")
# Brainy

Brainy is a replacement for the popular [Smarty](http://www.smarty.net/)
templating language. It is a fork from the Smarty 3 trunk.

Brainy is still very new and it's likely that you will encounter some issues.
Please report any problems that you encounter.


## Why Brainy?

- Brainy generates clean and fast code by default.
- Brainy has security defaults that align better with [best practices](http://www.phptherightway.com/#security).
- Brainy does not include features that are infrequently used and increase code bloat.

Because Brainy is a fork of Smarty 3, it shares much of the same syntax and features while eliminating dangerous footguns and making it hard to write bad code.


## Getting Started

Check out the [Getting Started](https://github.com/box/brainy/wiki/Getting-Started)
page on the wiki.


### Minimum Requirements

- PHP 7.4+
- `mbstring` PHP extension

The `mbstring` extension is required in order to properly support Unicode in templates and user-provided content. Brainy 3 cannot be run in a mode that does not handle Unicode properly.


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
- Refactoring:
  - Eliminate dead code
  - `@` error suppression
  - Increase code coverage
  - etc.
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
