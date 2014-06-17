all: parsers test

parsers:
	cd build/parsers; make all

test: clean
	phpunit

test-hhvm: clean
	./phpunit-hhvm

lint:
	# find src -name "*.php" -exec php -l {} \; | grep "^(?!No syntax errors)"
	phpcs -v --standard=build/phpcs-ruleset.xml src/

clean:
	rm -rf test/cache/*.php test/compiled/*.php
