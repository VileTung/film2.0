<?php

/**
 * @author Kevin
 * @copyright 2014
 * @info Get settings
 */

class settings
{
    //All our settings
    private $ini;

    //Read
    public function get($key)
    {
        //Make a database connection
        Database();

        list($rowCount, $result) = sqlQueryi("SELECT `value` FROM `settings` WHERE `key` = ? LIMIT 1", array("s", $key), true);

        //Value
        $value = $result[0]["value"];

        //If result
        if ($rowCount > 0)
        {
            //Boolean?
            if ($value == "true")
            {
                return true;
            }
            //Boolean?
            elseif ($value == "false")
            {
                return false;
            }
            //Regular value
            else
            {
                //Return
                return $value;
            }
        }
        //Nothing found
        else
        {
            return "error";
        }
    }

    //Save
    public function set($key, $value)
    {
        //Check if value is a boolean
        if (is_bool($value))
        {
            if ($value)
            {
                $value = "true";
            }
            else
            {
                $value = "false";
            }
        }

        //Make a database connection
        Database();

        sqlQueryi("UPDATE `settings` SET `value` = ? WHERE `key` = ?", array(
            "ss",
            $value,
            $key));
    }
}

?>