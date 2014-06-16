<?php
require_once(dirname(__FILE__)."/../dev_settings.php");
// Create Lexer
require_once './LexerGenerator.php';
$lex = new PHP_LexerGenerator('smarty_internal_configfilelexer.plex');
$contents = file_get_contents('smarty_internal_configfilelexer.php');
file_put_contents('smarty_internal_configfilelexer.php', $contents . "\n");

// Create Parser
passthru("$smarty_dev_php_cli_bin ./ParserGenerator/cli.php smarty_internal_configfileparser.y");

$contents = file_get_contents('smarty_internal_configfileparser.php');
$contents = '<?php
/**
* Brainy Internal Plugin Configfileparser
*
* This is the config file parser.
* It is generated from the internal.configfileparser.y file
* @package Brainy
* @subpackage Compiler
* @author Uwe Tews
* @author Matt Basta
*/
' . substr($contents, 6);
file_put_contents('smarty_internal_configfileparser.php', $contents);
