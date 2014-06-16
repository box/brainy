all: parsers test

parsers:
	cd build/parsers; make all

test: clean
	phpunit

test-hhvm: clean
	./test/_phpunit-tests-hhvm.sh

clean:
	rm -rf test/compiled/*.php
