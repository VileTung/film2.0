<?php

/**
 * @author Kevin
 * @copyright 2014
 * @info A solid scrape function
 */

function scrape($url, $info_hash, $method = "scrape")
{
	//Announce
	if ($method == "announce")
	{
		$get_params = array("info_hash" => pack("H*", $info_hash),
			"peer_id" => "-UT1820-5dmPcUOYGnrx",
			"port" => rand(10000, 65535),
			"uploaded" => 0,
			"no_peer_id" => 1,
			"downloaded" => 0,
			"compact" => 1,
			"left" => 1,
			"numwant" => 9999);
	} //Scrape
	else
	{
		$orgineleURL = $url;
		$url = str_replace("announce", "scrape", $url);
		$get_params = array("info_hash" => pack("H*", $info_hash));
	}

	//Split URL
	$urlInfo = @parse_url($url);

	//Poort bepalen
	if (!isset($urlInfo["port"]) || empty($urlInfo["port"]))
	{
		switch ($urlInfo['scheme'])
		{
			//HTTP
			case 'http':
				$urlInfo["port"] = 80;
				break;
			//UDP
			case 'udp':
				$urlInfo["port"] = 80;
				break;
			//HTTPS
			case 'https':
				$urlInfo["port"] = 443;
				break;
			//Error!
			default:
				return array("tracker" => $urlHost,
					"state" => "failed:no_port_detected",
					"method" => $urlScheme . ":" . $method);
				break;
		}
	}

	//URL parts
	$urlHost = $urlInfo["host"];
	$urlPort = $urlInfo["port"];
	$urlScheme = $urlInfo["scheme"];
	$urlPad = (isset($urlInfo["path"]) ? $urlInfo["path"] : "");

	$new_get_request_params = array();

	//Only if it exists
	if (isset($url["query"]))
	{
		$get_request_params = explode("&", $url["query"]);

		foreach (array_filter($get_request_params) as $array_value)
		{
			list($key, $value) = explode("=", $array_value);
			$new_get_request_params[$key] = $value;
		}
	}

	$http_params = @http_build_query(@array_merge($new_get_request_params, $get_params));

	//This is the complete URL
	$requestURL = $urlScheme . "://" . $urlHost . ":" . $urlPort . $urlPad . ($http_params ? "?" . $http_params : "");

	//UDP
	if ($urlScheme == "udp")
	{
		$transactionId = mt_rand(0, 65535);
		$fp = @fsockopen($urlScheme . "://" . $urlHost, $urlPort, $errno, $errstr); //sockets only

		//Geen verbinding
		if (!$fp)
		{
			return array("tracker" => $urlHost,
				"state" => "failed:timeout",
				"method" => $urlScheme . ":" . $method);
		}

		//Timeout, in seconds
		stream_set_timeout($fp, 3);

		$connectionId = "\x00\x00\x04\x17\x27\x10\x19\x80";

		//Connection request
		$packet = $connectionId . pack("N", 0) . pack("N", $transactionId);
		fwrite($fp, $packet);

		//Received
		$received = fread($fp, 16);

		//Too short
		if (strlen($received) < 1)
		{
			return array("tracker" => $urlHost,
				"state" => "failed:no_udp_data",
				"method" => $urlScheme . ":" . $method);
		}

		//Still too short
		if (strlen($received) < 16)
		{
			return array("tracker" => $urlHost,
				"state" => "failed:invalid_udp_packet",
				"method" => $urlScheme . ":" . $method);
		}

		//Unpack received data
		$receivedData = unpack("Naction/Ntransid", $received);

		//Invalid data
		if ($receivedData["action"] != 0 || $receivedData["transid"] != $transactionId)
		{
			return array("tracker" => $urlHost,
				"state" => "failed:invalid_udp_response",
				"method" => $urlScheme . ":" . $method);
		}
		$connectionId = substr($received, 8, 8);

		//Scrape request
		if ($method == "scrape")
		{
			$packet = $connectionId . pack("N", 2) . pack("N", $transactionId) . pack("H*", $info_hash);
		} //Announce request
		elseif ($method == "announce")
		{
			//8-bits, 8x a '0' (zero)
			$downloaded = "\x30\x30\x30\x30\x30\x30\x30\x30";
			$left = $downloaded;
			$uploaded = $downloaded;

			//IP-adres
			$ipAdres = getHostByName(getHostName()); //CLI
			//$ipAdres = ; //Browser

			$packet = $connectionId . pack("N", 1) . pack("N", $transactionId) . pack("H*", $info_hash) . pack("H*", "ee3eb1acec1dc7adc73eda16d05a495bea1ddab1") . $downloaded . $left . $uploaded . pack("N", 2) . pack("N", ip2long($ipAdres)) . pack("N", 69) . pack("N", 500) . pack("N", rand(0, 65535));
		}

		fwrite($fp, $packet);

		//UDP data, Scrape or Announce
		$readLength = 20; //8 + (12);
		$received = fread($fp, $readLength);

		//Just for announce
		if (strlen($received) < 1 && $method == "announce")
		{
			return array("tracker" => $urlHost,
				"state" => "failed:no_udp_data",
				"method" => $urlScheme . ":" . $method);
		}

		//Just for scrape
		if (strlen($received) < 1 && $method == "scrape")
		{
			//Scrape failed, perhaps announce works?
			return scrape($orgineleURL, $info_hash, "announce");
		}

		//Invalid scrape packet
		if (strlen($received) < 8 && $method == "scrape")
		{
			array("tracker" => $urlHost,
				"state" => "failed:invalid_udp_packet",
				"method" => $urlScheme . ":" . $method);
		}

		//Invalid UDP packet
		if (strlen($received) < $readLength)
		{
			array("tracker" => $urlHost,
				"state" => "failed:invalid_udp_packet",
				"method" => $urlScheme . ":" . $method);
		}

		//Unpack received data
		$receivedData = unpack("Naction/Ntransid", $received);

		//Invalid answer
		if ($receivedData["action"] != 2 || $receivedData["transid"] != $transactionId)
		{
			array("tracker" => $urlHost,
				"state" => "failed:invalid_udp_response",
				"method" => $urlScheme . ":" . $method);
		}

		$index = 8;

		//Announce
		if ($method == "announce")
		{
			$receivedData = unpack("Nleechers/Nseeders", substr($received, $index, 8));
		} //Scrape
		elseif ($method == "scrape")
		{
			$receivedData = unpack("Nseeders/Ncompleted/Nleechers", substr($received, $index, 12));
		}

		//We found what we wanted
		return array("tracker" => $urlHost,
			"seeders" => $receivedData["seeders"],
			"leechers" => $receivedData["leechers"],
			"state" => "ok",
			"method" => $urlScheme . ":" . $method);
	} //HTTP
	elseif ($urlScheme == "http" || $urlScheme == "https")
	{
		//Use cURL
		$ch = @curl_init();

		@curl_setopt($ch, CURLOPT_URL, $requestURL);
		@curl_setopt($ch, CURLOPT_PORT, $urlPort);
		@curl_setopt($ch, CURLOPT_HEADER, false);
		@curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		@curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		@curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
		@curl_setopt($ch, CURLOPT_TIMEOUT, 15);
		@curl_setopt($ch, CURLOPT_BUFFERSIZE, 1000);
		@curl_setopt($ch, CURLOPT_USERAGENT, "uTorrent/1820");

		//Data
		$content = @curl_exec($ch);
		@curl_close($ch);

		//Scrape
		if ((!$content || strlen($content) < 1) && $method == "scrape")
		{
			//Scrape failed, perhaps announce works?
			return scrape($orgineleURL, $info_hash, "announce");
		}

		//Announce
		if ((!$content || strlen($content) < 1) && $method == "announce")
		{
			return array("tracker" => $urlHost,
				"state" => "failed:invalid_http_packet",
				"method" => $urlScheme . ":" . $method);
		}

		//Start lightBenc
		$lightBenc = new lightbenc;

		//Decode content
		$bDecoded = $lightBenc->bdecode($content);

		//Decoding failed
		if (!is_array($bDecoded))
		{
			//Remove line breaks
			$content = str_replace(array("\r\n",
				"\r",
				"\n"), "", $content);

			return array("tracker" => $urlHost,
				"state" => "failed:unable_to_bdec:" . $content,
				"method" => $urlScheme . ":" . $method);
		}

		//Some extra encrypted hash we need
		$eHash = pack("H*", $info_hash);

		//Scrape data
		if ($method == "scrape")
		{
			return array("tracker" => $urlHost,
				"seeders" => (int)$bDecoded["files"][$eHash]["complete"],
				"leechers" => (int)$bDecoded["files"][$eHash]["incomplete"],
				"state" => "ok",
				"method" => $urlScheme . ":" . $method);
		} //Announce data
		elseif ($method == "announce")
		{
			return array("tracker" => $urlHost,
				"seeders" => (int)$bDecoded["complete"],
				"leechers" => (int)$bDecoded["incomplete"],
				"state" => "ok",
				"method" => $urlScheme . ":" . $method);
		}
	} //Invalid URL
	else
	{
		return array("tracker" => $urlHost,
			"state" => "failed:invalid_url",
			"method" => $urlScheme . ":" . $method);
	}
}

?>