<?php

/**
* @author Kevin
* @copyright 2014
* @info Get movies from YTS
*/

class yts
{
	public $iMovie = 0;
	
	//Constructor
	public function __construct($startPage = 1, $endPage=200)
	{
		//The loop
		for($i = $startPage; $i <= $endPage; $i++)
		{
			//JSON encoded data
			list($state, $json) = cURL("https://yts.re/api/list.json?limit=50&set=".$i);
			
			if($state)
			{
				//Decode data
				$data = json_decode($json,true);
				
				//Determine if done..
				if(isset($data["status"]))
				{
					throw new Exception("No new data available (".$i.")");
				}
				
				//Loop through movies
				foreach($data["MovieList"] as $movie)
				{				             
					//IMDB ID
					$id = preg_replace("~\D~", "", $movie["ImdbCode"]);
					
					//Gegevens verwerken
					$scraper = new scraper($id);
					$scraper->file($movie["TorrentUrl"],$movie["Quality"],"yts");
					
					//Movie counter
					$this->iMovie++;
				}
			}
		}
	}
}

?>