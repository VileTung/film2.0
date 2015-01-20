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
        list($count, $result) = sqlQueryi("SELECT `genre` FROM `genres` WHERE `imdb` = ?", array("s", $imdb), true, true);

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
        list($count, $result) = sqlQueryi("SELECT * FROM `data` WHERE `imdb` = ?", array("s", $imdb), true, true);

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
        list($count, $result) = sqlQueryi("SELECT * FROM `trackers` WHERE `hash` = ?", array("s", $hash), true, true);

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
        list($count, $result) = sqlQueryi("SELECT * FROM `subtitle` WHERE `imdb` = ?", array("s", $imdb), true, true);

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
        list($count, $result) = sqlQueryi("SELECT * FROM `imdb` " . $this->query, $parameters, true, true);

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
}

?>