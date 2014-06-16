all: parsers test

parsers:
	cd build/parsers; make all

test: clean
	rm -rf PHPunit/templates_c/*.php
	cd PHPunit; ./_phpunit-tests.sh

test-hhvm: clean
	rm -rf PHPunit/templates_c/*.php
	cd PHPunit; ./_phpunit-tests-hhvm.sh

clean:
	rm -rf test/templates_c/*.php
