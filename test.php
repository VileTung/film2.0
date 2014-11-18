<?php
	
require_once("functies/log.php");

$log = new loggen("/var/www/film2.0/test.txt");

function kevin()
{
global $log;

$log->error("inside an funckion");

}

kevin();



$log->info("test");

