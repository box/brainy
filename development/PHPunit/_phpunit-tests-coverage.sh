#!/bin/sh
php -d asp_tags=On /usr/local/bin/phpunit --coverage-html coverage _runAllTests.php > _results_c.txt
