all: parsers test

parsers:
	cd build/parsers; make all

test: clean
	mkdir -p test/cache
	mkdir -p test/compiled
	phpunit

test-hhvm: clean
	mkdir -p test/cache
	mkdir -p test/compiled
	./phpunit-hhvm

coverage: clean
	mkdir -p coverage
	phpunit --coverage-html ./coverage

lint:
	# find src -name "*.php" -exec php -l {} \; | grep "^(?!No syntax errors)"
	phpcs -v --standard=build/phpcs-ruleset.xml src/

clean:
	rm -rf test/cache/*.php test/compiled/*.php

docs: clean
	vendor/bin/phpdoc -d src/Brainy -t docs/ --ignore "*_internal_compile_*,*_internal_*parser.php,*_internal_*lexer.php,*/plugins/*"

docs-branch: coverage docs
	git branch -D gh-pages
	git checkout -b gh-pages
	git rm -rf build src test
	mv docs/* .
	git add .
	git add -f coverage
	git commit -a -u -m "Docs update"
	git checkout master
	git clean -f -d
	echo "Run git push -f origin gh-pages"
