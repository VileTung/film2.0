<?php

/**
 * @author Kevin
 * @copyright 2014
 * @info Web interface
 */

//Debug
error_reporting(E_ALL);
ini_set("display_errors", 1);

require_once ("bTemplate.php");
require_once ("pager.php");
require_once ("./../functions/functions.php");

class index
{
    //Template
    private $bTemplate;

    //URL
    private $genreURL = array();
    private $sortURL = array();
    private $newURL = array();

    //SQL
    private $query;
    private $type;
    private $value = array();
    private $whereAnd = "WHERE";


    public function __construct()
    {
        //Template
        $this->bTemplate = new bTemplate();

        //Make the connection
        Database();
    }

    private function genre($genre)
    {
        //SQL
        $this->type .= "s";
        $this->value[] = $genre;

        $this->query .= " INNER JOIN `genres` ON `imdb`.`imdb`=`genres`.`imdb` WHERE `genres`.`genre` = ?";

        //URL
        $this->newURL["genre"] = $genre;

        //Part of SQL
        $this->whereAnd = "AND";
    }

    private function title($title)
    {
        //SQL
        $this->type .= "s";
        $this->value[] = "%" . $title . "%";

        $this->query .= " " . $this->whereAnd . " `imdb`.`title` LIKE ? ";

        //URL
        $this->newURL["title"] = $title;

        //Part of SQL
        $this->whereAnd = "AND";
    }

    private function pager($page)
    {
        //URL
        $this->newURL["page"] = $page;
    }

    private function sorting()
    {
        if (!empty($_GET["sort"]))
        {
            //Sort order
            if (isset($_GET["by"]) && $_GET["by"] == "DESC")
            {
                $by = "DESC";
            }
            else
            {
                $by = "ASC";
            }

            //SQL
            $this->query .= " ORDER BY `imdb`.`" . $_GET["sort"] . "` " . $by;

            $this->newURL["sort"] = $_GET["sort"];
            $this->newURL["by"] = $by;
        }
        //Default
        else
        {
            //SQL
            $this->query .= " ORDER BY `imdb`.`added` DESC";
        }
    }

