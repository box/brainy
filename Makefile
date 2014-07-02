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

docs-branch:
	git branch -D gh-pages
	git checkout -b gh-pages
	git rm -rf build src test
	mv docs/* .
	git add .
	git commit -a -u -m "Docs update"
	git checkout master
	git clean -f -d
	echo "Run git push -f origin gh-pages"
