<?php

define('FITET_MONITOR_WP_CALLS_AVAILABLE', function_exists('wp_remote_get') && function_exists('wp_remote_post') && function_exists('is_wp_error'));

class Fitet_Portal_Rest_Http_Service {


    private $retry = 12; // max retry
    private $sleep_interval_list = [0, 0, 50, 100, 250, 500, 1000, 2000]; /// retry after x sec.
    private $wp_remote_params = ['timeout' => 10];

    public function get($url, $headers = [], $max_retries = null, $timeout = null) {
        $url = $this->proxy_url($url);
        $body = FITET_MONITOR_WP_CALLS_AVAILABLE ? $this->wp_get($url, $headers, $max_retries, $timeout) : $this->native_get($url, $headers, $max_retries, $timeout);
        return $this->fix_charset_in_body($body);
    }

    public function post($url, $body) {
        $url = $this->proxy_url($url);
        $body = FITET_MONITOR_WP_CALLS_AVAILABLE ? $this->wp_post($url, $body) : $this->native_post($url, $body);
        return $this->fix_charset_in_body($body);
    }

    private function proxy_url($url) {
        $proxy = function_exists('get_option') ? rtrim((string) get_option('fitet_monitor_proxy_url', ''), '/') : '';
        if ($proxy === '') {
            return $url;
        }

        $parts = parse_url($url);
        if (!is_array($parts) || !isset($parts['host'])) {
            return $url;
        }

        $host = strtolower($parts['host']);
        if ($host === 'portale.fitet.org') {
            $prefix = '';
        } elseif ($host === 'archivio.fitet.org') {
            $prefix = '/archivio';
        } else {
            return $url;
        }

        return $proxy . $prefix . ($parts['path'] ?? '/') . (isset($parts['query']) ? '?' . $parts['query'] : '');
    }

    public function native_get($url, $headers, $max_retries = null, $timeout = null) {
        $headers = array_map(function ($key, $value) {
            return "$key: $value";
        }, array_keys($headers), array_values($headers));

        $context = stream_context_create(["http" => [
            "header" => $headers,
            "timeout" => $timeout ?? (float) ini_get('default_socket_timeout'),
        ]]);

        for ($i = 0; $i < ($max_retries ?? $this->retry); $i++) {
            $response = @file_get_contents($url, false, $context);
            if (!$response) {
                $sleep = $this->get_sleep_interval($i);
                error_log("wp error - waiting $sleep ms timeout");
                usleep($sleep * 1000);
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
                $sleep = $this->get_sleep_interval($i);
                error_log("wp error - waiting $sleep ms timeout");
                usleep($sleep * 1000);
                continue;
            }
            return $response;
        }
        throw new Exception("Error: file_get_contents at $url");
    }


    public function wp_get($url, $headers, $max_retries = null, $timeout = null) {
        $response = null;
        for ($i = 0; $i < ($max_retries ?? $this->retry); $i++) {
            $parameters = array_merge($this->wp_remote_params, ['headers' => $headers]);
            if ($timeout !== null) {
                $parameters['timeout'] = $timeout;
            }

            $response = wp_remote_get($url, $parameters);
            $http_code = wp_remote_retrieve_response_code($response);
            if (is_wp_error($response)) {

                $error_message = json_encode($response);// $response->get_error_message();
                $sleep = $this->get_sleep_interval($i);
                error_log("wp_remote_get ERROR - waiting $sleep ms timeout - $i on $this->retry - $url - $error_message");
                usleep($sleep * 1000);
                continue;
            }
            if ($http_code != 200) {
                $sleep = $this->get_sleep_interval($i);
                error_log("wp_remote_get ERROR - waiting $sleep ms timeout - $i on $this->retry - $url - response code: $http_code");
                usleep($sleep * 1000);
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
            $http_code = wp_remote_retrieve_response_code($response);
            if (is_wp_error($response)) {
                $error_message = $response->get_error_message();
                $sleep = $this->get_sleep_interval($i);
                error_log("wp_remote_post ERROR - waiting $sleep ms timeout - $i on $this->retry - $url - $error_message");
                usleep($sleep * 1000);
                continue;
            }
            if ($http_code != 200) {
                $sleep = $this->get_sleep_interval($i);
                error_log("wp_remote_post ERROR - waiting $sleep ms timeout - $i on $this->retry - $url - response code: $http_code");
                usleep($sleep * 1000);
                continue;
            }
            return $response['body'];
        }
        throw new Exception($this->wp_createMessage($response));
    }

    public function wp_createMessage($response) {
        if (is_wp_error($response)) {
            return $response->get_error_message();
        }
        if (is_array($response)) {
            return 'HTTP ' . wp_remote_retrieve_response_code($response);
        }
        return 'No response from Fitet';
    }

    private function get_sleep_interval(int $i) {
        if ($i < count($this->sleep_interval_list)) {
            return $this->sleep_interval_list[$i];
        } else {
            return end($this->sleep_interval_list);
        }
    }

    public function fix_charset_in_body($body) {
        // Se non è una stringa o è vuota, non tocchiamo niente
        if (!is_string($body) || $body === '') {
            return $body;
        }

        $encoding = null;

        // 1. Prova a rilevare l'encoding se mb_detect_encoding è disponibile
        if (function_exists('mb_detect_encoding')) {
            $encoding = mb_detect_encoding(
                $body,
                'UTF-8, ISO-8859-1, WINDOWS-1252',
                true
            );
        }

        // 2. Se abbiamo rilevato un encoding e NON è UTF-8, convertiamo
        if ($encoding && strtoupper($encoding) !== 'UTF-8') {

            if (function_exists('mb_convert_encoding')) {
                // Conversione "di lusso" con mbstring
                $body = mb_convert_encoding($body, 'UTF-8', $encoding);

            } elseif (function_exists('iconv')) {
                // Fallback usando iconv
                $converted = @iconv($encoding, 'UTF-8//IGNORE', $body);
                if ($converted !== false) {
                    $body = $converted;
                }
            }

        } elseif (!$encoding && function_exists('iconv')) {
            // 3. Se NON siamo riusciti a rilevare l'encoding ma iconv c'è,
            //    facciamo un tentativo best-effort assumendo ISO-8859-1
            $converted = @iconv('ISO-8859-1', 'UTF-8//IGNORE', $body);
            if ($converted !== false) {
                $body = $converted;
            }
        }

        // 4. Ripulisci eventuali byte non validi in UTF-8
        $body = wp_check_invalid_utf8($body, true);

        return $body;


    }
}

