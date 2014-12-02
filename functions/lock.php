<?php

/**
* @author Kevin
* @copyright 2014
* @info Locks a session, also makes it stoppable
*/

class locker
{
	private $session;
	
	//Constructor
	public function __construct()
	{
		global $cache;
		
		//Generate session ID
		$this->session = $session = mt_rand(10000,65535);
		
		//Create lock file
		$sessionFile = fopen($cache."lock_".$session, "w");
		fclose($sessionFile);
		
		//Make sure everything went OK
		if(!file_exists($cache."lock_".$session))
		{
			throw new Exception("Couldn't create a lock!");
		}
	}
	
	//Get session ID, don't know why..
	public function getSession()
	{
		return $this->session;
	}
	
	//Check if session.lock still exists, otherwise, exit!
	public function check()
	{
		global $logging, $cache;
		
		if(!file_exists($cache."lock_".$this->session))
		{
			throw new Exception("Stopping, ".$this->session.".lock doesn't exist");
		}
		else
		{
			//Message
			$logging->debug("Process continues (".$this->session.")");
		}
	}
	
	//Regular exit
	public function stop()
	{
		global $logging, $cache;
		
		unlink($cache."lock_".$this->session);
		
		//Message
		$logging->info("Process stopped (".$this->session.")");
	}
}

?>