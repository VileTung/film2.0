<?php

/**
 * @author Kevin
 * @copyright 2014
 * @info Torrent
 */

class torrent
{
    //Save hash
    private $hash;

    //Save trackers
    private $trackers = array();

    //Save largest filesize
    private $movieSize;

    //Scraped results
    private $scrapedData = array();

    //We've got a torrent file
    public function file($url)
    {
        //Retrieve file
        list($state, $content) = cURL($url);

        //Failed
        if (!$state)
        {
            throw new Exception("Invalid torrent-url (" . $url . ")");
        }

        //Decode torrent
        $decoded = lightBenc($content,"decode");

        //Calculate hash
        $this->hash = sha1(lightBenc($decoded["info"],"encode"));

        //Calculate filesize
        $this->movieSize = max($decoded["info"]["files"]);

        //There is only one tracker in this variable
        $trackerList = array($decoded["announce"]);

        //Now we add the other ones
        foreach ($decoded["announce-list"] as $trackers)
        {
        	$trackerList[] = $trackers[0];
        }

        //Remove duplicates
        $this->trackers = array_unique($trackerList);
    }

    //We've got some trackers and hash
    public function tracker($hash, $trackers)
    {
        //Provided hash is not valid
        if (!preg_match("~^[0-9a-f]{40}$~i", $hash))
        {
            throw new Exception("Invalid hash!");
        }

        //Provided tracker(s) is/are not valid
        if (!is_array($trackers) || !count($trackers) > 0)
        {
            throw new Exception("Invalid tracker(s)!");
        }

        //Set hash and tracker(s)
        $this->hash = $hash;
        $this->trackers = $trackers;
    }

    //Now we are going to scrape
    public function scrape()
    {
        //Default seeders
        $countSeeders = 0;

        foreach ($this->trackers as $tracker)
        {
            //Scrape'n
            $scrape = scrape($tracker, $this->hash);

            //Everything went OK
            if ($scrape["state"] == "ok")
            {
                //Seeders & leechers
                $this->scrapedData[] = array(
                    "tracker" => $tracker,
                    "seeders" => $scrape["seeders"],
                    "leechers" => $scrape["leechers"],
                    "update" => date("Y-m-d H:i:s"));

                //We need this
                $countSeeders += $scrape["seeders"];
            }
            //Scrape failed
            else
            {
                //Empty seeders & leechers
                $this->scrapedData[] = array(
                    "tracker" => $tracker,
                    "seeders" => 0,
                    "leechers" => 0,
                    "update" => date("Y-m-d H:i:s", 0));
            }
        }

        //Torrent is dead
        if (!$countSeeders > 0)
        {
            throw new Exception("Torrent is dead!");
        }
    }

    //Insert data
    public function database($state = true, $name, $imdb, $quality, $retriever,$reliability)
    {
		var_dump($this->scrapedData); var_dump($this->hash);
		
        //Open database connection
        Database();

        //Delete torrent
        if (!$state)
        {
            sqlQueryi("DELETE FROM `data` WHERE `hash` = ?", array("s", $this->hash));
            sqlQueryi("DELETE FROM `trackers` WHERE `hash` = ?", array("s", $this->hash));
        }
        //Continue, insert or update
        else
        {
            //Check if row exists
            list($rowCount, $result) = sqlQueryi("SELECT `id` FROM `data` WHERE `hash` = ?", array("s", $this->hash), true);

            //Torrent doesn't exist
            if (!$rowCount > 0)
            {
                //Insert data
                sqlQueryi("INSERT INTO `data` (`name`,`imdb`,`hash`,`size`,`quality`,`retriever`,`added`, `reliability`) VALUES (?,?,?,?,?,?,?,?)", array(
                    "ssssssss",
                    $name,
                    $imdb,
                    $this->hash,
                    $this->movieSize,
                    $quality,
                    $retriever,
                    date("Y-m-d H:i:s"),
                    $reliability));

                //Insert trackers
                foreach ($this->scrapedData as $tracker)
                {
                    sqlQueryi("INSERT INTO `trackers` (`hash`,`tracker`,`leechers`,`seeders`,`update`) VALUES (?,?,?,?,?)", array(
                        "sssss",
                        $this->hash,
                        $tracker["tracker"],
                        $tracker["leechers"],
                        $tracker["seeders"],
                        $tracker["update"]));
                }
            }
            //Torrent does exist
            else
            {
                //Update trackers
                foreach ($this->scrapedData as $tracker)
                {
                    //Check if tracker exists
                    list($rowCount, $result) = sqlQueryi("SELECT * FROM `trackers` WHERE `hash` = ? AND `tracker` = ?", array(
                        "ss",
                        $this->hash,
                        $tracker["tracker"]), true);

                    //Update
                    if ($rowCount > 0)
                    {
                        sqlQueryi("UPDATE `trackers` SET `leechers` = ?, `seeders` = ?, `update` = ? WHERE `hash` = ? AND `tracker` = ?", array(
                            "sssss",
                            $tracker["leechers"],
                            $tracker["seeders"],
                            $tracker["update"],
                            $this->hash,
                            $tracker["tracker"]));
                    }
                    //New/add
                    else
                    {
                        sqlQueryi("INSERT INTO `trackers` (`hash`,`tracker`,`leechers`,`seeders`,`update`) VALUES (?,?,?,?,?)", array(
                            "sssss",
                            $this->hash,
                            $tracker["tracker"],
                            $tracker["leechers"],
                            $tracker["seeders"],
                            $tracker["update"]));
                    }
                }
            }
        }
    }
}
