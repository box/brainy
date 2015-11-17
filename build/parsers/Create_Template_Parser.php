<?php
ini_set('max_execution_time', 300);
ini_set('xdebug.max_nesting_level', 300);

// Create Lexer
require_once './LexerGenerator.php';
$lex = new PHP_LexerGenerator('Lexer.plex');
$contents = file_get_contents('Lexer.php');
$contents = str_replace(array('SMARTYldel', 'SMARTYrdel'), array('".$this->ldel."', '".$this->rdel."'), $contents);
file_put_contents('Lexer.php', $contents);

// Create Parser
require_once './ParserGenerator.php';
$me = new PHP_ParserGenerator();
$me->main('Parser.y');

$contents = file_get_contents('Parser.php');
$contents = '<?php
/**
 * Brainy Internal Plugin Templateparser
 *
 * This is the template parser.
 * It is generated from the Parser.y file
 * @package Brainy
 * @subpackage Compiler
 * @author Uwe Tews
 * @author Matt Basta
 */

namespace Box\Brainy\Compiler;

' . substr($contents, 6);
file_put_contents('Parser.php', $contents);
