all: parsers test

parsers:
	cd build/parsers; make all

test: clean
	phpunit

test-hhvm: clean
	cat `which phpunit` | grep '/usr/bin/env php' | sed 's/ php / hhvm --php /' | sed 's/\$\*//' | /usr/bin/env sh

lint:
	# find src -name "*.php" -exec php -l {} \; | grep "^(?!No syntax errors)"
	phpcs -v --standard=build/phpcs-ruleset.xml src/

clean:
	rm -rf test/cache/*.php test/compiled/*.php
