<?php

/**
 * @author Kevin
 * @copyright 2015
 * @info Server side AJaX calls
 */

//Check if there is any POST-data
if (count($_POST) > 0)
{
    //Security check
    if ($_POST["check" . date("Y")] == date("dmY"))
    {
        //Load
        require_once ("./../functions/functions.php");

        //Start
        $_ajaxAction = new ajaxAction();

        //JSON
        if ($_POST["return"] == "json")
        {
            //JSON Header
            header("Content-type: application/json; charset=utf-8");

            //Admin - Start master process
            if ($_POST["type"] == "adminMasterProcess")
            {

                list($state, $message) = $_ajaxAction->adminMasterProcess();

                //Print
                print (json_encode(array("state" => $state, "message" => $message)));
            }
            //Admin - Start master process
            elseif ($_POST["type"] == "adminAddProcess")
            {
                list($state, $message) = $_ajaxAction->addProcess($_POST);

                //Print
                print (json_encode(array("state" => $state, "message" => $message)));
            }
            //Admin - Remove stopped session
            elseif ($_POST["type"] == "adminRemoveSession")
            {
                list($state, $message) = $_ajaxAction->removeSession($_POST);

                //Print
                print (json_encode(array("state" => $state, "message" => $message)));
            }
            //Admin - Stop an active session
            elseif ($_POST["type"] == "adminStopSession")
            {
                list($state, $message) = $_ajaxAction->stopSession($_POST);

                //Print
                print (json_encode(array("state" => $state, "message" => $message)));
            }
            //Admin - Kill an active (or dead) session
            elseif ($_POST["type"] == "adminKillSession")
            {
                list($state, $message) = $_ajaxAction->killSession($_POST);

                //Print
                print (json_encode(array("state" => $state, "message" => $message)));
            }
            //Admin - Remove process from the queue
            elseif ($_POST["type"] == "adminRemoveProcess")
            {
                list($state, $message) = $_ajaxAction->removeProcess($_POST);

                //Print
                print (json_encode(array("state" => $state, "message" => $message)));
            }
            //Admin - Remove buildCache lock
            elseif ($_POST["type"] == "adminRemoveCacheLock")
            {
                $_settings = new settings();

                //Settings, remove buildCache lock
                $_settings->set("buildCache", false);

                //Print
                print (json_encode(array("state" => "alert alert-success", "message" => "BuildCache lock has been removed!")));
            }
        }
        //HTML
        elseif ($_POST["return"] == "html")
        {
            //Default page
            if ($_POST["type"] == "index")
            {
                $bTemplate = new bTemplate();

                //Get data
                list($count, $data) = $_ajaxAction->index();

                //Got data
                if ($data)
                {
                    //Set data
                    $bTemplate->set("movies", $data);

                    //Count lower then 30, we don't need 'load more'
                    $bTemplate->set("loadMore", ($count < 30 ? false : true), true);

                    print ($bTemplate->fetch("template/movies.html"));
                }
                //No data
                else
                {
                    //Return nothing
                    print ("");
                }
            }
            //Admin - Master process state
            elseif ($_POST["type"] == "adminState")
            {
                $bTemplate = new bTemplate();

                $bTemplate->set("process", $_ajaxAction->adminState());

                print ($bTemplate->fetch("template/adminState.html"));

            }
            //Admin - Master process state
            elseif ($_POST["type"] == "adminBuildCacheState")
            {
                $bTemplate = new bTemplate();

                $bTemplate->set("buildCache", $_ajaxAction->adminBuildCacheState());

                print ($bTemplate->fetch("template/adminBuildCacheState.html"));

            }
            //Admin - Add process form
            elseif ($_POST["type"] == "adminProcessForm")
            {
                $bTemplate = new bTemplate();

                //Create new array
                $string = array();
                $string[] = array("id" => "0", "value" => "None");

                //All processes
                $data = $_ajaxAction->getProcess();

                if (is_array($data))
                {
                    foreach ($data as $key => $value)
                    {
                        $string[] = array("id" => $value["id"], "value" => $value["process"] . " - " . $value["repeat"] . " - " . $value["start"]);
                    }
                }

                $bTemplate->set("waitFor", $string);

                print ($bTemplate->fetch("template/adminAdd.html"));

            }
            //Admin - Process options list
            elseif ($_POST["type"] == "adminOptions")
            {
                $bTemplate = new bTemplate();

                //YTS
                if ($_POST["option"] == "yts")
                {
                    print ($bTemplate->fetch("template/adminYTS.html"));
                }
            }
            //Admin - Get sessions list
            elseif ($_POST["type"] == "adminSessions")
            {
                $bTemplate = new bTemplate();

                //Create new array
                $string = array();

                //All sessions
                $data = $_ajaxAction->getSession();

                if (is_array($data))
                {
                    foreach ($data as $key => $value)
                    {
                        //Check if alive
                        if (processAlive($value["pid"]))
                        {
                            $active = "green";
                        }
                        else
                        {
                            $active = "red";
                        }

                        //Progressbar colors
                        if ($value["progress"] < 34)
                        {
                            $progressBar = "progress-bar-danger";
                        }
                        elseif ($value["progress"] < 67)
                        {
                            $progressBar = "progress-bar-warning";
                        }
                        else
                        {
                            $progressBar = "progress-bar-success";
                        }

                        //Remove or stop?
                        if ($value["state"] == "Working")
                        {
                            $bTemplate->set("working", true, true);
                        }
                        else
                        {
                            $bTemplate->set("working", false, true);
                        }

                        $string[] = array(
                            "process" => $value["process"],
                            "state" => $value["state"],
                            "progress" => $value["progress"],
                            "class" => $progressBar,
                            "start" => $value["start"],
                            "end" => $value["end"],
                            "sessionId" => $value["sessionId"],
                            "pid" => $value["pid"],
                            "active" => $active);
                    }
                }

                $bTemplate->set("sessions", $string);

                print ($bTemplate->fetch("template/adminSession.html"));
            }
            //Admin - Get processes list
            elseif ($_POST["type"] == "adminProcesses")
            {
                $bTemplate = new bTemplate();

                //All processes
                $data = $_ajaxAction->getProcess();

                if (is_array($data))
                {
                    $bTemplate->set("processes", $data);
                }
                //No data
                else
                {
                    $bTemplate->set("processes", array());
                }

                print ($bTemplate->fetch("template/adminProcess.html"));
            }
        }
    }
}
//Show nothing
else
{
    header($_SERVER["SERVER_PROTOCOL"] . " 301 Moved Permanently");
    header("Location: index.php");
}

