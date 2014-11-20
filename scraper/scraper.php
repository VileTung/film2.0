<?php

/**
* @author Kevin
* @copyright 2014
* @info Scraper, binds the needed functions together!
*/

class scraper
{
	//IMDB ID
	private $imdbId;
	
	//Constructor
	public function __construct($id=false)
	{
		var_dump($id);
		
		
		if($id && is_numeric($id))
		{
			$this->imdbId = $id;
		}
		
		var_dump($this->imdbId);
	}
	
	public function file($url, $quality, $retriever)
	{
		try
		{
			print("Torrent\n");
			$torrent = new torrent;
			$torrent->file($url);
			
			print("Scrape\n");
			$torrent->scrape();
			
			print("IMDB\n");
			$imdb = new imdb;
			$imdb->getInfo($this->imdbId);
			
			print("Database 1\n");
			$torrent->database(true, $this->imdbId, $quality, $retriever, "0");
			
			print("Database 2\n");
			$imdb->database();
			
			print("Klaar\n");
		}
		catch(Exception $e)
		{
			print($e->getMessage());
		}
	}
}