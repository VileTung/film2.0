<?php

/**
 * @author Kevin
 * @copyright 2014
 * @info Get movie data (Web and API)
 */

class getMovies
{
    //Begin
    private $begin;

    //Limit
    private $limit;

    //Title
    private $titleCut;
    private $titleLength;

    //Query
    private $query;

    //Query parameters
    private $parameter = array("");

    //Query parts
    private $qBy;
    private $qGenre;
    private $qSort;
    private $qTitle;

    //Build cache?
    private $buildCache = false;

    //Constructor
    public function __construct()
    {
        //Make the connection
        Database();
    }

    //Getter
    public function __get($property)
    {
        if (property_exists($this, $property))
        {
            return $this->$property;
        }
    }

    //Setter
    public function __set($property, $value)
    {
        if (property_exists($this, $property))
        {
            $this->$property = $value;
        }
    }

    private function buildQuery()
    {
        //Reset values
        $extraQuery = "WHERE";

        $this->parameter = array("");
        $this->query = "";

        //Genre
        if (!empty($this->qGenre) && $this->qGenre != "")
        {
            $this->parameter[0] .= "s";
            $this->parameter[] = $this->qGenre;

            $this->query .= " INNER JOIN `genres` ON `imdb`.`imdb`=`genres`.`imdb` WHERE `genres`.`genre` = ?";

            $extraQuery = "AND";
        }

        //Title
        if (!empty($this->qTitle) && $this->qTitle != "")
        {
            $this->parameter[0] .= "s";
            $this->parameter[] = "%" . $this->qTitle . "%";

            $this->query .= " " . $extraQuery . " `imdb`.`title` LIKE ?";

            $extraQuery = "AND";
        }

        //Sort (and by)
        if (!empty($this->qSort) && $this->qSort != "")
        {
            //By is defined
            if (!empty($this->qBy) && $this->qBy != "")
            {
                $this->query .= " ORDER BY `imdb`.`" . $this->qSort . "` " . $this->qBy;
            }
            //By is not defined
            else
            {
                $this->query .= " ORDER BY `imdb`.`" . $this->qSort . "` DESC";
            }
        }
        //Default sorting, newest first
        else
        {
            $this->query .= " ORDER BY `imdb`.`added` DESC";
        }

        //Now set the limit
        $this->query .= " LIMIT " . $this->begin . "," . $this->limit;
    }

    private function titleCutter($title)
    {
        //Title cutter
        if ($this->titleCut)
        {
            if (strlen($title) > $this->titleLength)
            {
                return substr($title, 0, ($this->titleLength - 2)) . "..";
            }
        }

        //Return what we got
        return $title;
    }

    private function getGenres($imdb)
    {
        //Get all genres
        list($count, $result) = sqlQueryi("SELECT `genre` FROM `genres` WHERE `imdb` = ?", array("s", $imdb), true, true, $this->
            buildCache);

        if ($count > 0)
        {
            return $result;
        }
        //Nothing
        else
        {
            return false;
        }
    }

    private function getTorrents($imdb)
    {
        //Get all torrents
        list($count, $result) = sqlQueryi("SELECT * FROM `data` WHERE `imdb` = ?", array("s", $imdb), true, true, $this->
            buildCache);

        foreach ($result as $key => $fetch)
        {
            //Check if not empty
            if (is_array(self::getTrackers($fetch["hash"])))
            {
                //Add trackers to the list
                $result[$key]["trackers"] = self::getTrackers($fetch["hash"]);
            }

            //Add readable filesize
            $result[$key]["sizeReadable"] = self::readableSize($fetch["size"]);
        }

        //Send data back
        return $result;
    }

    private function getTrackers($hash)
    {
        //Get all trackers
        list($count, $result) = sqlQueryi("SELECT * FROM `trackers` WHERE `hash` = ?", array("s", $hash), true, true, $this->
            buildCache);

        if ($count > 0)
        {
            return $result;
        }
        //Nothing
        else
        {
            return false;
        }
    }

    private function getSubtitles($imdb)
    {
        //Get all subtitles
        list($count, $result) = sqlQueryi("SELECT * FROM `subtitle` WHERE `imdb` = ?", array("s", $imdb), true, true, $this->
            buildCache);

        if ($count > 0)
        {
            return $result;
        }
        //Nothing
        else
        {
            return false;
        }
    }