class ajaxAction
{
    //Get data for the index page
    public function index()
    {
        //Start data class
        $_getMovies = new getMovies();

        //Set retrieved data
        $_getMovies->__set("begin", $_POST["begin"]);
        $_getMovies->__set("limit", $_POST["limit"]);

        $_getMovies->__set("qBy", $_POST["by"]);
        $_getMovies->__set("qGenre", $_POST["genre"]);
        $_getMovies->__set("qSort", $_POST["sort"]);
        $_getMovies->__set("qTitle", $_POST["title"]);

        //Title cut
        $_getMovies->__set("titleCut", true);
        $_getMovies->__set("titleLength", 18);

        //Movie and other data
        $data = $_getMovies->data();

        //Formated data
        $newData = array();

        //Test if there are any rows
        if (is_array($data))
        {
            //Do some magic
            foreach ($data as $key => $fetch)
            {
                //New data
                $torrents = array();

                //Loop through torrents
                foreach ($fetch["torrent"] as $keyT => $fetchT)
                {
                    //Build magnet link
                    $magnet = "magnet:?xt=urn:btih:" . $fetchT["hash"] . "&dn=" . urlencode($fetch["title"]);

                    //Default
                    $average = 0;
                    $count = 0;
                    $ratio = 0;

                    //Tracker info
                    $trackerState = "";

                    //Loop through trackers
                    foreach ($fetchT["trackers"] as $keyR => $fetchR)
                    {
                        //Split URL
                        $urlInfo = @parse_url($fetchR["tracker"]);

                        //Tracker info for the website
                        $trackerState .= ($trackerState != "" ? "<br />" : "") . $urlInfo["host"] . " S:" . $fetchR["seeders"] . "/L:" . $fetchR["leechers"];

                        //Only if values are not empty
                        if ($fetchR["seeders"] > 0 && $fetchR["leechers"] > 0)
                        {
                            $average += $fetchR["seeders"] / $fetchR["leechers"];
                            $count++;
                        }

                        //Add trackers to magnet link
                        $magnet .= "&tr=" . urlencode($fetchR["tracker"]);
                    }

                    //Calculate state
                    if ($average == 0)
                    {
                        $state = "Dead";
                    }
                    elseif (($average / $count) > 2)
                    {
                        $state = "Good";
                    }
                    elseif (($average / $count) >= 1)
                    {
                        $state = "OK";
                    }
                    else
                    {
                        $state = "Bad";
                    }

                    $torrents[] = array(
                        "quality" => $fetchT["quality"],
                        "size" => $fetchT["sizeReadable"],
                        "availability" => $trackerState,
                        "state" => $state,
                        "url" => $magnet);
                }

                //New data
                $subtitles = array();

                //Loop through subtitles
                if (isset($fetch["subtitle"]) && count($fetch["subtitle"]) > 0)
                {
                    foreach ($fetch["subtitle"] as $keyS => $fetchS)
                    {
                        //Show it nicely
                        if ($fetchS["language"] == "nl")
                        {
                            $language = "Dutch";
                        }
                        elseif ($fetchS["language"] == "en")
                        {
                            $language = "English";
                        }

                        $subtitles[] = array("url" => "./subtitle/" . $fetchS["imdb"] . "_" . $fetchS["hash"] . ".srt", "language" => $language);
                    }
                }
                //No subtitles
                else
                {
                    $subtitles[] = array("url" => "#", "language" => "<em>None</em>");
                }

                //New data
                $genres = "";

                //Genres
                foreach ($fetch["genre"] as $keyG => $fetchG)
                {
                    $genres .= ($genres != "" ? ", " : "") . $fetchG["genre"];
                }

                //Build new data
                $newData[] = array(
                    "description" => $fetch["description"],
                    "imdb" => $fetch["imdb"],
                    "title" => $fetch["title"],
                    "titleCutted" => $fetch["titleCutted"],
                    "year" => date("Y", strtotime($fetch["release"])),
                    "rating" => $fetch["rating"],
                    "runtime" => $fetch["runtime"],
                    "genres" => $genres,
                    "torrents" => $torrents,
                    "subtitles" => $subtitles);
            }

            //Return all data
            return array(count($data), $newData);
        }
        else
        {
            //Return nothing
            return array(0, false);
        }
    }

