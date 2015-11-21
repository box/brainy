all: parsers autoload test

autoload:
	composer dump-autoload

parsers: src/Brainy/Compiler/Lexer.php src/Brainy/Compiler/Parser.php clean

src/Brainy/Compiler/Lexer.php: build/parsers/Lexer.plex
	cd build/parsers/ && php Create_Template_Parser.php
	mv build/parsers/Lexer.php src/Brainy/Compiler/Lexer.php

src/Brainy/Compiler/Parser.php: build/parsers/Parser.y
	cd build/parsers/ && php Create_Template_Parser.php
	mv build/parsers/Parser.php src/Brainy/Compiler/Parser.php


test: clean
	mkdir -p test/compiled
	./vendor/bin/phpunit

test-hhvm: clean
	mkdir -p test/compiled
	./phpunit-hhvm

coverage: clean
	mkdir -p coverage
	./vendor/bin/phpunit --coverage-html ./coverage

lint:
	# find src -name "*.php" -exec php -l {} \; | grep "^(?!No syntax errors)"
	phpcs -v --standard=build/phpcs-ruleset.xml src/

clean:
	rm -rf test/compiled/*
	rm -f build/parsers/*.out
	rm -f build/parsers/Lexer.php build/parsers/Parser.php

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
