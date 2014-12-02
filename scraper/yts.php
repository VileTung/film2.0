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
		global $logging, $locker;
		
		//Message
		$logging->info("Starting YTS (".$startPage." until ".$endPage.")");
		
		//Try..
		try
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
						throw new Exception("No new data available (".$i." of the ".$endPage.")");
					}
					
					//Loop through movies
					foreach($data["MovieList"] as $movie)
					{
						//Message
						$logging->info("Locker check");			
						$locker->check();
						
						//IMDB ID
						$id = preg_replace("~\D~", "", $movie["ImdbCode"]);
						
						//Gegevens verwerken
						$scraper = new scraper($id);
						$scraper->file($movie["TorrentUrl"],$movie["Quality"],"yts");
						
						//Message
						$logging->info("YTS movie: ".$this->iMovie);
						
						//Movie counter
						$this->iMovie++;
					}
					
					//Message
					$logging->info("YTS page: ".$i." (".$startPage." until ".$endPage.")");
				}
			}
		}
		//Error reporting
		catch(Exception $e) 
		{
			throw new Exception($e->getMessage());
		}
	}
}

?>