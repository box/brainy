all: parsers unit-test unit-test-hhvm

parsers:
	cd lexer; make all

unit-test:
	rm -rf PHPunit/templates_c/*.php
	cd PHPunit; ./_phpunit-tests.sh

unit-test-hhvm:
	rm -rf PHPunit/templates_c/*.php
	cd PHPunit; ./_phpunit-tests-hhvm.sh
