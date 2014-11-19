<?php

/**
* @author Kevin
* @copyright 2014
* @info Cache IMDB information
*/

function cache($id,$url) 
{
	global $cache;
	
	//If cache exists
	if(file_exists($cache.$id))
	{
		$made = filemtime($cache.$id);
		$now = time();
		$diff = round(abs($now - $made) / 60);
		
		//Must not be older than one month
		if ($diff > (1440*7*4))
		{		
			return file_get_contents($cache.$id);
		}
	}
	
	//Cache doesn't exist
	list($content, $state) = cURL($url);
	
	//Save cache
	if($state)
	{		
		$cacheState = file_put_contents($cache.$id, $content, LOCK_EX);
		
		//OK
		if($cacheState)
		{
			return $content;
		}
		//Failed
		else
		{
			return false;
		}			
	}
}