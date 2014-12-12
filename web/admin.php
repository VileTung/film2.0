<?php

/**
 * @author Kevin
 * @copyright 2014
 * @info Admin interface
 */

//Debug
error_reporting(E_ALL);
ini_set("display_errors", 1);

require_once ("bTemplate.php");
require_once ("./../functions/functions.php");

class admin
{
    //Template
    private $bTemplate;

    public function __construct()
    {
        //Template
        $this->bTemplate = new bTemplate();

        //Make the connection
        Database();
    }

    //Start a new process
    private function startProcess()
    {
        global $cache, $success;

        //Random number
        $unique = mt_rand(10000, 65535);

        //Command
        $cmd = "cd /var/www/film2.0 && ./run.php YTS " . $_POST["start"] . " " . $_POST["end"];

        //Save command
        file_put_contents($cache . $unique, $cmd, LOCK_EX);

        //Command file
        $command = $cache . $unique;

        //chmod, otherwise it will fail
        chmod($cache . $unique, 0755);

        //Execute and start the process!
        exec(sprintf("%s  > /dev/null 2>/dev/null &", $command));

        $success = array("state" => true, "message" => "Successfully started a new process!");

        //Redirect, we want to update the page again
        header("refresh:2;url=admin.php");
    }

    //Show HTML
    public function show()
    {
        global $cache;

        //Default, no message bar needed
        $success = array("state" => false, "message" => false);
        $error = array("state" => false, "message" => false);

        if (isset($_POST["process"]) && (isset($_POST["start"]) && is_numeric($_POST["start"])) && (isset($_POST["end"]) && is_numeric($_POST["end"])))
        {
            self::startProcess();
        }

        //Stop a session
        if (isset($_GET["delete"]) && is_numeric($_GET["delete"]))
        {
            //Delete lock file
            if (file_exists($cache . "lock_" . $_GET["delete"]))
            {
                unlink($cache . "lock_" . $_GET["delete"]);

                $success = array("state" => true, "message" => "Successfully stopped the process (" . $_GET["delete"] . ")!");
            }
            else
            {
                $error = array("state" => true, "message" => "Failed to stop the process (" . $_GET["delete"] . ")!");
            }

            //Redirect, we want to lose the $_GET["delete"] in the URL
            header("refresh:2;url=admin.php");
        }

        //Remove finished or aborted session from DB
        if (isset($_GET["clean"]) && is_numeric($_GET["clean"]))
        {
            //Remove
            sqlQueryi("DELETE FROM `sessions` WHERE `sessionId` = ?", array("i", $_GET["clean"]));

            $success = array("state" => true, "message" => "Successfully removed the process from the Database (" . $_GET["clean"] . ")!");

            //Redirect, we want to lose the $_GET["clean"] in the URL
            header("refresh:2;url=admin.php");
        }

        //Get all sessions
        list($rowCount, $result) = sqlQueryi("SELECT * FROM `sessions`", false, true);

        foreach ($result as $key => $value)
        {
            //Remove session entry?
            if ($value["state"] == "Working")
            {
                $result[$key]["working"] = "<a data-href=\"admin.php?delete=" . $value["sessionId"] . "\" data-toggle=\"modal\" data-target=\"#confirm-delete\" href=\"#\">Stop</a>";
            }
            else
            {
                $result[$key]["working"] = "<a href=\"admin.php?clean=" . $value["sessionId"] . "\">Remove</a>";
            }

            //Progressbar colors
            if ($value["progress"] < 34)
            {
                $result[$key]["class"] = "progress-bar-danger";
            }
            elseif ($value["progress"] < 67)
            {
                $result[$key]["class"] = "progress-bar-warning";
            }
            else
            {
                $result[$key]["class"] = "progress-bar-success";
            }
        }

        $this->bTemplate->set("processes", $result);

        //Error bar
        $this->bTemplate->set("isError", $error["state"], true);
        $this->bTemplate->set("error", $error["message"]);

        //Success bar
        $this->bTemplate->set("isSuccess", $success["state"], true);
        $this->bTemplate->set("success", $success["message"]);

        //Print the template!
        print ($this->bTemplate->fetch("admin.tpl"));
    }
}

$show = new admin();
$show->show();

?>