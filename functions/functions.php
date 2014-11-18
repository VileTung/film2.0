<?php

/**
* @author Kevin
* @copyright 2014
* @info Bind functions together
*/

header("Content-Type: text/html; charset=utf-8");

//Guzzle locatie
$guzzle = "/home/Guzzle/vendor/autoload.php";

//Guzzle laden
require_once ($guzzle);

//Guzzle functies
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Stream\Stream;

//We zijn afhankelijk van cURL
if (!function_exists("curl_init") && !file_exists($guzzle))
{
	die("cURL en/of Guzzle zijn niet geïnstalleerd(!!)");
}

//Variables for easy folders
$root = dirname(dirname(__FILE__)) . "/";
$functions = $root."functions/";

//Load additional functions
require_once($functions."scrape.php");
require_once($functions."lightbenc.php");
require_once($functions."torrent.php");
require_once($functions."log.php");
require_once($functions."sqlQuery.php");

//cURL
function cURL($url)
{
	//Nodig voor Guzzle
	$client = new Client();

	try
	{
		//Proberen gegevens te halen
		$request = $client->createRequest("GET", $url, ["timeout"=>60]);
		$response = $client->send($request);
		
		//Gegevens terug sturen
		return array(true, $response->getBody()->getContents());
	}
	catch (RequestException $e)
	{
		//Als het is mislukt, willen we weten waarom
		return array(false, $e->getMessage());
	}
}

?>