    //Get data for the admin page, master process state
    public function adminState()
    {
        //Get process last update from settings
        $_settings = new settings();

        //Active
        if (processAlive($_settings->get("pPID")))
        {
            //Calc difference
            $difference = time() - $_settings->get("pLastUpdate");

            //OK
            if ($difference > 0 && $difference < (60 * 5))
            {
                $process = array(
                    "class" => "success",
                    "state" => "Running!",
                    "text" => "Sanity will take place in about <strong>" . self::convertSeconds((60 * 5) - $difference) . "</strong>.",
                    "start" => "disabled",
                    "stop" => "");
            }
            //Something is wrong
            else
            {
                $process = array(
                    "class" => "success",
                    "state" => "Running!",
                    "text" => "Master process is active and sanity should've taken place <strong>" . self::convertSeconds($difference - (60 *
                        5)) . "</strong> ago!",
                    "start" => "disabled",
                    "stop" => "");
            }
        }
        //Process is not running
        else
        {
            $process = array(
                "class" => "danger",
                "state" => "Stopped!",
                "text" => "The master process is not active!",
                "start" => "",
                "stop" => "disabled");
        }

        //Return data
        return $process;
    }

    //Get data for the admin page, buidCache state
    public function adminBuildCacheState()
    {
        //Get process last update from settings
        $_settings = new settings();

        if ($_settings->get("buildCache"))
        {
            return array("class" => "danger", "state" => "Working!");
        }
        else
        {
            return array("class" => "success", "state" => "Stopped!");
        }
    }

    //Start/stop the master process
    public function adminMasterProcess()
    {
        global $cache, $root;

        $_settings = new settings();

        //Check if active
        if (processAlive($_settings->get("pPID")))
        {
            //True, stop
            $state = self::stopProcess($_settings->get("pSessionId"));

            if ($state)
            {
                return array("alert alert-success", "The master process should be stopping!");
            }
            else
            {
                return array("alert alert-danger", "Failed to stop the master process!");
            }
        }
        //Not active, start
        else
        {
            //Command
            $cmd = "cd " . $root . " && ./process.php";

            //Save command
            file_put_contents($cache . "process", $cmd, LOCK_EX);

            //Command file
            $command = $cache . "process";

            //chmod, otherwise it will fail
            chmod($cache . "process", 0755);

            //Execute and start the process!
            exec(sprintf("%s > /dev/null 2> /dev/null &", $command));

            return array("alert alert-success", "The master process should be starting!");
        }
    }

    //Remove a stopped session
    public function removeSession($data)
    {
        //Make the connection
        Database();

        //Remove
        sqlQueryi("DELETE FROM `sessions` WHERE `sessionId` = ?", array("i", $data["id"]));

        //Return
        return array("alert alert-success", "Successfully removed the process (" . $data["id"] . ")!");
    }

    //Stop an active session
    public function stopSession($data)
    {
        global $cache, $cacheExpire;

        //Delete lock file
        if (file_exists($cache . "lock_" . $data["id"]))
        {
            unlink($cache . "lock_" . $data["id"]);

            //Mark cache as 'old'
            touch($cacheExpire);

            //Return
            return array("alert alert-success", "Successfully stopped the process (" . $data["id"] . ")!");
        }
        else
        {
            //Return
            return array("alert alert-danger", "Failed to stop the process (" . $data["id"] . ")!");
        }
    }

