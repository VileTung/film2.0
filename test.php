#!/usr/bin/php
<?php
	
/*$a1 = array("1","2");
$a2 = array("9","0");

$test = array_merge($a1,$a2);

var_dump($a1);
var_dump($a2);
var_dump($test);*/
	
	
	
require_once("functions/functions.php");

$logging = new loggen($log."test.txt");

//YIFY SUBTITLE
//$t = new yts();
//$t -> subtitle("1646971");

//TORRENTZ.EU

$test = new torrentz(0,0,2014);
$test->movies();





/*
//$test->yify("123");


	
	//$test = "BKMGTP";
	
	//$test = array("B","kB","MB","GB","TB","PT");
	
	//var_dump($test[0]);
	
	die();
	
//require_once("functies/log.php");

$log = new loggen("/var/www/film2.0/test.txt");

function kevin()
{
global $log;

$log->info("info testen");
$log->debug("debug eventjes testen");
$log->warning("inside an funckion");
$log->error("inside an funckion");

}

kevin();



$log->info("test");*/

//var_dump(preg_match("~^[0-9a-f]{40}$~i","a11A2AC68A11634E980F265CB1433C599D017A759"));
	


/*Database();

$tor = new torrent(null);

try{
$tor->file("https://yts.re/download/start/11A2AC68A11634E980F265CB1433C599D017A759.torrent");
$tor->scrape();
$tor->database(true,"Kevin","imdb","quality","yts","0");
} catch(Exception $e) {
    print($e->getMessage());
}*/
	
//$test = new regex;
//$test2 = $test->main("year");

//var_dump($test2);
	
//$imdb = new imdb("How to train your dragon 2010");

//var_dump($imdb->getInfo());

//echo "hallo ollah";

/*	Database();
	
	$hash = strtolower("11A2AC68A11634E980F265CB1433C599D017A759");
	$tracker = "http://localhost.nl";
	
var_dump(sqlQueryi("SELECT * FROM `trackers` WHER `hash` = ? AND `tracker` = ?", array(
                        "ss",
                        $hash,
                        $tracker), true));
						
$imdb = new imdb;
try{
$imdb->getInfo("1355630");
var_dump($imdb->database());
} catch(Exception $e) {
    print($e->getMessage());
}
	
	$test = new locker("123456");
	
	var_dump($test);
	
	die();exit();
	
	
	try{
	
$logging = new loggen($log."test.txt");
	
	//Message
	$logging->info("IMDB");			
	$imdb = new imdb;
	$imdb->getInfo("1811371");
					
	//Message
	$logging->info("Movie DB");
	$imdb->database();
} catch(Exception $e) {
    print($e->getMessage());
}
	
	
	die(); exit();
	
	
	
	
	
try{
	
	$locker = new locker();
	$logging = new loggen($log."test.txt");
	
	$yts = new yts(0,0);
	
	$locker->stop();

} catch(Exception $e) {
    $logging->error($e->getMessage());
}*/