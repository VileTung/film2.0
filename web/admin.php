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
        list($rowCount, $result) = sqlQueryi("SELECT * FROM `sessions`", false, true);

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

    //Get the 'wait for' processes
    public function waitFor()
    {
        //Get all processes
        list($rowCount, $result) = sqlQueryi("SELECT * FROM `process`", false, true);

        //Create new array
        $string = array();

        foreach ($result as $key => $value)
        {
            $string[] = array("id" => $value["id"], "value" => $value["process"] . " - " . $value["repeat"] . " - " . $value["start"]);
        }

        return $string;
    }

    //Show HTML
    public function show()
    {
        //Get all sessions
        $this->bTemplate->set("processes", self::getProcess());

        //Get all 'wait for' processes
        $this->bTemplate->set("waitFor", self::waitFor());

        //Get process last update from settings
        $_settings = new settings();

        //Calc difference
        $difference = time() - $_settings->get("pLastUpdate");

        //OK
        if ($difference > 0 && $difference < (60 * 5))
        {
            $this->bTemplate->set("sanity", array(
                "class" => "success",
                "state" => "Running!",
                "text" => "Sanity will take place in about <strong>" . self::convertSeconds((60 * 5) - $difference) . "</strong>.",
                "start" => "disabled",
                "stop" => "",
                "type" => "stop",
                "sessionId" => $_settings->get("pSessionId")));
        }
        //Process is not running
        else
        {
            $this->bTemplate->set("sanity", array(
                "class" => "danger",
                "state" => "Stopped!",
                "text" => "Sanity should've taken place <strong>" . self::convertSeconds($difference - (60 * 5)) . "</strong> ago!",
                "start" => "",
                "stop" => "disabled",
                "type" => "start",
                "sessionId" => ""));
        }

        //Print the template!
        print ($this->bTemplate->fetch("admin.tpl"));
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

if (!defined("action.php"))
{
    $show = new admin();
    $show->show();
}

?>