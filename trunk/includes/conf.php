<?php /* conf.php ( config file ) */

require_once $_SERVER['INCLUDE_PATH'].'/classes/Rediska.php'; // <- site-specific settings

$options = array(
	//'rediska' =>'resurl_',
	'namespace' => 'resurl_'
);
$rediska = new Rediska($options);

$allowed_symbols = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPRSTUVWXYZ.-_";

// use mod_rewrite?
define('REWRITE', true);

// allow urls that begin with these strings
$allowed_protocols = array('http:', 'https:', 'mailto:');

// uncomment the line below to skip the protocol check
// $allowed_procotols = array();

?>
