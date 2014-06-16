all: parsers test

parsers:
	cd build/parsers; make all

test:
	cd development; make unit-test

test-hhvm:
	cd development; make unit-test-hhvm
