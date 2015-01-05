<?php

/**
* @author Kevin
* @copyright 2014
* @info Cache data/URL
*/

class cache
{
	//File new, or?
	private $cached;
	
	//Unique ID
	private $id;
	
	//Check if cached and cache is valid
	public function __construct($id)
	{
		global $logging, $cache;

		//If cache exists
		if (file_exists($cache . $id))
		{
			$future = filemtime($cache . $id) + (60 * 1440 * 7 * 4);
			$now = time();

			//Must not be older than one month
			if ($future > $now)
			{
				$this->cached = true;
				
				//Message
				$logging->info("Read cache (" . $id . ")");
			}
			//Too old
			else
			{
				$this->cached = false;
				
				//Message
				$logging->warning("Cache is too old (" . $id . ")");
			}
		}
		//Not found
		else
		{
			$this->cached = false;
			
			//Message
			$logging->debug("Cache doesn't exists (" . $id . ")");
		}
		
		//Set ID
		$this->id = $id;
	}
	
	//Cache data via an URL
	public function url($url)
	{
		global $logging, $cache;
		
		//Read
		if($this->cached)
		{			
			return self::read();
		}
		//Retrieve and save
		else
		{
			//Message
			$logging->info("Get new cache (" . $this->id . ")");

			//Cache doesn't exist
			list($state, $content) = cURL($url);

			//Save cache
			if ($state)
			{
				self::save($content);
				
				return $content;
			}
			//No data found
			else
			{
				//Message
				$logging->error("No data retrieved (" . $url . " - " . $this->id . ")");

				return false;
			}
		}		
	}
	
	//Cache given data
	public function content($content)
	{
		global $logging, $cache;
		
		//Read
		if($this->cached)
		{			
			return self::read();
		}
		//Retrieve and save
		else
		{
			//Message
			$logging->info("Get new cache (" . $this->id . ")");

			self::save($content);
			
			return $content;
		}		
	}
	
	//Read cached content
	private function read()
	{
		global $cache;

		return file_get_contents($cache . $this->id);
	}
	
	//Save cached content
	private function save($content)
	{
		global $logging, $cache;		
		
		$state = file_put_contents($cache . $this->id, $content, LOCK_EX);

		//OK
		if ($state)
		{
			//Message
			$logging->info("Cache saved (" . $this->id . ")");
		}
		//Failed
		else
		{
			//Message
			$logging->error("Couldn't save cache (" . $this->id . ")");
		}
	}
}

?>