all: Lexer.php Parser.php
	cp Lexer.php ../../src/Brainy/Compiler/Lexer.php
	cp Parser.php ../../src/Brainy/Compiler/Parser.php
	rm -f *.out

Lexer.php: Lexer.plex
	php Create_Template_Parser.php

Parser.php: Parser.y
	php Create_Template_Parser.php

clean:
	rm -f Parser.out Parser.php Lexer.php Lexer.out
