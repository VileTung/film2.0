<?php

/**
 * @author Kevin
 * @copyright 2014
 * @info Get movies from YTS
 */

class yts
{
    //Movie counter
    private $iMovie = 1;

    private $retry = 0;

    //Get movies
    public function movies($startPage = 1, $endPage = 200)
    {
        global $logging, $locker;

        //Message
        $logging->info("Starting YTS (" . $startPage . " until " . $endPage . ")");

        //Page 0 is equal to page 1..
        if ($startPage == 0 || $startPage == "0" || $endPage == 0 || $endPage == "0")
        {
            //Starting page
            if ($startPage == 0 || $startPage == "0")
            {
                $startPage = 1;
            }

            //Ending page
            if ($endPage == 0 || $endPage == "0")
            {
                $endPage = 1;
            }

            //Message
            $logging->info("Changed starting/ending page (" . $startPage . " until " . $endPage . ")");
        }

        //Try..
        try
        {
            //The loop
            for ($i = $startPage; $i <= $endPage; $i++)
            {
                //JSON encoded data
                list($state, $json) = cURL("https://yts.wf/api/list.json?limit=50&set=" . $i);
                //Possible other URLs
                //https://yts.re
                //http://ytsre.eu

                if ($state)
                {
                    //Reset retry counter
                    $this->retry = 0;

                    //Decode data
                    $data = json_decode($json, true);

                    //Determine if done..
                    if (isset($data["status"]))
                    {
                        throw new Exception("No new data available (" . $i . " of the " . $endPage . ")");
                    }

                    //Loop through movies
                    foreach ($data["MovieList"] as $movie)
                    {
                        //Message
                        $logging->info("Locker check");
                        $locker->check();

                        //IMDB ID
                        $id = preg_replace("~\D~", "", $movie["ImdbCode"]);

                        //Process data
                        $scraper = new scraper($id);
                        $scraper->file($movie["TorrentUrl"], $movie["Quality"], "yts");

                        //Get subtitle
                        self::subtitle($id);

                        //Update progress
                        self::progress($startPage, $endPage, $this->iMovie);

                        //Message
                        $logging->info("YTS movie: " . $this->iMovie);

                        //Movie counter
                        $this->iMovie++;

                        print ("\n");
                    }

                    //Message
                    $logging->info("YTS page: " . $i . " (" . $startPage . " until " . $endPage . ")");
                }
                else
                {
                    //Are we allowed to retry?
                    if ($this->retry < 5)
                    {
                        //Message
                        $logging->warning("No data retrieved from YTS (attempt: " . $this->retry . " - page: " . $i . ")");

                        //Lower by 1
                        $i--;

                        //Update retry count..
                        $this->retry++;
						
						//Pause
						sleep(5);
                    }
                    //Stop!
                    else
                    {
                        //Message
                        $logging->error("No data retrieved from YTS (attempt: " . $this->retry . " - page: " . $i . ")");
                    }
                }
            }
        }
        //Error reporting
        catch (exception $e)
        {
            throw new Exception($e->getMessage());
        }
    }

    //Calculate progress
    private function progress($start, $end, $current)
    {
        global $locker;

        $total = (($end - $start) + 1) * 50;

        $progress = ($current / $total) * 100;

        $locker->update($progress);
    }

    //Get subtitle, the old fashion way..
    public function subtitleHTML($id)
    {
        global $logging;

        try
        {
            //Message
            $logging->info("YIFY Subtitle (HTML - " . $id . ")");

            //Get page
            list($state, $content) = cURL("http://yifysubtitles.com/movie-imdb/tt" . $id);

            //Failed
            if (!$state)
            {
                throw new Exception("Invalid YIFY subtitle-url (" . $id . ")");
            }

            //Regex
            $regex = new regex;

            //Extract info
            $data = $regex->main("yify", $content);

            //Default
            $nl = array();
            $en = array();

            //Loop
            foreach ($data[3] as $key => $language)
            {
                //Dutch
                if (strtolower($language) == "dutch" && strpos($data[0][$key], "verified") !== false)
                {
                    $nl[] = array(
                        "id" => $data[1][$key],
                        "votes" => $data[2][$key],
                        "url" => $data[4][$key]);
                }
                //English
                elseif (strtolower($language) == "english" && strpos($data[0][$key], "verified") !== false)
                {
                    $en[] = array(
                        "id" => $data[1][$key],
                        "votes" => $data[2][$key],
                        "url" => $data[4][$key]);
                }
            }

            //Dutch
            if (count($nl) > 0)
            {
                foreach ($nl as $dutch)
                {
                    //Construct URL
                    $url = "http://yifysubtitles.com/" . str_replace("subtitles", "subtitle", $dutch["url"]) . ".zip";

                    //Retrieve subtitle
                    $getSubtitle = new subtitle($id);

                    $getSubtitle->saveSubtitle($url, "nl");
                }
            }
			
            //English
            elseif (count($en) > 0)
            {
                foreach ($en as $english)
                {
                    //Construct URL
                    $url = "http://yifysubtitles.com/" . str_replace("subtitles", "subtitle", $english["url"]) . ".zip";

                    //Retrieve subtitle
                    $getSubtitle = new subtitle($id);

                    $getSubtitle->saveSubtitle($url, "en");
                }
            }
            //Failed
            else
            {
                //Message
                $logging->warning("No subtitles found! (" . $id . ")");
            }
        }
        //Error reporting
        catch (exception $e)
        {
            $logging->error($e->getMessage());
        }
    }
	
	//Get subtitle, the new way!
    public function subtitle($id)
    {
        global $logging;

        try
        {
            //Message
            $logging->info("YIFY Subtitle (API - " . $id . ")");

            //Get page
            list($state, $json) = cURL("http://api.yifysubtitles.com/subs/tt" . $id);
			//Mirror/alternative 'http://api.ysubs.com/subs/'

            //Failed
            if (!$state)
            {
                throw new Exception("Invalid YIFY subtitle-url (" . $id . ")");
            }
			
			//Decode data
			$data = json_decode($json, true);
			
			//Check if there are any subtitles
			if(isset($data["subtitles"]) && $data["subtitles"]>0)
			{
				$subs = $data["subs"]["tt".$id];
			
				//Get all Dutch subtitles
				if(isset($subs["dutch"]))
				{
					foreach($subs["dutch"] as $key=>$subtitle)
					{
						//Construct URL
						$url = "http://yifysubtitles.com" . $subtitle["url"];

						//Retrieve subtitle
						$getSubtitle = new subtitle($id);
	
						$getSubtitle->saveSubtitle($url, "nl");
					}
				}
				//Otherwise, English..
				elseif(isset($subs["english"]))
				{
					foreach($subs["english"] as $key=>$subtitle)
					{
						//Construct URL
						$url = "http://yifysubtitles.com" . $subtitle["url"];

						//Retrieve subtitle
						$getSubtitle = new subtitle($id);
	
						$getSubtitle->saveSubtitle($url, "en");
					}
				}
				//Failed
				else
				{
					//Message
					$logging->warning("No subtitles found! (" . $id . ")");
				}
			}
        }
        //Error reporting
        catch (exception $e)
        {
            $logging->error($e->getMessage());
        }
    }
}

?>