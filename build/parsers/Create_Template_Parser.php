<?php
// require_once(dirname(__FILE__)."/../dev_settings.php");
ini_set('max_execution_time', 300);
ini_set('xdebug.max_nesting_level', 300);

// Create Lexer
require_once './LexerGenerator.php';
$lex = new PHP_LexerGenerator('smarty_internal_templatelexer.plex');
$contents = file_get_contents('smarty_internal_templatelexer.php');
$contents = str_replace(array('SMARTYldel', 'SMARTYrdel'), array('".$this->ldel."', '".$this->rdel."'),$contents);
file_put_contents('smarty_internal_templatelexer.php', $contents);

// Create Parser
passthru("$smarty_dev_php_cli_bin ./ParserGenerator/cli.php smarty_internal_templateparser.y");

$contents = file_get_contents('smarty_internal_templateparser.php');
$contents = '<?php
/**
 * Brainy Internal Plugin Templateparser
 *
 * This is the template parser.
 * It is generated from the smarty_internal_templateparser.y file
 * @package Brainy
 * @subpackage Compiler
 * @author Uwe Tews
 * @author Matt Basta
 */
' . substr($contents, 6);
file_put_contents('smarty_internal_templateparser.php', $contents);
