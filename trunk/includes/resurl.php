<?php /* resurl.php ( resURL class file ) */

class resURL
{

	var $chars = "0123456789_abcdefghijklmnopqrstuvwxyz-ABCDEFGHIJKLMNOPRSTUVWXYZ";
	
	
	
	// constructor
	function resURL()
	{
		global $rediska;
		$this->rediska = $rediska;
		
		
	}

	// return the id for a given url (or -1 if the url doesn't exist)
	function get_id($url)
	{
		$key = new Rediska_Key(sha1($url));
		$id = $key->getValue();
		return $id;
	}

	// return the url for a given id (or -1 if the id doesn't exist)
	function get_url($id)
	{
		$id_key = new Rediska_Key($id);
		$url = $id_key->getValue();
		if( !is_null($url))	{
			$this->increment_views($id);	
			return $url;
		} else 
			return -1;
			
		
	}
	
	// add a url to the database
	function add_url($url)
	{
		// check to see if the url's already in there
		$id = $this->get_id($url);
		
		// if it is, return true
		if ( !is_null($id) )
		{
			return true;
		}
		else // otherwise, put it in
		{
			$id = $this->get_next_id($this->get_last_id());
			$id_key = new Rediska_Key($id);
			$url_key = new Rediska_Key(sha1($url));
			$id_key->setValue( $url );
			$url_key->setValue( $id );
			return true;
		}
	}

	// return the most recent id (or -1 if no ids exist)
	function get_last_id()
	{	
		$last_key = new Rediska_Key('last_id_key');
		return $last_key->getValue();
	}	

	// save the most recent id
	function set_last_id($last_id)
	{	
		$last_key = new Rediska_Key('last_id_key');
		return $last_key->setValue($last_id);
	}	

	// return the next id
	function get_next_id($last_id)
	{ 
	
		// if the last id is -1 (non-existant), start at the begining with 0
		if ( is_null($last_id) )
		{
			$next_id = 0;
		}
		else
		{
			// loop through the id string until we find a character to increment
			for ( $x = 1; $x <= strlen($last_id); $x++ )
			{
				$pos = strlen($last_id) - $x;

				if ( substr($last_id,$pos,1) != substr($this->chars,-1) )
				{
					$next_id = $this->increment_id($last_id, $pos);
					break; // <- kill the for loop once we've found our char
				}
			}

			// if every character was already at its max value (z),
			// append another character to the string
			if ( !isSet($next_id) )
			{
				$next_id = $this->append_id($last_id);
			}
		}

		// check to see if the $next_id we made already exists, and if it does, 
		// loop the function until we find one that doesn't
		//
		// (this is basically a failsafe to get around the potential dangers of
		//  my kludgey use of a timestamp to pick the most recent id)
		$next_id_key = new Rediska_Key($next_id);
		
		if ( $next_id_key->isExists() )
		{
			$next_id = $this->get_next_id($next_id);
		}
		$this->set_last_id($next_id);
		return $next_id;
	}

	// make every character in the string 0, and then add an additional 0 to that
	function append_id($id)
	{
		for ( $x = 0; $x < strlen($id); $x++ )
		{
			$id[$x] = 0;
		}

		$id .= 0;

		return $id;
	}

	// increment a character to the next alphanumeric value and return the modified id
	function increment_id($id, $pos)
	{		
		//var_dump($id);
		//var_dump($pos);
		$char = mb_substr($id, $pos,1);
		
		//var_dump($char);
		$n = mb_strpos($this->chars, $char);
		//var_dump($n);
		$n++;
		$new_char = mb_substr($this->chars,$n,1);

		//var_dump($new_char);
		//var_dump($id);
		$id = substr_replace($id, $new_char, $pos, 1);
		//echo $id;
		//exit();
		// set all characters after the one we're modifying to 0
		if ( $pos != (strlen($id) - 1) )
		{
			for ( $x = ($pos + 1); $x < strlen($id); $x++ )
			{
				$id{$x} = 0;
			}
		}
		//sleep(5);	
		return $id;
	}
	
	
	//Count views for $id
	function increment_views($id) 
	{
			//add increment for count views
			$id_views_key = new Rediska_Key($id.":Views");
			$id_views_key->increment();		
	}
	

}

?>
