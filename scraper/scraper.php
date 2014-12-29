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
	public function __construct($id = false)
	{
		global $logging;

		if ($id && is_numeric($id))
		{
			//Message
			$logging->info("Set IMDB ID (" . $id . ")");

			$this->imdbId = $id;
		}
	}

	public function file($url, $quality, $retriever)
	{
		global $logging;

		try
		{
			//Message
			$logging->info("Torrent");
			$torrent = new torrent;
			$filename = $torrent->file($url);

			//Message
			$logging->info("Scrape");
			$torrent->scrape();

			//Message
			$logging->info("IMDB");
			$imdb = new imdb;
			$imdb->getInfo($this->imdbId);

			//Get subtitle from OpenSubtitles
			$oSubtitles = new openSubtitles();
			$oSubtitles->retrieve($this->imdbId, $filename);

			//Message
			$logging->info("Torrent DB");
			$torrent->database(true, $this->imdbId, $quality, $retriever, "0");

			//Message
			$logging->info("Movie DB");
			$imdb->database();
		}
		catch (exception $e)
		{
			//Message
			$logging->error($e->getMessage());
		}
	}
}
