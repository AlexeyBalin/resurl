<?php /* index.php ( resURL implementation ) */
ini_set("default_charset", "UTF-8");

require_once 'includes/conf.php'; // <- site-specific settings
require_once 'includes/resurl.php'; // <- resURL class file

$resurl = new resURL();
$msg = '';

// if the form has been submitted
if ( isset($_REQUEST['longurl']) )
{
	// escape bad characters from the user's url
	$longurl = trim(mysql_escape_string($_REQUEST['longurl']));

	// set the protocol to not ok by default
	$protocol_ok = false;
	
	// if there's a list of allowed protocols, 
	// check to make sure that the user's url uses one of them
	if ( count($allowed_protocols) )
	{
		foreach ( $allowed_protocols as $ap )
		{
			if ( strtolower(substr($longurl, 0, strlen($ap))) == strtolower($ap) )
			{
				$protocol_ok = true;
				break;
			}
		}
	}
	else // if there's no protocol list, screw all that
	{
		$protocol_ok = true;
	}
		
	// add the url to the database
	if ( $protocol_ok && $resurl->add_url($longurl) )
	{
		$id = $resurl->get_id($longurl);
		if ( REWRITE ) // mod_rewrite style link
		{
			$url = 'http://'.$_SERVER['HTTP_HOST'].'/'.$id;
		}
		else // regular GET style link
		{
			$url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?id='.$id;
		}
		if( $_REQUEST['format'] == 'json' ) {
		    $response = json_encode( array('url' => $url) );
		    if( isset($_REQUEST['callback']) ) {
			echo $_REQUEST['callback']."(".$response.")";
		    }else{
			echo $response;
		    }
		} else {
		    echo $url;
		}
		exit();
	}
	elseif ( !$protocol_ok )
	{
		echo 'Invalid protocol!';
		exit();
	}
	else
	{
		echo 'Creation of your ur1 failed for some reason.';
		exit();
	}
}
else // if the form hasn't been submitted, look for an id to redirect to
{
    header("Location: /");
}
?>