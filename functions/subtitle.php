<?php

/**
 * @author Kevin
 * @copyright 2014
 * @info Subtitles
 */

class subtitle
{
    //Set IMDB
    private $imdb;

    public function __construct($id)
    {
        //Need to add some '0'
        $this->imdb = sprintf("%07d", $id);
    }

    public function saveSubtitle($url, $language)
    {
        global $cache, $logging;

        //Download subtitle
        list($state, $content) = cURL($url);

        //Failed
        if (!$state)
        {
            throw new Exception("Subtitle download failed (" . $url . ")");
        }

        //Save subtitle
        $file = fopen($cache . $this->imdb . ".zip", "x");
        fwrite($file, $content);
        fclose($file);

        //Check if file exists
        if (!file_exists($cache . $this->imdb . ".zip"))
        {
            //Error, file doesn't exist
            throw new Exception("No file to extract (" . $this->imdb . " - " . $language . " - " . $cache . $this->imdb . ".zip - " . $url . ")");
        }

        //Unzip
        $zip = new ZipArchive;
        $extract = $zip->open($cache . $this->imdb . ".zip");

        //Can we open it?
        if ($extract === true)
        {
            //Extract file
            $zip->extractTo($cache . $this->imdb);
            $zip->close();

            //Message
            $logging->info("Subtitle extracted! (" . $this->imdb . " - " . $language . ")");
        }
        //Failed
        else
        {
            //Remove cache, zip file and extracted directory
            self::removeCache();

            //Error
            throw new Exception("Subtitle extraction failed! (" . $this->imdb . " - " . $language . " - " . $cache . $this->imdb . ".zip)");
        }

        //Move to subtitle dir
        self::scanSubtitle($cache . $this->imdb, $language);

        //Remove cache, zip file and extracted directory
        self::removeCache();
    }

    //Scan for subtitles
    private function scanSubtitle($dir, $language)
    {
        global $logging;

        //Message
        $logging->info("Scanning for subtitles in folder");

        //Check if is directory
        if (is_dir($dir))
        {
            //Objects in the directory
            $objects = scandir($dir);

            foreach ($objects as $object)
            {
                if ($object != "." && $object != "..")
                {
                    //If object is a file, only srt and filesize > 1000 bytes
                    if (pathinfo($dir . "/" . $object, PATHINFO_EXTENSION) == "srt" && filesize($dir . "/" . $object) > 1000)
                    {
                        self::moveSubtitle($dir . "/" . $object, $language);
                    }
                    //Else if dir, enter it
                    elseif (filetype($dir . "/" . $object) == "dir")
                    {
                        self::scanSubtitle($dir . "/" . $object, $language);
                    }
                }
            }
            reset($objects);
        }
    }

    //Move subtitles
    private function moveSubtitle($file, $language)
    {
        global $subtitle, $logging;

        //Check if subtitle does exist
        $md5File = md5_file($file);

        if (file_exists($subtitle . $this->imdb . "_" . $md5File . ".srt"))
        {
            //Remove cache, zip file and extracted directory
            self::removeCache();

            //Error
            throw new Exception("Subtitle does exist");
        }

        //Moving
        $stateR = rename($file, $subtitle . $this->imdb . "_" . $md5File . ".srt");

        if (!$stateR)
        {
            //Remove cache, zip file and extracted directory
            self::removeCache();

            //Error
            throw new Exception("Failed to move subtitle file");
        }

        //DB connection
        Database();

        //Check if in DB
        list($rowCount, $result) = sqlQueryi("SELECT `hash` FROM `subtitle` WHERE `imdb` = ? AND `hash` = ?", array(
            "ss",
            $this->imdb,
            $md5File), true);

        if ($rowCount > 0)
        {
            //Message
            $logging->warning("Removed subtitle(s) from Database");

            //Remove from DB
            sqlQueryi("DELETE FROM `subtitle` WHERE `imdb` = ? AND `hash` = ?", array(
                "ss",
                $this->imdb,
                $md5File));
        }

        //Insert in DB
        sqlQueryi("INSERT INTO `subtitle` (`imdb`,`hash`,`language`) VALUES (?,?,?)", array(
            "sss",
            $this->imdb,
            $md5File,
            $language));

        //Message
        $logging->info("Subtitle added (" . $this->imdb . " - " . $language . ")");
    }

    //Remove cached files
    private function removeCache()
    {
        global $cache;

        unlink($cache . $this->imdb . ".zip");
        self::recursiveDelete($cache . $this->imdb);
    }

    //Can remove a directory recursive
    private function recursiveDelete($dir)
    {
        //Check if is directory
        if (is_dir($dir))
        {
            //Objects in the directory
            $objects = scandir($dir);

            foreach ($objects as $object)
            {
                if ($object != "." && $object != "..")
                {
                    //If directory, then clean it
                    if (filetype($dir . "/" . $object) == "dir")
                    {
                        self::recursiveDelete($dir . "/" . $object);
                    }
                    //File
                    else
                    {
                        //Remove file
                        unlink($dir . "/" . $object);
                    }
                }
            }
            reset($objects);

            //Remove directory
            rmdir($dir);
        }
    }
}

?>