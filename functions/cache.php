<?php

/**
* @author Kevin
* @copyright 2014
* @info Cache IMDB information
*/

function cache($id,$url) 
{
	global $logging, $cache;
	
	//If cache exists
	if(file_exists($cache.$id))
	{
		$made = filemtime($cache.$id);
		$now = time();
		$diff = round(abs($now - $made) / 60);
		
		//Must not be older than one month
		if ($diff > (1440*7*4))
		{
			//Message
			$logging->info("Read cache (".$id.")");
			
			return file_get_contents($cache.$id);
		}
	}
	
	//Message
	$logging->info("Get new cache (".$id.")");
	
	//Cache doesn't exist
	list($state, $content) = cURL($url);
	
	//Save cache
	if($state)
	{
		$cacheState = file_put_contents($cache.$id, $content, LOCK_EX);
		
		//OK
		if($cacheState)
		{
			//Message
			$logging->info("Cache saved (".$id.")");
			
			return $content;
		}
		//Failed
		else
		{
			//Message
			$logging->error("Couldn't save cache (".$id.")");
			
			return false;
		}			
	}
}

?>