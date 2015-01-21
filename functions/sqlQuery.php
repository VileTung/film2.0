<?php

/**
 * @author Kevin
 * @copyright 2014
 * @info Simple MySQLnd function
 */

//MySQL connection
function Database($connect = true)
{
    global $MySQLi;

    unset($GLOBALS["MySQLi"]);

    $MySQLi = mysqli_connect("localhost", "root", "8206479Ef", "film2.0-dev");

    if (!$MySQLi)
    {
        die("Failed to connect to MySQL");
    }

    //We need this
    $GLOBALS["MySQLi"] = $MySQLi;
}

function sqlQueryi($query, $parameters = false, $result = false, $useCache = false, $buildCache = false)
{
    global $MySQLi, $cache, $cacheExpire;

    //Strip duplicated and unnecessary spaces
    $query = preg_replace("~(?:\"[^\"]++\"|'[^']++'|`[^`]++`)(*SKIP)(*F)|\s{1,}~i", " ", $query);

    $md5Query = md5($query . serialize($parameters));

    //Check if we want cache
    if ($useCache && $result)
    {
        //Check if we have cache
        if (file_exists($cache . "sql_" . $md5Query) && filemtime($cache . "sql_" . $md5Query) > filemtime($cacheExpire))
        {
            //Valid cache, read it
            $data = unserialize(file_get_contents($cache . "sql_" . $md5Query));

            //Return and quit this function
            return array($data[0], $data[1]);
        }
    }

    //Make sure we have a connection
    if (!$MySQLi)
    {
        die("There is no active MySQL connection!");
    }

    //Prepare SQL
    $execute = $MySQLi->prepare($query);

    //Error
    if (!$execute)
    {
        die("SQLi error: " . $MySQLi->error . "\nQuery: " . $query);
    }

    if ($parameters)
    {
        //Make sure everything is correct
        if (strlen($parameters[0]) != (count($parameters) - 1))
        {
            die("Parameters do not match, " . strlen($parameters[0]) . " & " . (count($parameters) - 1));
        }

        //Place parameters in a new array as reference
        $parametersRef = array();

        //Type
        $parametersRef[] = &$parameters[0];

        for ($i = 1; $i < (count($parameters)); $i++)
        {
            $parametersRef[] = &$parameters[$i];
        }

        //Parameters
        call_user_func_array(array($execute, "bind_param"), $parametersRef);
    }

    //Execute SQL
    $execute->execute();

    //Only if we want result
    if ($execute && $result)
    {
        //Result
        $result = $execute->get_result();

        //Empty array
        $return = array();

        //Data
        while ($data = $result->fetch_array(MYSQLI_ASSOC))
        {
            $return[] = $data;
        }

        //Call settings class
        $_settings = new settings();

        //Cache
        if (($useCache && !$_settings->get("buildCache")) || $buildCache)
        {
            //Data
            $data = array($result->num_rows, $return);

            //Save
            file_put_contents($cache . "sql_" . $md5Query, serialize($data), LOCK_EX);
        }

        //Return
        return array($result->num_rows, $return);
    }
}

?>