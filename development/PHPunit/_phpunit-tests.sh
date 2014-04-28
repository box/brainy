#!/bin/sh
php -d asp_tags=On /usr/bin/phpunit --verbose _runAllTests.php > _results.txt