    private function URL($type, $sort, $textG, $get, $extra, $upDown = true)
    {
        //Check if there are any parameters
        if (count($sort) > 0)
        {
            $URL = $sort;

            if (isset($_GET[$get]) && $_GET[$get] == $type)
            {
                //No sort
                if (!$upDown)
                {
                    $text = $textG;
                    $class = "class=\"active\"";
                }
                //Sort
                elseif (isset($sort["by"]) && $sort["by"] == "ASC")
                {
                    $URL["by"] = "DESC";
                    $text = $textG . " &#8595;";
                    $class = "class=\"active\"";
                }
                elseif (isset($sort["by"]) && $sort["by"] == "DESC")
                {
                    $URL["by"] = "ASC";
                    $text = $textG . " &#8593;";
                    $class = "class=\"active\"";
                }
            }
            else
            {
                $text = $textG;
                $class = "";
            }

            $URL = "?" . http_build_query($URL) . "&" . $get . "=" . $type;
        }
        else
        {
            $URL = "?" . $get . "=" . $type;
            $text = $textG;
            $class = "";
        }

        //Template
        $this->bTemplate->set("url" . $extra, $URL);
        $this->bTemplate->set("text" . $extra, $text);
        $this->bTemplate->set("class" . $extra, $class);
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

    public function show()
    {
        //Page
        if (!empty($_GET["page"]))
        {
            $this->newURL["page"] = $_GET["page"];
        }

        //Genre
        if (!empty($_GET["genre"]))
        {
            self::genre($_GET["genre"]);
        }

        //Search
        if (!empty($_GET["title"]))
        {
            self::title($_GET["title"]);
        }

        //Order
        self::sorting();

        //Remove genre from URL
        $this->genreURL = $this->newURL;
        unset($this->genreURL["genre"]);

        //Remove genre from URL
        $this->sortURL = $this->newURL;
        unset($this->sortURL["sort"]);

        //Create URL for sorting
        self::URL("added", $this->sortURL, "Added", "sort", "SA");
        self::URL("imdb", $this->sortURL, "IMDB", "sort", "SI");
        self::URL("rating", $this->sortURL, "Rating", "sort", "SRa");
        self::URL("release", $this->sortURL, "Release", "sort", "SRe");
        self::URL("runtime", $this->sortURL, "Runtime", "sort", "SRu");
        self::URL("title", $this->sortURL, "Title", "sort", "ST");

        //Create URL for genres
        self::URL("action", $this->genreURL, "Action", "genre", "GAc", false);
        self::URL("adventure", $this->genreURL, "Adventure", "genre", "GAd", false);
        self::URL("comedy", $this->genreURL, "Comedy", "genre", "GCo", false);
        self::URL("crime", $this->genreURL, "Crime", "genre", "GCr", false);
        self::URL("documentary", $this->genreURL, "Documentary", "genre", "GDo", false);
        self::URL("drama", $this->genreURL, "Drama", "genre", "GDr", false);
        self::URL("family", $this->genreURL, "Family", "genre", "GFam", false);
        self::URL("fantasy", $this->genreURL, "Fantasy", "genre", "GFan", false);
        self::URL("history", $this->genreURL, "History", "genre", "GHi", false);
        self::URL("horror", $this->genreURL, "Horror", "genre", "GHo", false);
        self::URL("music", $this->genreURL, "Music", "genre", "GMusic", false);
        self::URL("musical", $this->genreURL, "Musical", "genre", "GMusica", false);
        self::URL("mystery", $this->genreURL, "Mystery", "genre", "GMy", false);
        self::URL("romance", $this->genreURL, "Romance", "genre", "GR", false);
        self::URL("sci-Fi", $this->genreURL, "Sci-Fi", "genre", "GSc", false);
        self::URL("sport", $this->genreURL, "Sport", "genre", "GSp", false);
        self::URL("thriller", $this->genreURL, "Thriller", "genre", "GT", false);
        self::URL("war", $this->genreURL, "War", "genre", "GWa", false);
        self::URL("western", $this->genreURL, "Western", "genre", "GWe", false);

        //Bind parameters
        if (count($this->value) > 0)
        {
            $parameters = array_merge(array($this->type), $this->value);
        }
        else
        {
            $parameters = false;
        }

        //Count rows from MySQL
        list($rowCount, $resultRows) = sqlQueryi("SELECT COUNT(*) AS `rows` FROM `imdb` " . $this->query, $parameters, true, true);

        //Calculate how many movies
        $movieCount = ceil($resultRows[0]["rows"] / 6);
        $pageCount = ceil($resultRows[0]["rows"] / 30);

        //Limit total count to 60, for now..
        if ($movieCount > 6)
        {
            $movieCount = 6;
        }

        //Necessary for the pager
        if (isset($_GET["page"]) && $_GET["page"] != 1 && !empty($_GET["page"]))
        {
            $extra = 30 * ($_GET["page"] - 1);
        }
        else
        {
            $extra = 0;
        }

        //Total
        $total = array();

        for ($i = 0; $i <= $movieCount - 1; $i++)
        {
            //Get movies
            list($rowCount, $result) = sqlQueryi("SELECT * FROM `imdb` " . $this->query . " LIMIT " . ($i * 5 + $extra) . ",5", $parameters, true, true);

            foreach ($result as $key => $fetch)
            {
                //Title too long
                if (strlen($fetch["title"]) > 18)
                {
                    $title = substr($fetch["title"], 0, 16) . "..";
                }
                //Don't change
                else
                {
                    $title = $fetch["title"];
                }

                //Get all genres
                list($rowCountG, $resultG) = sqlQueryi("SELECT `genre` FROM `genres` WHERE `imdb` = ?", array("s", $fetch["imdb"]), true, true);

                $genres = "";

                foreach ($resultG as $key => $value)
                {
                    $genres .= $value["genre"] . ", ";
                }

                //Get all torrents
                list($rowCountT, $resultT) = sqlQueryi("SELECT * FROM `data` WHERE `imdb` = ?", array("s", $fetch["imdb"]), true, true);

                $torrents = array();

                foreach ($resultT as $key => $valueT)
                {
                    //Get all trackers
                    list($rowCountTr, $resultTr) = sqlQueryi("SELECT * FROM `trackers` WHERE `hash` = ?", array("s", $valueT["hash"]), true, true);

                    $avg = "";
                    $count = 0;
                    $trackersURL = "magnet:?xt=urn:btih:" . $valueT["hash"] . "&dn=" . urlencode($fetch["title"]);

                    foreach ($resultTr as $key => $value)
                    {
                        if ($value["seeders"] > 0 && $value["leechers"] > 0)
                        {
                            $avg += $value["seeders"] / $value["leechers"];

                            $count++;
                        }

                        //URL
                        $trackersURL .= "&tr=" . urlencode($value["tracker"]);
                    }
					
                    //Calculate state
					if($avg==0)
					{
						$state = "Dead";
					}
                    elseif (($avg / $count) > 2)
                    {
                        $state = "Good";
                    }
                    elseif (($avg / $count) >= 1)
                    {
                        $state = "OK";
                    }
                    else
                    {
                        $state = "Bad";
                    }

                    $torrents[] = array(
                        "url" => $trackersURL,
                        "size" => self::readableSize($valueT["size"]),
                        "quality" => $valueT["quality"],
                        "state" => $state);
                }

                //Get all subtitles
                list($rowCountS, $resultS) = sqlQueryi("SELECT * FROM `subtitle` WHERE `imdb` = ?", array("s", $fetch["imdb"]), true, true);

                //Subtitle
                $subtitles = array();

                if ($rowCountS > 0)
                {
                    foreach ($resultS as $keyS => $valueS)
                    {
                        //Show it nicely
                        if ($valueS["language"] == "nl")
                        {
                            $language = "Dutch";
                        }
                        elseif ($valueS["language"] == "en")
                        {
                            $language = "English";
                        }

                        $subtitles[] = array("url" => "http://192.168.1.202/film2.0/subtitle/" . $valueS["imdb"] . "_" . $valueS["hash"] . ".srt", "language" => $language);
                    }
                }
                else
                {
                    $subtitles[0] = array("url" => "#", "language" => "<em>None</em>");
                }

                $total[] = array(
                    "imdb" => $fetch["imdb"],
                    "title" => $title,
                    "titleOriginal" => $fetch["title"],
                    "date" => "(" . date("Y", strtotime($fetch["release"])) . ")",
                    "description" => $fetch["description"],
                    "runtime" => $fetch["runtime"],
                    "genres" => $genres,
                    "rating" => $fetch["rating"],
                    "torrents" => $torrents,
                    "subtitles" => $subtitles);
            }
        }

        //Set the movies
        $this->bTemplate->set("movies", $total);

        //URL for title search
        if (isset($this->newURL["genre"]))
        {
            $this->bTemplate->set("genre", true, true);
            $this->bTemplate->set("genreURL", $this->newURL["genre"]);
        }
        else
        {
            $this->bTemplate->set("genre", false, true);
        }

        if (isset($this->newURL["sort"]))
        {
            $this->bTemplate->set("sort", true, true);
            $this->bTemplate->set("sortURL", $this->newURL["sort"]);
        }
        else
        {
            $this->bTemplate->set("sort", false, true);
        }

        if (isset($this->newURL["by"]))
        {
            $this->bTemplate->set("by", true, true);
            $this->bTemplate->set("byURL", $this->newURL["by"]);
        }
        else
        {
            $this->bTemplate->set("by", false, true);
        }

        //Pager
        $pager = new pager($pageCount, $this->newURL);
        $navigation = $pager->navigation();

        $this->bTemplate->set("navigation", $navigation);

        //Print the template!
        print ($this->bTemplate->fetch("index.tpl"));
    }
}

$show = new index();
$show->show();

?>