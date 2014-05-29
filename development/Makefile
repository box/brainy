all: parsers unit-test

parsers:
	cd lexer; make all

unit-test:
	rm -rf PHPunit/templates_c/*.php
	cd PHPunit; ./_phpunit-tests.sh
