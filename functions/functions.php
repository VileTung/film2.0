<?php

/**
 * @author Kevin
 * @copyright 2014
 * @info Bind functions together
 */

//Debug
error_reporting(E_ALL);
ini_set("display_errors", 1);

header("Content-Type: text/html; charset=utf-8");

//Guzzle location
$guzzle = "/home/Guzzle/vendor/autoload.php";

//We need Guzzle/cURL
if (!function_exists("curl_init") && !file_exists($guzzle))
{
    die("cURL en/of Guzzle zijn niet geïnstalleerd(!!)");
}

//Load Guzzle
require_once ($guzzle);

//Guzzle functies
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Stream\Stream;

//Variables for easy folder access
$root = dirname(dirname(__file__)) . "/";

$cache = $root . "cache/";
$functions = $root . "functions/";
$cacheExpire = $functions . "cache";
$log = $root . "log/";
$poster = $root . "poster/";
$scraper = $root . "scraper/";
$subtitle = $root . "subtitle/";

//Load additional functions
require_once ($functions . "cache.php");
require_once ($functions . "error.php");
require_once ($functions . "imdb.php");
require_once ($functions . "lightbenc.php");
require_once ($functions . "lock.php");
require_once ($functions . "log.php");
require_once ($functions . "regex.php");
require_once ($functions . "scrape.php");
require_once ($functions . "sqlQuery.php");
require_once ($functions . "subtitle.php");
require_once ($functions . "torrent.php");

//Scrapers
require_once ($scraper . "openSubtitles.php");
require_once ($scraper . "scraper.php");
require_once ($scraper . "yts.php");

//Guzzle client
$client = new Client();

//cURL
function cURL($url)
{
    global $client;

    try
    {
        //Try
        $request = $client->createRequest("GET", $url, ["timeout" => 60, "cookies" => true]);
        $response = $client->send($request);

        //Return data
        return array(true, $response->getBody()->getContents());
    }
    catch (RequestException $e)
    {
        //Failed, send information
        return array(false, $e->getMessage());
    }
}

?>