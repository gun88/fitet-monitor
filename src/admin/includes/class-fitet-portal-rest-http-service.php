<?php

define('FITET_MONITOR_WP_CALLS_AVAILABLE', function_exists('wp_remote_get') && function_exists('wp_remote_post') && function_exists('is_wp_error'));

class Fitet_Portal_Rest_Http_Service {


	private $retry = 3; // max retry
	private $sleep = 3; /// retry after 3 sec.

	public function get($url) {
		if (FITET_MONITOR_WP_CALLS_AVAILABLE)
			return $this->wp_get($url);
		return $this->native_get($url);
	}

	public function post($url, $body) {
		if (FITET_MONITOR_WP_CALLS_AVAILABLE)
			return $this->wp_post($url, $body);
		return $this->native_post($url, $body);
	}

	public function native_get($url) {
		for ($i = 0; $i < $this->retry; $i++) {
			$response = file_get_contents($url);
			if (!$response) {
				sleep($this->sleep);
				continue;
			}
			return $response;
		}
		throw new Exception("Error: file_get_contents at $url");
	}

	public function native_post($url, $body) {
		$header[] = "Content-type: application/x-www-form-urlencoded";
		$header = implode("\r\n", $header);

		$context = stream_context_create(["http" => [
			"method" => "POST",
			"header" => $header,
			'content' => $body
		]]);

		for ($i = 0; $i < $this->retry; $i++) {
			$response = file_get_contents($url, false, $context);
			if (!$response) {
				sleep($this->sleep);
				continue;
			}
			return $response;
		}
		throw new Exception("Error: file_get_contents at $url");
	}


	public function wp_get(string $url) {
		$response = null;
		for ($i = 0; $i < $this->retry; $i++) {
			$response = wp_remote_get($url);
			if (is_wp_error($response)) {
				sleep($this->sleep);
				continue;
			}
			return $response['body'];
		}
		throw new Exception($this->createMessage($response));
	}

	public function wp_post($url, $body) {
		$response = null;
		for ($i = 0; $i < $this->retry; $i++) {
			$response = wp_remote_post(
				$url, array(
				'method' => 'POST',
				'body' => $body
			));
			if (is_wp_error($response)) {
				sleep($this->sleep);
				continue;
			}
			return $response['body'];
		}
		throw new Exception($this->wp_createMessage($response));
	}

	public function wp_createMessage($response) {
		$message = "";
		foreach ($response->errors as $key => $value) {
			$message .= "$key: " . implode("\n", $value) . "\n";
		}
		return $message;
	}


}

