<?php /* index.php ( resURL implementation ) */
ini_set("default_charset", "UTF-8");

require_once 'includes/conf.php'; // <- site-specific settings
require_once 'includes/resurl.php'; // <- resURL class file
require_once 'Net/DNSBL/SURBL.php'; // <- URL blacklisting

if( $language[0] != 'en' ) {
    switch( $language[0] ) {
	case "ru":
	    putenv('LC_ALL=ru_RU');
	    setlocale(LC_MESSAGES, 'ru_RU');
	break;
    }
    
    $domain = "resurl";
    bindtextdomain($domain, "./locale");
    bind_textdomain_codeset($domain, 'UTF-8');
    textdomain($domain);
}

$msg = '';
$resurl = new resURL();
// if the form has been submitted
if ( isset($_POST['longurl']) )
{
	// This is a write transaction, use the master database


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
			exit();
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
		<link rel="stylesheet" type="text/css" href="style.css" />
	</head>
	
	<body onload="document.getElementById('longurl').focus()">
	    <div id="header">
		<img src="/li8ru.png" style="width:125px;height:55px;float:left;vertical-align:baseline;" />
		<h1><?php echo _('Short URL Generator'); ?></h1>
		<br style="clear: both" />
		
		<?php echo $msg; ?>
		
	    </div>
		<br style="clear: both" />
		<form action="/" method="post">
		
			<fieldset>
				<label for="longurl"><?php echo _('Enter a long URL:')?></label>
				<input type="text" name="longurl" id="longurl" />
				<input type="submit" name="submit" id="submit" value="<?php echo _('Make Short URL!')?>" />
			</fieldset>
		
		</form>
		
		<br />
		<div id="banner">
<script type="text/javascript"><!--
google_ad_client = "ca-pub-1492774486618114";
/* li8 728&#42;90 */
google_ad_slot = "9318323210";
google_ad_width = 728;
google_ad_height = 90;
//-->
</script>
<script type="text/javascript"
src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
</script>
		</div>
		<div id="top_sites">
			<h3><?php echo _('Top viewed sites')?></h3>
			<ul>
	    <?php 
			$top_sites = $resurl->get_top_sites(10);
			foreach( $top_sites as $id => $site ) {
				$urs = parse_url($site['url']);
				?>
					<li id="site_<?php echo $id?>"><?php echo '<a target="_blank" href="'.$site['url'].'">'.$urs['host'].substr($urs['path'],0, 5).'...'.substr($urs['path'],-4).'</a> '; echo _('Views:'); echo $site['views'];?> </li>
				<?
			}
	    ?>
			</ul>
		</div>

		<p class="license">
	          <?php echo  _('<a href="http://li8.ru/">li8</a> is an Open Service from <a href="http://fasqu.com/">FASQu Inc.</a>, powered by <a href="http://li8.ru/2">resURL</a>. Code is based on <a href="http://lilurl.sourceforge.net/">lilURL</a>.') ?>
	        </p>
	    
	</body>
</html>
		
