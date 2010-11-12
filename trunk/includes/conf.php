<?php /* conf.php ( config file ) */

require_once 'includes/Rediska.php'; 

//get Browser Language
function getAcceptedLanguage() {
    if( !isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ) {
	return false;
    }
    $languages = split(",", $_SERVER['HTTP_ACCEPT_LANGUAGE'] );
    $lang_q = Array();
    foreach( $languages as $aLang ) {
	$lang_array = split(";q=", trim( $aLang ) );
        $lang = trim( $lang_array[0] );
        if( !isset( $lang_array[1] ) )
    	    $q = 1;
	else
            $q = trim($lang_array[1]);
            $lang_q["$lang"] = (float)$q;
    }
    
    arsort($lang_q);
    $i = 0;
    $lang_index = Array();
    foreach($lang_q as $lang => $q) {
	$lang_index[$i] = $lang; //add to a new array the index key/language
	$i++;
    }
     return $lang_index;
} // end of 'getAcceptedLanguage()'


// <- site-specific settings
//Use autodetect, or you can set your language
$language = getAcceptedLanguage();

$options = array(
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
