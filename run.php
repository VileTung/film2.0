#!/usr/bin/php
<?php

/**
* @author Kevin
* @copyright 2014
* @info The Runner!
*/

//Only CLI (CommandLine)
if (php_sapi_name() == "cli") {

	require_once("functions/functions.php");

	print("Pick one: 'yts': ");
	$retriever = trim(fgets(STDIN));

	if($retriever!="yts" && $retriever!="torrentz")
	{
		print("\nWrong input!\n");
		exit;
	}
	
	print("Starting page: ");
	$min = trim(fgets(STDIN));

	if(!is_numeric($min))
	{
		print("\nWrong input!\n");
		exit;
	}

	print("Ending page: ");	
	$max = trim(fgets(STDIN));

	if(!is_numeric($max))
	{
		print("\nWrong input!\n");
		exit;
	}

	print("\n");
	print("Is this correct?\n");
	print("Retriever: ".$retriever."\n");
	print("Start: ".$min."\n");
	print("End: ".$max."\n");
	print("\n");
	print("Typ 'yes' to continue: ");

	$correct = trim(fgets(STDIN));

	if($correct!="yes")
	{
		print("\nWrong input!\n");
		exit;
	}

	print("\n\n");
	
	if(isset($retriever) && isset($min) && isset($max))
	{
		try{
			//Locks a session
			$locker = new locker();
			
			//Get created session
			$session = $locker->getSession();
			
			//Starts logging
			$logging = new loggen($log.$session.".txt");
			
			//Message
			$logging->info("Starting '".$retriever."' (".$min." until ".$max.")!");
			
			//Initialize YTS
			$yts = new yts();
			
			//Start YTS
			$yts->movies($min,$max);
			
			//Removes lock
			$locker->stop();
		}
		//Error..
		catch(Exception $e) 
		{
			$logging->error($e->getMessage());
		}
	}
	//We're missing some info
	else
	{
		print("We're missing some info!\n");
		exit;
	}
}
//Browser is not allowed..
else
{
	die("I'm a CLI application!\n");
}

?>