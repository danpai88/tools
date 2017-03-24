<?php
namespace danpai\net;

class Http
{
	public static function multiGet($urls = [])
	{
		$mh = curl_multi_init();
		$handles = [];
		foreach ($urls as $key => $url) {
			$handles[$key] = curl_init($url);
			curl_setopt($handles[$key], CURLOPT_RETURNTRANSFER, true);
			curl_multi_add_handle($mh, $handles[$key]);
		}

		$running = null;
	  	do {
	     	curl_multi_exec($mh, $running);
	   	} while ($running);

	   	$responses = [];
	   	foreach ($handles as $key => $handle) {
	   		$responses[] = curl_multi_remove_handle($mh, $handle);
	   	}

	   	return $responses;
	}

	public static function get()
	{

	}
}