<?php

class Fitet_Portal_Rest_Http_Service {

	// todo prova ad usare servizio wordpress
	public function get($url) {
		return file_get_contents($url);
	}

	public function post($url, $body) {
		$header[] = "Content-type: application/x-www-form-urlencoded";
		$header = implode("\r\n", $header);

		$context = stream_context_create(["http" => [
			"method" => "POST",
			"header" => $header,
			'content' => $body
		]]);

		return file_get_contents($url, false, $context);
	}



}

