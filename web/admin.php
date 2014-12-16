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

    //Get processes
    public function getProcess()
    {
        //Get all sessions
        list($rowCount, $result) = sqlQueryi("SELECT * FROM `sessions`", false, true, false);

        foreach ($result as $key => $value)
        {
            $process = exec("ps cax | grep " . $value["pid"]);

            //Check if PID is still active
            if (!empty($process))
            {
                $result[$key]["active"] = "green";
            }
            else
            {
                $result[$key]["active"] = "red";
            }

            //Remove session entry?
            if ($value["state"] == "Working")
            {
                $result[$key]["working"] = "<a data-pid=\"" . $value["pid"] . "\" data-session=\"" . $value["sessionId"] . "\" data-toggle=\"modal\" data-target=\"#confirm-delete\" href=\"#\">Stop</a>";
            }
            else
            {
                $result[$key]["working"] = "<a data-session=\"" . $value["sessionId"] . "\" data-href=\"action.php\" href=\"#\" class=\"clean\">Remove</a>";
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

        return $result;
    }

    //Show HTML
    public function show()
    {
        $result = self::getProcess();

        $this->bTemplate->set("processes", $result);

        //Print the template!
        print ($this->bTemplate->fetch("admin.tpl"));
    }
}

if (!defined("action.php"))
{
    $show = new admin();
    $show->show();
}

?>