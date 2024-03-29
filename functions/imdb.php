<?php

/**
 * @author Kevin
 * @copyright 2014
 * @info IMDB
 */

class imdb
{
    //Movie exists?
    private $movieExists;

    //Found IMDB info
    private $foundData;

    //IMDB ID
    private $imdbId;

    //Constructor
    public function __construct()
    {
        //Movie doesn't exist
        $this->movieExists = false;
    }

    //Get info from IMDB
    public function getInfo($id)
    {
        global $logging, $cache;

        //Save the ID
        $this->imdbId = $id;

        Database();

        //Check for duplicate
        list($rowCount, $result) = sqlQueryi("SELECT `imdb` FROM `imdb` WHERE `imdb` = ?", array("i", $id), true);

        if ($rowCount > 0)
        {
            //Message
            $logging->warning("Movie already exists (" . $id . ")");

            $this->movieExists = true;
            return true;
        }
        //Continu
        else
        {
            //Cache
			$_cache = new cache("info_" . md5($id));
			
            $content = $_cache->url("http://www.imdb.com/title/tt" . $id . "/");

            if ($content)
            {
                //Regex
                $regex = new regex;

                //Original/A.K.A title available?
                $titleOriginal = trim($regex->imdb("originalTitle", $content, 1));

                //If we find it..
                if ($titleOriginal)
                {
                    $title = $titleOriginal;
                }
                //.. Otherwise..
                else
                {
                    $title = trim($regex->imdb("title", $content, 1));
                }

                //Other information we want
                $description = trim($regex->imdb("plot", $content, 1));
                $runtime = trim($regex->imdb("runtime", $content, 1));
                $rating = $regex->imdb("rating", $content, 1);
                $releaseDate = $regex->imdb("releaseDate", $content, 1);
                $releaseDate = date("Y-m-d", strtotime($releaseDate));

                //Extract and filter
                $genre = $regex->imdb("genre", $content);
                $genre = array_filter(array_unique($genre[1]));

                //Both poster locations
                $poster = $regex->imdb("poster", $content, 1);
                $posterBig = substr($poster, 0, strpos($poster, "_")) . ".jpg";

                //This is what we need!
                $this->foundData = array(
                    "title" => $title,
                    "description" => $description,
                    "runtime" => $runtime,
                    "rating" => $rating,
                    "releaseDate" => $releaseDate,
                    "genre" => $genre,
                    "poster" => $poster,
                    "posterBig" => $posterBig);

                //Message
                $logging->info("Movie found (" . $title . " - " . $id . ")");
                return true;
            }
            //Failed(?)
            else
            {
                throw new Exception("Something went wrong, couldn't retrieve info.. (" . $id . ")");
            }
        }
    }

    //Insert in DB
    public function database()
    {
        global $logging, $poster;

        //If movie exists, we can stop
        if ($this->movieExists)
        {
            throw new Exception("Stopping, movie exists!");
        }

        //We need to call 'getInfo' first..
        if (!$this->foundData)
        {
            throw new Exception("There is no data to add..");
        }

        Database();

        //Does poster exist?
        if (file_exists($poster . $this->imdbId . ".jpg"))
        {
            //Message
            $logging->info("Poster exists (" . $this->imdbId . ")");
        }
        //Doesn't exist!
        else
        {
            //Large poster
            list($state, $content) = cURL($this->foundData["posterBig"]);

            if ($state)
            {
                //Save poster
                $file = fopen($poster . $this->imdbId . ".jpg", "x");
                fwrite($file, $content);
                fclose($file);

                //Get height and width
                list($widthBig, $heightBig) = getimagesize($poster . $this->imdbId . ".jpg");

                //Poster can't be too large
                if ($widthBig > 3000 || $heightBig > 3000)
                {
                    //Message
                    $logging->warning("Poster is too large (" . $this->imdbId . ")");

                    //Get normal sized poster
                    $state = false;
                }
                //Poster is perfect
                else
                {
                    //Succeeded
                    $state = true;
                }
            }
            //Failed
            else
            {
                //Message
                $logging->warning("Large poster failed (" . $this->imdbId . ")");
            }

            //Failed, normal size poster..
            if (!$state)
            {
                //Message
                $logging->info("Fetching normal size poster (" . $this->imdbId . ")");

                //Get poster
                list($state, $content) = cURL($this->foundData["poster"]);

                if ($state)
                {
                    //Remove (old) big poster, just in case
                    if (file_exists($poster . $this->imdbId . ".jpg"))
                    {
                        unlink($poster . $this->imdbId . ".jpg");
                    }

                    //Save new poster
                    $file = fopen($poster . $this->imdbId . ".jpg", "x");
                    fwrite($file, $content);
                    fclose($file);
                }
                else
                {
                    //Message
                    $logging->error("Normal poster failed (" . $this->imdbId . ")");
                }
            }

            //If we succeeded, then resize
            if ($state)
            {
                //Max height and width
                $width = 163;
                $height = 240;

                //Current height and width
                list($widthOriginal, $heightOriginal) = getimagesize($poster . $this->imdbId . ".jpg");

                $scaleOriginal = $widthOriginal / $heightOriginal;

                //Calculate scale
                if ($width / $height > $scaleOriginal)
                {
                    $width = $height * $scaleOriginal;
                }
                else
                {
                    $height = $width / $scaleOriginal;
                }

                //Adjust poster
                $image = imagecreatetruecolor($width, $height);
                $newImage = imagecreatefromjpeg($poster . $this->imdbId . ".jpg");
                imagecopyresampled($image, $newImage, 0, 0, 0, 0, $width, $height, $widthOriginal, $heightOriginal);

                //Save poster
                imagejpeg($image, $poster . $this->imdbId . ".jpg", 100);

                //Message
                $logging->info("Poster saved (" . $this->imdbId . ")");
            }
            else
            {
                //Message
                $logging->error("Failed downloading poster (" . $this->imdbId . ")");
            }
        }

        //Insert data
        sqlQueryi("INSERT INTO `imdb` (`imdb`,`title`,`description`,`runtime`,`rating`,`release`,`added`) VALUES (?,?,?,?,?,?,?)", array(
            "sssssss",
            $this->imdbId,
            $this->foundData["title"],
            $this->foundData["description"],
            $this->foundData["runtime"],
            $this->foundData["rating"],
            $this->foundData["releaseDate"],
            date("Y-m-d H:i:s")));

        //Genres
        foreach ($this->foundData["genre"] as $genre)
        {
            sqlQueryi("INSERT INTO `genres` (`imdb`,`genre`) VALUES (?,?)", array(
                "ss",
                $this->imdbId,
                $genre));
        }

        //Message
        $logging->info("Movie added (" . $this->foundData["title"] . " - " . $this->imdbId . ")");
    }
}

?>