<?php

/**
 * @author Kevin
 * @copyright 2014
 * @info Get movies from Torrentz.eu
 */
class torrentz
{
	//Movie counter
	private $iMovie = 1;

	//URL
	private $URL;

	//Start, end and year
	private $start;
	private $end;
	private $year;

	//Movie list
	private $movieList = array();

	public function __construct($startPage = 1, $endPage = 200, $year = false)
	{
		global $logging, $cache;

		//Message
		$logging->info("Initializing Torrentz (" . $startPage . " until " . $endPage . ")");

		//Start and end page
		$this->start = $startPage;
		$this->end = $endPage;

		//Torrentz URL
		if ($year)
		{
			$this->URL = "http://torrentz.eu/verified?f=movies+" . $year . "&p=";
		} else
		{
			$this->URL = "http://torrentz.eu/verified?f=movies&p=";
		}

		//Regex class
		$regex = new regex();

		//The loop
		for ($i = $startPage; $i <= $endPage; $i++)
		{
			list($state, $data) = cURL($this->URL . $i);

			if ($state)
			{
				//Movies from page
				$movieData = $regex->torrentz("movie", $data);

				//Find movies
				if ($movieData[1] && is_array($movieData[1]))
				{
					//Message
					$logging->info("Extracting page (" . $i . ")");

					//Combine hash and title
					$combined = array_combine($movieData[1], $movieData[2]);

					//Return list
					$this->movieList = array_merge($combined, $this->movieList);
				} //Empty page
				else
				{
					//Message
					$logging->warning("No data retrieved from Torrentz (" . $i . ")");
				}
			}
		}
	}

	public function movies()
	{
		global $logging, $locker;

		//Message
		$logging->info("Starting Torrentz (" . $this->start . " until " . $this->end . ")");

		//Regex class
		$regex = new regex();

		//Try..
		try
		{
			//The loop
			foreach ($this->movieList as $hash => $title)
			{
				//Remove HTML tags from title
				$title = strip_tags($title);

				//Convert &#[0-9]+ entities to UTF-8
				$title = preg_replace_callback("/(&#[0-9]+;)/", function ($m)
				{
					return mb_convert_encoding($m[1], "UTF-8", "HTML-ENTITIES");
				}, $title);

				//Convert other characters, &amp; e.g.
				$title = htmlspecialchars_decode($title);

				//Only if year
				$year = $regex->main("year", $title, 1);

				if ($year && is_numeric($year))
				{
					//Only non-cam/ts
					if (!$regex->torrentz("illegal", $title, 1))
					{
						//Get quality
						$quality = self::quality($title);

						//Only quality
						if ($quality)
						{
							//Clean title
							$cleanTitle = $regex->torrentz("cleanTitle", $title, 1);

							print($hash . " - " . $quality . " - " . $cleanTitle . " - ");
							print($regex->main("year", $title, 1) . " - ");
							print("\n");

							//Get IMDB ID

							//Now send it to the scraper!
						}
					}
				} else
				{
					//Message
					$logging->warning("No year found (" . $title . ")");
				}
			}
		}
			//Error reporting
		catch (exception $e)
		{
			throw new Exception($e->getMessage());
		}
	}

	//Get quality out of the title
	private function quality($title)
	{
		//Sorted qualities, from high to low
		$qualities = array("3D",
			"1080p",
			"720p",
			"HD[ |-]?Rip",
			"WEB[ |-]?DL",
			"WEB[ |-]?Rip",
			"WEB[ |-]?HD",
			"DVD[ |-]?Rip");

		//Flip/reverse array
		$qualities = array_reverse($qualities);

		$regex = new regex();

		//Loop through qualities
		foreach ($qualities as $quality)
		{
			$result = $regex->match("~\b(" . $quality . ")\b~i", $title, 1);

			if ($result)
			{
				//Remove regex code
				$quality = str_replace("[ |-]?", "-", $quality);

				return $quality;
			}
		}

		//Default
		return false;
	}

	//Get trackers from site
	private function fetch($hash)
	{
		global $logging;

		//Default
		$tData = array();
		$pData = array();

		//Regex class
		$regex = new regex();

		//Message
		$logging->info("Fetching Trackers (Torrentz " . $hash . ")");

		list($state, $tData) = cURL("http://torrentz.eu/" . $hash);

		//Extract trackers <div>
		$trackerData = $regex->torrentz("trackerlist", $tData, 1);

		//Tracker list
		$trackerlist = $regex->torrentz("tracker", $trackerData);

		//Check if there are any trackers
		if (isset($trackerlist[1]))
		{
			$tData = $trackerlist[1];
		} //Failed
		else
		{
			//Message
			$logging->warning("No trackers found (Torrentz)");
		}

		//Message
		$logging->info("Fetching Trackers (TorrentProject " . $hash . ")");

		list($state, $json) = cURL("http://torrentproject.se/" . $hash . "/trackers_json");

		//Decode data
		$jsonData = json_decode($json, true);

		//Check if there are any trackers
		if ($jsonData)
		{
			$pData = $jsonData;
		} //Failed
		else
		{
			//Message
			$logging->warning("No trackers found (TorrentProject)");
		}

		//Merge both arrays
		$combined = array_merge($tData, $pData);

		//Remove duplicates
		$unique = array_unique($combined);

		//Send the merged array back
		return $unique;
	}
}