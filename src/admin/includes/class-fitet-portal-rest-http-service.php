<?php

define('FITET_MONITOR_WP_CALLS_AVAILABLE', function_exists('wp_remote_get') && function_exists('wp_remote_post') && function_exists('is_wp_error'));

class Fitet_Portal_Rest_Http_Service {


	private $retry = 12; // max retry
	private $sleep = 5; /// retry after x sec.
	private $wp_remote_params = ['timeout' => 10];

	public function get($url, $headers = []) {
		if (FITET_MONITOR_WP_CALLS_AVAILABLE)
			return $this->wp_get($url, $headers);
		return $this->native_get($url, $headers);
	}

	public function post($url, $body) {
		if (FITET_MONITOR_WP_CALLS_AVAILABLE)
			return $this->wp_post($url, $body);
		return $this->native_post($url, $body);
	}

	public function native_get($url, $headers) {
		// to native headers
		$headers = array_map(function ($key, $value) {
			return "$key: $value";
		}, array_keys($headers), array_values($headers));

		for ($i = 0; $i < $this->retry; $i++) {
			$response = empty($headers) ? file_get_contents($url) : file_get_contents($url, true, stream_context_create(["http" => ["header" => $headers]]));
			if (!$response) {
				sleep($this->sleep);
				error_log("wp error - waiting $this->sleep sec timeout");
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
				error_log("wp error - waiting $this->sleep sec timeout");
				continue;
			}
			return $response;
		}
		throw new Exception("Error: file_get_contents at $url");
	}


	public function wp_get($url, $headers) {
		$response = null;
		for ($i = 0; $i < $this->retry; $i++) {
			$parameters = array_merge($this->wp_remote_params, ['headers' => $headers]);
			$response = wp_remote_get($url, $parameters);
			if (is_wp_error($response)) {
				sleep($this->sleep);
				error_log("wp error - waiting $this->sleep sec timeout");
				continue;
			}
			return $response['body'];
		}
		throw new Exception($this->wp_createMessage($response));
	}

	public function wp_post($url, $body) {
		$response = null;
		for ($i = 0; $i < $this->retry; $i++) {
			$response = wp_remote_post(
				$url, array_merge($this->wp_remote_params, [
				'method' => 'POST',
				'body' => $body
			]));
			if (is_wp_error($response)) {
				sleep($this->sleep);
				error_log("wp error - waiting $this->sleep sec timeout");
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

