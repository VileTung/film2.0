#!/usr/bin/php
<?php

/**
 * @author Kevin
 * @copyright 2014
 * @info Always active process
 */

//Debug
error_reporting(E_ALL);
ini_set("display_errors", 1);

require_once ("functions/functions.php");

//Make the connection
Database();

//Locks a session
$locker = new locker("Infinite Processor");

//Get created session
$session = $locker->getSession();

//Call settings class
$_settings = new settings();

//Set sessionId settings.ini
$_settings->set("pSessionId", $session);

//Starts logging
$logging = new loggen($log . $session);

try
{
    while (true)
    {
        //Get movies
        list($rows, $result) = sqlQueryi("SELECT *, UNIX_TIMESTAMP(`start`) AS `startDate` FROM `process` WHERE NOW() > `start`", false, true); //SELECT *, UNIX_TIMESTAMP(`start`) AS `startDate` FROM `process` WHERE NOW() > `start` AND `wait` NOT IN(SELECT `id` FROM `process`)

        if ($rows > 0)
        {
            foreach ($result as $key => $fetch)
            {
                //Check if active
                $active = exec("ps cax | grep " . $fetch["pid"]);

                if (empty($active) && $fetch["pid"] != "0")
                {
                    //Remove entry from DB, since it's not active
                    sqlQueryi("DELETE FROM `process` WHERE `id` = ?", array("i", $fetch["id"]));

                    //Message
                    $logging->info("Deleted inactive entry ('" . $fetch["process"] . "')");
                }
                elseif ($fetch["pid"] == "0")
                {
                    //Check if we need to wait
                    list($waitRows, $waitResult) = sqlQueryi("SELECT * FROM `process` WHERE `id` = ?", array("i", $fetch["wait"]), true);

                    if ($waitRows == 0)
                    {
                        //Repeated process
                        if ($fetch["repeat"] == "true")
                        {
                            //Repeat every..
                            switch ($fetch["flow"])
                            {
                                case "hour":
                                    $newDate = $fetch["startDate"] + (3600);
                                    break;
                                case "day":
                                    $newDate = $fetch["startDate"] + (3600 * 24);
                                    break;
                                case "week":
                                    $newDate = $fetch["startDate"] + (3600 * 24 * 7);
                                    break;
                                case "month":
                                    $newDate = $fetch["startDate"] + (3600 * 24 * 7 * 4);
                                    break;
                            }

                            //Repeat and wait?
                            if ($fetch["wait"] != 0)
                            {
                                $wait = $fetch["id"];
                            }
                            else
                            {
                                $wait = 0;
                            }

                            //Add new entry
                            sqlQueryi("INSERT INTO `process` (`process`, `wait`, `repeat`, `flow`, `start`) VALUES (?, ?, ?, ?, ?)", array(
                                "sssss",
                                $fetch["process"],
                                $wait,
                                $fetch["repeat"],
                                $fetch["flow"],
                                date("Y-m-d H:i:s", $newDate)));

                            //Message
                            $logging->info("Repeated process ('" . $fetch["process"] . "' starts at " . date("d-m-Y H:i:s", $newDate) . ")");
                        }

                        //Message
                        $logging->info("Start new process ('" . $fetch["process"] . "')");

                        //Clear data variable. Otherwise everything gets appended!
                        unset($data);

                        //Now start the process
                        exec($fetch["process"] . " > /dev/null 2> /dev/null & echo \$! &", $data);

                        //Update entry, add PID
                        sqlQueryi("UPDATE `process` SET `pid` = ? WHERE `id` = ?", array(
                            "ii",
                            $data[0],
                            $fetch["id"]));
                    }
                    else
                    {
                        //Message
                        $logging->info("Can't start, have to wait ('" . $fetch["process"] . "')");
                    }
                }
            }
        }

        //Message
        $logging->info("Locker check");
        $locker->check();

        //Message
        $logging->info("Going to sleep");

        //Set last update in settings.ini
        $_settings->set("pLastUpdate", time());

        //Wait 5 minutes
        sleep(60 * 5);
    }

    //Removes lock, however, the script can't reach here!
    $locker->stop();
}
//Error..
catch (exception $e)
{
    $logging->error($e->getMessage());
}

?>