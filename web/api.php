<?php

/**
 * @author Kevin
 * @copyright 2014
 * @info API
 */

//Security check
if (isset($_POST["check" . date("Y")]) && $_POST["check" . date("Y")] == date("dmY"))
{
    require_once ("./../functions/functions.php");

    class api
    {
        //Build query
        private $query;
        private $type;
        private $value = array();
        private $whereAnd = "WHERE";

        //Parameters for SQL
        private $parameters = false;

        //Default page limit
        private $limit = "0";

        //Total results
        private $total = array();

        //Found some movies?
        private $movieFound = true;

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

        private function check()
        {
            //Genre
            if (isset($_POST["genre"]) && !empty($_POST["genre"]))
            {
                $this->type .= "s";
                $this->value[] = $_POST["genre"];

                $this->query .= " INNER JOIN `genres` ON `imdb`.`imdb`=`genres`.`imdb` WHERE `genres`.`genre` = ?";

                //Part of SQL
                $this->whereAnd = "AND";
            }

            //Title
            if (isset($_POST["title"]) && !empty($_POST["title"]))
            {
                $this->type .= "s";
                $this->value[] = "%" . $_POST["title"] . "%";

                $this->query .= " " . $whereAnd . " `imdb`.`title` LIKE ? ";

                //Part of SQL
                $this->whereAnd = "AND";
            }

            //Sort
            if (isset($_POST["sort"]) && !empty($_POST["sort"]))
            {
                //Determine if order by is given
                if (isset($_POST["by"]) && !empty($_POST["by"]))
                {
                    $by = $_POST["by"];
                }
                else
                {
                    $by = "ASC";
                }

                //SQL
                $this->query .= " ORDER BY `imdb`.`" . $_POST["sort"] . "` " . $by;
            }

            //Page
            if (isset($_POST["page"]) && is_numeric($_POST["page"]))
            {
                $this->limit = ($_POST["page"] - 1) * 50;
            }

            //Bind parameters
            if (count($this->value) > 0)
            {
                $this->parameters = array_merge(array($type), $value);
            }
        }

        public function get()
        {
            //Make the connection
            Database();

            //Get movies
            list($rowCount, $result) = sqlQueryi("SELECT * FROM `imdb` " . $this->query . " LIMIT " . $this->limit . ",50", $this->parameters, true);

            //We found some movies!
            if ($rowCount > 0)
            {
                foreach ($result as $key => $fetch)
                {
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
                        if ($avg == 0)
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

                    $this->total[] = array(
                        "imdb" => $fetch["imdb"],
                        "title" => $fetch["title"],
                        "date" => $fetch["release"],
                        "added" => $fetch["added"],
                        "description" => $fetch["description"],
                        "runtime" => $fetch["runtime"],
                        "genres" => $genres,
                        "rating" => $fetch["rating"],
                        "torrents" => $torrents,
                        "subtitles" => $subtitles);
                }
            }
            //No movies found
            else
            {
                $this->movieFound = false;
            }
        }

        public function show()
        {
            //Output, XML
            if (isset($_POST["output"]) && $_POST["output"] == "xml")
            {
                header("Content-Type: application/xml; charset=utf-8");

                require_once ("bTemplate.php");

                $bTemplate = new bTemplate();

                //Movies found?
                $bTemplate->set("movieFound", $this->movieFound, true);

                //If we found movies
                if ($this->movieFound)
                {
                    //Set the movies
                    $bTemplate->set("movies", $this->total);
                }
                else
                {
                    //Set error message
                    $bTemplate->set("error", "1");
                    $bTemplate->set("message", "I couldn't find any movies.");
                }

                //Print the template!
                print ($bTemplate->fetch("template.xml"));
            }
            //Default we use JSON
            else
            {
                header("Content-type: application/json; charset=utf-8");

                //If we found movies
                if ($this->movieFound)
                {
                    print (json_encode($this->total));
                }
                else
                {
                    $json = array("error" => "1", "message" => "I couldn't find any movies.");

                    //Print it
                    print (json_encode($json));
                }
            }
        }
    }

    //Show!
    $api = new api();
    $api->get();
    $api->show();
}
else
{
    header("Location: ./");
}

?>