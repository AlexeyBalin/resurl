<?php /* index.php ( resURL implementation ) */
ini_set("default_charset", "UTF-8");
set_include_path(get_include_path().":".$_SERVER['INCLUDE_PATH']);


require_once 'includes/conf.php'; // <- site-specific settings
require_once 'includes/resurl.php'; // <- resURL class file

require_once 'Net/DNSBL/SURBL.php'; // <- URL blacklisting

setlocale(LC_MESSAGES, 'ru_RU');
$domain = "resurl";

bindtextdomain($domain, "./locale");
textdomain($domain);


$msg = '';

// if the form has been submitted
if ( isset($_POST['longurl']) )
{
	// This is a write transaction, use the master database
	$resurl = new resURL();

	// escape bad characters from the user's url
	$longurl = trim(mysql_escape_string($_POST['longurl']));

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
		

	$surbl = new Net_DNSBL_SURBL();

	if ($surbl->isListed($longurl))
	{
		$msg = '<p class="error">'._('Blacklisted URL!').'</p>';
	}
	elseif ( $protocol_ok && $resurl->add_url($longurl) ) // add the url to the database
	{
		if ( REWRITE ) // mod_rewrite style link
		{
			$url = 'http://'.$_SERVER['HTTP_HOST'].'/'.$resurl->get_id($longurl);
		}
		else // regular GET style link
		{
			$url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?id='.$resurl->get_id($longurl);
		}

		$msg = '<p class="success">'._('Short URL is:').'<a href="'.$url.'">'.$url.'</a></p>';
	}
	elseif ( !$protocol_ok )
	{
		$msg = '<p class="error">'._('Invalid protocol!').'</p>';
	}
	else
	{
		$msg = '<p class="error">'._('Creation of your li8 url failed for some reason.').'</p>';
	}
}
else // if the form hasn't been submitted, look for an id to redirect to
{

	// This is a read transaction, use the slave database
	$resurl = new resURL();

	if ( isSet($_GET['id']) ) // check GET first
	{
		$id = mysql_escape_string($_GET['id']);
	}
	elseif ( REWRITE ) // check the URI if we're using mod_rewrite
	{
		$explodo = explode('/', $_SERVER['REQUEST_URI']);
		$id = mysql_escape_string($explodo[count($explodo)-1]);
	}
	else // otherwise, just make it empty
	{
		$id = '';
	}
	
	// if the id isn't empty and it's not this file, redirect to it's url
	if ( $id != '' && $id != basename($_SERVER['PHP_SELF']) )
	{
		$location = $resurl->get_url($id);
		
		if ( $location != -1 )
		{
			header('Location: '.$location);
		}
		else
		{
			$msg = '<p class="error">Sorry, but that ur1 isn\'t in our database.</p>';
		}
	}
}

// print the form

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html>

	<head>
		<title><?php echo _('Short URL Generator'); ?></title>
		
		<style type="text/css">
		body {
			font: .8em, "Trebuchet MS", Verdana, Arial, Helvetica, sans-serif;
			text-align: center;
			color: #333;
			background-color: #fff;
			margin-top: 5em;
		}
		
		h1 {
			font-size: 2em;
			padding: 0;
			margin: 0;
		}

		form {
			width: 28em;
			background-color: #eee;
			border: 1px solid #ccc;
			margin-left: auto;
			margin-right: auto;
			padding: 1em;
		}

		fieldset {
			border: 0;
			margin: 0;
			padding: 0;
		}
		
		a {
			color: #09c;
			text-decoration: none;
			font-weight: bold;
		}

		a:visited {
			color: #07a;
		}

		a:hover {
			color: #c30;
		}

		.error, .success {
			font-size: 1.2em;
			font-weight: bold;
		}
		
		.error {
			color: #ff0000;
		}
		
		.success {
			color: #000;
		}
		
		p.license {
			margin-left: auto;
			margin-right: auto;
			font-size: 1em;
			font-weight: bold;
			width: 300px;
		}
		
		</style>

	</head>
	
	<body onload="document.getElementById('longurl').focus()">
		
		<h1><?php echo _('Short URL Generator'); ?></h1>
		
		<?php echo $msg; ?>
		
		<form action="/" method="post">
		
			<fieldset>
				<label for="longurl"><?php echo _('Enter a long URL:')?></label>
				<input type="text" name="longurl" id="longurl" />
				<input type="submit" name="submit" id="submit" value="<?php echo _('Make Short URL!')?>" />
			</fieldset>
		
		</form>

		<p class="license">
	          <?php echo  _('<a href="http://li8.ru/">li8</a> is an Open Service from <a href="http://fasqu.com/">FASQu Inc.</a>, powered by <a href="http://li8.ru/2">resURL</a>. Code is based on <a href="http://ur1.ca">ur1.ca</a>.') ?>
	        </p>
	</body>
</html>
		