    private function readableSize($bytes)
    {
        //Types
        $type = array(
            "B",
            "kB",
            "MB",
            "GB",
            "TB",
            "PT");

        //Factor
        $factor = floor((strlen($bytes) - 1) / 3);

        //Result
        return sprintf("%.2f", $bytes / pow(1024, $factor)) . @$type[$factor];
    }

    public function data()
    {
        //Build query
        self::buildQuery();

        //SQL parameters
        if (count($this->parameter) > 0 && $this->parameter[0] != "")
        {
            $parameters = array($this->parameter[0], $this->parameter[1]);
        }
        else
        {
            $parameters = false;
        }

        //Count rows
        list($count, $result) = sqlQueryi("SELECT * FROM `imdb` " . $this->query, $parameters, true, true, $this->buildCache);

        if ($count > 0)
        {
            //Loop through movies
            foreach ($result as $key => $fetch)
            {
                //Add cutted title
                $result[$key]["titleCutted"] = self::titleCutter($fetch["title"]);

                //Torrents
                $torrent = self::getTorrents($fetch["imdb"]);

                if (is_array($torrent))
                {
                    $result[$key]["torrent"] = $torrent;
                }

                //Subtitles
                $subtitle = self::getSubtitles($fetch["imdb"]);

                if (is_array($subtitle))
                {
                    $result[$key]["subtitle"] = $subtitle;
                }

                //Genres
                $genre = self::getGenres($fetch["imdb"]);

                if (is_array($genre))
                {
                    $result[$key]["genre"] = $genre;
                }
            }

            return $result;
        }
        //Nothing found
        else
        {
            return false;
        }
    }

    //Set the settings for pre-build cache
    public function setCache()
    {
        global $logging, $locker;

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

        //Message
        $logging->info("Locker check");
        $locker->check();

        //Message
        $logging->info("Cache index page");

        //Build default index page
        self::buildCache("DESC", "", "added");

        //Build cache for the combinations
        $data = self::combinations($combined);

        //Total and count for progress
        $total = 1 + count($data) + (count($sort) * 2) + (count($genre) * 2);
        $progress = 1;

        //Message
        $logging->info("Cache combinations page");

        foreach ($data as $combinations)
        {
            //Split combinations
            $_sort = $combinations[0];
            $_by = $combinations[1];
            $_genre = $combinations[2];

            //Build
            self::buildCache($_by, $_genre, $_sort);

            $progress++;

            //Progress
            $locker->update(($progress / $total) * 100);
        }

        //Message
        $logging->info("Cache 'sort'");

        //Build cache for 'sort'
        foreach ($sort as $s)
        {
            //ASC or DESC
            foreach ($by as $b)
            {
                //Build
                self::buildCache($b, "", $s);

                $progress++;

                //Progress
                $locker->update(($progress / $total) * 100);
            }
        }

        //Message
        $logging->info("Cache 'genres'");

        //Build cache for 'genres'
        foreach ($genre as $g)
        {
            //ASC or DESC
            foreach ($by as $b)
            {
                //Build
                self::buildCache($b, $g, "added");

                $progress++;

                //Progress
                $locker->update(($progress / $total) * 100);
            }
        }
    }

    //Build cache
    private function buildCache($by, $genre, $sort)
    {
        //Call settings class
        $_settings = new settings();

        //Set wait
        $wait = true;

        //Check if buildCache is not already active
        while ($wait == true)
        {
            //Check
            if (!$_settings->get("buildCache"))
            {
                //Exit, we can continue
                $wait = false;
                break;
            }

            //Wait a minute
            sleep(60);
        }

        //Settings, buildCache is active
        $_settings->set("buildCache", true);

        //Default values
        self::__set("limit", 30);
        self::__set("qTitle", "");
        self::__set("titleCut", false);
        self::__set("titleLength", 1);
        self::__set("buildCache", true);

        //Page looping
        for ($i = 0; $i <= 11; $i++)
        {
            self::__set("begin", ($i * 30));

            self::__set("qBy", $by);
            self::__set("qGenre", $genre);
            self::__set("qSort", $sort);

            //Movie and other data
            $data = self::data();
        }

        //Settings, buildCache is finished
        $_settings->set("buildCache", false);
    }

    //Get all possibilities from array
    private function combinations($arrays, $i = 0)
    {
        global $locker;
        
        //Check
        $locker->check();
        
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