    //Kill an active (or dead) session
    public function killSession($data)
    {
        global $cache, $cacheExpire;

        //Make the connection
        Database();

        //Delete lock file
        if (file_exists($cache . "lock_" . $data["id"]))
        {
            unlink($cache . "lock_" . $data["id"]);
        }

        //Mark cache as 'old'
        touch($cacheExpire);

        //Update state
        sqlQueryi("UPDATE `sessions` SET `state` = ?, `end` = ? WHERE `pid` = ?", array(
            "ssi",
            "Aborted",
            date("Y-m-d H:i:s"),
            $data["id"]));

        //System kill
        exec("kill -9 " . $data["id"]);

        //Return
        return array("alert alert-success", "Successfully killed the process (" . $data["id"] . ")!");
    }

    //Get all sessions
    public function getSession()
    {
        //Make the connection
        Database();

        //Get all processes
        list($rows, $result) = sqlQueryi("SELECT * FROM `sessions`", false, true);

        return $result;
    }

    //Get all processes
    public function getProcess()
    {
        //Make the connection
        Database();

        //Get all processes
        list($rows, $result) = sqlQueryi("SELECT * FROM `process`", false, true);

        return $result;
    }

    //Add a process to the database
    public function addProcess($data = array())
    {
        global $root;

        //Make the connection
        Database();

        //Legal?
        $legal = false;

        //YTS
        if ($data["process"] == "yts" && isset($data["begin"]) && isset($data["end"]))
        {
            //Command
            $cmd = "cd " . $root . " && ./run.php YTS " . $data["begin"] . " " . $data["end"];

            $legal = true;
        }
        //buildCache
        elseif ($data["process"] == "buildCache")
        {
            //Command
            $cmd = "cd " . $root . " && ./run.php buildCache";

            $legal = true;
        }

        if ($legal)
        {
            //Add new process to the DB
            sqlQueryi("INSERT INTO `process` (`wait`, `process`, `repeat`, `flow`, `start`) VALUES (?, ?, ?, ?, NOW())", array(
                "ssss",
                $data["wait"] . "@",
                $cmd,
                $data["repeat"],
                $data["flow"]));

            return array("alert alert-success", "Added a new process to the queue!");
        }

        //No process selected
        return array("alert alert-danger", "No valid process was selected!");
    }

    //Stop a process, using the friendly way
    public function stopProcess($session)
    {
        global $cache, $cacheExpire;

        //Delete lock file
        if (file_exists($cache . "lock_" . $session))
        {
            unlink($cache . "lock_" . $session);

            //Mark cache as 'old'
            touch($cacheExpire);

            return true;
        }
        //Failed
        else
        {
            return false;
        }
    }

    //Remove process entry
    public function removeProcess($data)
    {
        //Make the connection
        Database();

        //Delete process
        sqlQueryi("UPDATE `process` SET `process` = 'sleep 1', `repeat` = 'false' WHERE `id` = ?", array("i", $data["id"]));

        return array("alert alert-success", "Successfully removed the process from the queue!");
    }

    //Convert seconds to a readable elapsed time
    private function convertSeconds($secondsGiven)
    {
        //To prevent notices warnings
        $return = "";

        $month = 0;
        $day = 0;
        $hour = 0;
        $minute = 0;

        //Months
        if (floor($secondsGiven / 2592000) > 0)
        {
            $month = floor($secondsGiven / 2592000);

            $return = $return . " " . $month . " " . ($month > 1 ? "months" : "month");
        }

        //Days
        if (floor(($secondsGiven % 2592000) / 86400) > 0)
        {
            $day = floor(($secondsGiven % 2592000) / 86400);

            $return = $return . ($month > 0 ? "," : "") . " " . $day . " " . ($day > 1 ? "days" : "day");
        }

        //Hours
        if (floor(($secondsGiven % 86400) / 3600) > 0)
        {
            $hour = floor(($secondsGiven % 86400) / 3600);

            $return = $return . ($day > 0 ? "," : "") . " " . $hour . " " . ($hour > 1 ? "hours" : "hour");
        }

        //Minutes
        if (floor(($secondsGiven % 3600) / 60) > 0)
        {
            $minute = floor(($secondsGiven % 3600) / 60);

            $return = $return . ($hour > 0 ? "," : "") . " " . $minute . " " . ($minute > 1 ? "minutes" : "minute");
        }

        //Seconds
        if (floor($secondsGiven % 60) > 0)
        {
            $return = $return . ($secondsGiven > 59 ? " and " : " ") . ($secondsGiven % 60) . " seconds";
        }


        return $return;
    }
}

?>