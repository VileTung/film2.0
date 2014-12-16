<?php

/**
 * @author Kevin
 * @copyright 2014
 * @info Admin actions
 */

if (isset($_POST["process"]) && (isset($_POST["start"]) && is_numeric($_POST["start"])) && (isset($_POST["end"]) && is_numeric($_POST["end"])))
{
    require_once ("./../functions/functions.php");

    //JSON Header
    header("Content-type: application/json");

    //Make the connection
    Database();

    //Random number
    $unique = mt_rand(10000, 65535);

    //Command
    $cmd = "cd " . $root . " && ./run.php YTS " . $_POST["start"] . " " . $_POST["end"];

    //Save command
    file_put_contents($cache . $unique, $cmd, LOCK_EX);

    //Command file
    $command = $cache . $unique;

    //chmod, otherwise it will fail
    chmod($cache . $unique, 0755);

    //Execute and start the process!
    exec(sprintf("%s > /dev/null 2> /dev/null &", $command));

    //Print
    print (json_encode(array("state" => "alert alert-success", "message" => "Successfully started a new process!")));
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
    $data = $show->getProcess();

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
	
	//JavaScript need to be reloaded too..
	//print("<script src=\"js/admin.js\"></script>");
}
else
{
    header($_SERVER["SERVER_PROTOCOL"] . " 301 Moved Permanently");
    header("Location: index.php");
}

?>