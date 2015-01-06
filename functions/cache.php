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

    //CLI or web
    private $cli;

    //Check if cached and cache is valid
    public function __construct($id = false, $expireFile = false, $cli = true)
    {
        global $logging, $cache, $cacheExpire;

        //No valid ID
        if (!$id || empty($id))
        {
            $this->cached = false;
        }
        //If cache exists
        elseif (file_exists($cache . $id))
        {
            $future = filemtime($cache . $id) + (60 * 1440 * 7 * 4);
            $now = time();

            //Must not be older than one month
            if ((!$expireFile && $future > $now) || ($expireFile && filemtime($cache . $id) > filemtime($cacheExpire)))
            {
                $this->cached = true;

                //Message
                ($cli ? $logging->info("Read cache (" . $id . ")") : "");
            }
            //Too old
            else
            {
                $this->cached = false;

                //Message
                ($cli ? $logging->warning("Cache is too old (" . $id . ")") : "");
            }
        }
        //Not found
        else
        {
            $this->cached = false;

            //Message
            ($cli ? $logging->debug("Cache doesn't exists (" . $id . ")") : "");
        }

        //Set ID
        $this->id = $id;

        //Set CLI var
        $this->cli = $cli;
    }

    //Returns cache state
    public function cached()
    {
        //Exists
        if ($this->cached)
        {
            return true;
        }
        //False
        else
        {
            return false;
        }
    }

    //Cache data via an URL
    public function url($url)
    {
        global $logging, $cache;

        //Read
        if ($this->cached)
        {
            return self::read();
        }
        //Retrieve and save
        else
        {
            //Message
            ($this->cli ? $logging->info("Get new cache (" . $this->id . ")") : "");

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
                ($this->cli ? $logging->error("No data retrieved (" . $url . " - " . $this->id . ")") : "");

                return false;
            }
        }
    }

    //Read cached content
    public function read()
    {
        global $cache;

        return file_get_contents($cache . $this->id);
    }

    //Save cached content
    public function save($content)
    {
        global $logging, $cache;

        $state = file_put_contents($cache . $this->id, $content, LOCK_EX);

        //OK
        if ($state)
        {
            //Message
            ($this->cli ? $logging->info("Cache saved (" . $this->id . ")") : "");
        }
        //Failed
        else
        {
            //Message
            ($this->cli ? $logging->error("Couldn't save cache (" . $this->id . ")") : "");
        }
    }

    //Prebuild (SQL) cache
    public function prebuild()
    {
        //Build
        self::buildWeb();

        //API
        //Perhaps in the future?
        //I don't see any reason why I should make it.

    }

    private function buildWeb()
    {
        //Make the connection
        Database();

        //Complete query array
        $query = array();

        //Add empty search, for web-index
        $query[] = array("", false);

        //Sorting
        $sort = array(
            "added",
            "imdb",
            "rating",
            "release",
            "runtime",
            "title");

        //Sort by
        $by = array("ASC", "DESC");

        //Genres
        $genre = array(
            "action",
            "adventure",
            "comedy",
            "crime",
            "documentary",
            "drama",
            "family",
            "fantasy",
            "history",
            "horror",
            "music",
            "musical",
            "mystery",
            "romance",
            "sci-fi",
            "sport",
            "thriller",
            "war",
            "western");

        //Combine all arrays
        $combined = array(
            $sort,
            $by,
            $genre);

        //Generate SQL based on the combinations
        foreach (self::combinations($combined) as $combinations)
        {
            //Split combinations
            $_sort = $combinations[0];
            $_by = $combinations[1];
            $_genre = $combinations[2];

            $query[] = array(" INNER JOIN `genres` ON `imdb`.`imdb`=`genres`.`imdb` WHERE `genres`.`genre` = ? ORDER BY `imdb`.`" . $_sort . "` " . $_by, array("s", $_genre));
        }

        //Genereate SQL based on loose combinations
        //First the sorting one
        foreach ($sort as $s)
        {
            //ASC or DESC
            foreach ($by as $b)
            {
                $query[] = array(" ORDER BY `imdb`.`" . $s . "` " . $b, false);
            }
        }

        //Then genres
        foreach ($genre as $g)
        {
            $query[] = array(" INNER JOIN `genres` ON `imdb`.`imdb`=`genres`.`imdb` WHERE `genres`.`genre` = ?", array("s", $g));
        }

        //Progress counter
        $progress = 0;

        //Now execute the SQL's
        foreach ($query as $sql)
        {
            //Count rows from SQL
            list($rowCount, $resultCount) = sqlQueryi("SELECT COUNT(*) AS `rows` FROM `imdb` " . $sql[0], $sql[1], true, true);

            //Calculate how many movies
            $movieCount = ceil($resultCount[0]["rows"] / 6);
            $pageCount = ceil($resultCount[0]["rows"] / 30);

            //Limit 6 movies a row, for now..
            if ($movieCount > 6)
            {
                $movieCount = 6;
            }

            //Simulate walking through pages
            if ($pageCount > 20)
            {
                $pageCount = 20;
            }

            for ($page = 1; $page <= $pageCount; $page++)
            {
                //Neccessary for correct counting
                $extra = 30 * ($page - 1);

                //Total
                $total = array();

                //This is the real deal!
                for ($i = 0; $i <= $movieCount - 1; $i++)
                {
                    //Get movies
                    list($rowCount, $result) = sqlQueryi("SELECT * FROM `imdb` " . $sql[0] . " LIMIT " . ($i * 5 + $extra) . ",5", $sql[1], true, true);

                    foreach ($result as $key => $fetch)
                    {
                        //Get all genres
                        list($rowCountG, $resultG) = sqlQueryi("SELECT `genre` FROM `genres` WHERE `imdb` = ?", array("s", $fetch["imdb"]), true, true);

                        //Get all torrents
                        list($rowCountT, $resultT) = sqlQueryi("SELECT * FROM `data` WHERE `imdb` = ?", array("s", $fetch["imdb"]), true, true);

                        foreach ($resultT as $key => $valueT)
                        {
                            //Get all trackers
                            list($rowCountTr, $resultTr) = sqlQueryi("SELECT * FROM `trackers` WHERE `hash` = ?", array("s", $valueT["hash"]), true, true);
                        }

                        //Get all subtitles
                        list($rowCountS, $resultS) = sqlQueryi("SELECT * FROM `subtitle` WHERE `imdb` = ?", array("s", $fetch["imdb"]), true, true);
                    }
                }
            }

            //$percent = ($progress / count($query)) * 100;

            //Progress counter
            $progress++;
        }
    }

    //Get all possibilities from array
    private function combinations($arrays, $i = 0)
    {
        //Check if possible
        if (!isset($arrays[$i]))
        {
            return array();
        }

        if ($i == count($arrays) - 1)
        {
            return $arrays[$i];
        }

        //Get data from next array
        $tmp = self::combinations($arrays, $i + 1);

        $result = array();

        //Loop throug each element from $arrays[$i] and then array from tmp
        foreach ($arrays[$i] as $v)
        {
            //Next loop
            foreach ($tmp as $t)
            {
                //If array, then merge, otherwise add
                $result[] = is_array($t) ? array_merge(array($v), $t) : array($v, $t);
            }
        }

        return $result;
    }
}

?>