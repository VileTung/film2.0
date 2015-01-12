<?php

/**
 * @author Kevin
 * @copyright 2014
 * @info Admin actions
 */

if (isset($_POST) && count($_POST) > 0)
{
    if (isset($_POST["process"]) && isset($_POST["start"]) && isset($_POST["end"]) && isset($_POST["wait"]) && isset($_POST["repeat"]) && isset($_POST["flow"]) && isset($_POST["startDate"]))
    {
        require_once ("./../functions/functions.php");

        //JSON Header
        header("Content-type: application/json");

        //Make the connection
        Database();

        //Command
        $cmd = "cd " . $root . " && ./run.php YTS " . $_POST["start"] . " " . $_POST["end"];

        //Add new process to the DB
        sqlQueryi("INSERT INTO `process` (`wait`, `process`, `repeat`, `flow`, `start`) VALUES (?, ?, ?, ?, ?)", array(
            "sssss",
            $_POST["wait"] . "@",
            $cmd,
            $_POST["repeat"],
            $_POST["flow"],
            $_POST["startDate"]));

        //Print
        print (json_encode(array("state" => "alert alert-success", "message" => "Successfully added process to the queue!")));
    }
    //Delete a process
    elseif (isset($_POST["processD"]) && $_POST["processD"] == "delete")
    {
        require_once ("./../functions/functions.php");

        //JSON Header
        header("Content-type: application/json");

        //Make the connection
        Database();

        //Delete process
        sqlQueryi("UPDATE `process` SET `process` = 'sleep 1', `repeat` = 'false' WHERE `id` = ?", array("i", $_POST["id"]));

        //Print
        print (json_encode(array("state" => "alert alert-success", "message" => "Successfully added process to the queue!")));
    }
    //Start the processor
    elseif (isset($_POST["processor"]) && $_POST["processor"] == "start")
    {
        require_once ("./../functions/functions.php");

        //JSON Header
        header("Content-type: application/json");

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

        //Print
        print (json_encode(array("state" => "alert alert-success", "message" => "The master processor is starting!")));
    }
    //Stop a session
    elseif (isset($_POST["stop"]) && is_numeric($_POST["stop"]))
    {
        require_once ("./../functions/functions.php");

        //JSON Header
        header("Content-type: application/json");

        //Make the connection
        Database();

        //Delete lock file
        if (file_exists($cache . "lock_" . $_POST["stop"]))
        {
            unlink($cache . "lock_" . $_POST["stop"]);

            //Mark cache as 'old'
            touch($cacheExpire);

            //Print
            print (json_encode(array("state" => "alert alert-success", "message" => "Successfully stopped the process (" . $_POST["stop"] . ")!")));
        }
        else
        {
            //Print
            print (json_encode(array("state" => "alert alert-danger", "message" => "Failed to stop the process (" . $_POST["stop"] . ")!")));
        }
    }
    //Kill a session
    elseif (isset($_POST["kill"]) && is_numeric($_POST["kill"]))
    {
        require_once ("./../functions/functions.php");

        //JSON Header
        header("Content-type: application/json");

        //Make the connection
        Database();

        //Delete lock file
        if (file_exists($cache . "lock_" . $_POST["kill"]))
        {
            unlink($cache . "lock_" . $_POST["kill"]);
        }

        //Mark cache as 'old'
        touch($cacheExpire);

        //Update state
        sqlQueryi("UPDATE `sessions` SET `state` = ?, `end` = ? WHERE `pid` = ?", array(
            "ssi",
            "Aborted",
            date("Y-m-d H:i:s"),
            $_POST["kill"]));

        //System kill
        exec("kill -9 " . $_POST["kill"]);

        //Print
        print (json_encode(array("state" => "alert alert-success", "message" => "Successfully killed the process (" . $_POST["kill"] . ")!")));
    }
    //Remove finished or aborted session from DB
    elseif (isset($_POST["clean"]) && is_numeric($_POST["clean"]))
    {
        require_once ("./../functions/functions.php");

        //JSON Header
        header("Content-type: application/json");

        //Make the connection
        Database();

        //Remove
        sqlQueryi("DELETE FROM `sessions` WHERE `sessionId` = ?", array("i", $_POST["clean"]));

        //Print
        print (json_encode(array("state" => "alert alert-success", "message" => "Successfully removed the process from the Database (" . $_POST["clean"] . ")!")));
    }
    //Reload/refresh processes
    elseif (isset($_POST["refresh"]) && $_POST["refresh"] == "process")
    {
        define("action.php", true);

        require_once ("admin.php");

        $show = new admin();

        //Refresh processes
        if ($_POST["type"] == "session")
        {
            $data = $show->getSession();

            //Loop through data
            foreach ($data as $key => $value)
            {
                print ("<tr>");
                print ("<td>" . $value["process"] . "</td>");
                print ("<td>");
                print ("<span style=\"color: " . $value["active"] . "\">" . $value["state"] . "</span>");
                print ("</td>");
                print ("<td>");
                print ("<div class=\"progress\" style=\"margin-bottom: 0px;\">");
                print ("<div data-toggle=\"tooltip\" title=\"Complete: " . $value["progress"] . "%\" style=\"width: " . $value["progress"] . "%;\" aria-valuemax=\"100\" aria-valuemin=\"0\" aria-valuenow=\"" . $value["progress"] . "\" role=\"progressbar\" class=\"progress-bar " . $value["class"] . "\"><span class=\"sr-only\">" . $value["progress"] . "% complete</span>");
                print ("</div>");
                print ("</div>");
                print ("</td>");
                print ("<td>" . $value["start"] . "</td>");
                print ("<td>" . $value["end"] . "</td>");
                print ("<td><a target=\"_blank\" href=\"./../log/" . $value["sessionId"] . ".html\">Click</a>");
                print ("</td>");
                print ("<td>" . $value["working"] . "</td>");
                print ("</tr>");
            }
        }
        elseif ($_POST["type"] == "waitFor")
        {
            $data = $show->waitFor();

            //Default value
            print ("<option value=\"0\">None</option>");

            //Loop through data
            foreach ($data as $key => $value)
            {
                print ("<option value=\"" . $value["id"] . "\">" . $value["value"] . "</option>");
            }
        }
    }
    //Mark cache as old
    elseif (isset($_POST["markCache"]) && $_POST["markCache"] == "cache")
    {
        require_once ("./../functions/functions.php");

        //JSON Header
        header("Content-type: application/json");

        //Mark cache as 'old'
        if (!touch($cacheExpire))
        {
            //Print
            print (json_encode(array("state" => "alert alert-danger", "message" => "Failed to mark cache as old!")));
        }
        else
        {
            //Print
            print (json_encode(array("state" => "alert alert-success", "message" => "Marked cache as old!")));
        }
    }
    //Wrong values or missing $_POST's
    else
    {
        //JSON Header
        header("Content-type: application/json");

        //Print
        print (json_encode(array("state" => "alert alert-danger", "message" => "Invalid variables received!")));
    }
}
else
{
    header($_SERVER["SERVER_PROTOCOL"] . " 301 Moved Permanently");
    header("Location: index.php");
}

?>