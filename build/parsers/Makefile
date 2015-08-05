all: smarty_internal_templatelexer.php smarty_internal_templateparser.php
	cp smarty_internal_templatelexer.php ../../src/Brainy/sysplugins/smarty_internal_templatelexer.php
	cp smarty_internal_templateparser.php ../../src/Brainy/sysplugins/smarty_internal_templateparser.php

smarty_internal_templatelexer.php: smarty_internal_templatelexer.plex
	php Create_Template_Parser.php

smarty_internal_templateparser.php: smarty_internal_templateparser.y
	php Create_Template_Parser.php

clean:
	rm -f smarty_internal_parser.out smarty_internal_parser.php
