<?php

/**
* @author Kevin
* @copyright 2014
* @info Simple MySQLnd function
*/

//MySQL verbinding
function Database($verbinden = true)
{
	global $MySQLi;

	unset($GLOBALS["MySQLi"]);
	
	$MySQLi = mysqli_connect("localhost", "root", "8206479Ef", "film2");

	if ($MySQLi->connect_errno)
	{
		die("Failed to connect to MySQL: (" . $MySQLi->connect_errno . ") " . $MySQLi->connect_error);
	}
	
	//We need this
	$GLOBALS["MySQLi"] = $MySQLi;
}

function sqlQueryi($query, $parameters = false, $result = false)
{
	global $MySQLi;

	//Prepare SQL
	$execute = $MySQLi->prepare($query);

	if ($parameters)
	{
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

	//Error
	if (!$execute)
	{
		return("SQLi fout: " . $MySQLi->error . "\nQuery: " . $query);
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
		while ($gegevens = $result->fetch_array(MYSQLI_ASSOC))
		{
			$return[] = $gegevens;
		}

		//Return
		return array($result->num_rows, $return);
	}
	//Just debugging
	//else
	//{
	//	return array($MySQLi, $execute, $MySQLi->error);
	//}
}

?>