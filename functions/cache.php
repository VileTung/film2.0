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
    public function __construct($id = false, $cli = true)
    {
        global $logging, $cache;

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
            if ($future > $now)
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
}

?>