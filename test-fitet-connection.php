<?php
declare(strict_types=1);

header('Content-Type: text/plain; charset=UTF-8');
header('Cache-Control: no-store');

$url = 'https://portale.fitet.org/';

if (!function_exists('curl_init')) {
    http_response_code(500);
    echo "KO - estensione PHP cURL non disponibile\n";
    exit;
}

$curl = curl_init($url);
curl_setopt_array($curl, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_CONNECTTIMEOUT => 5,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_USERAGENT => 'Fitet Monitor connection test',
]);

$result = curl_exec($curl);
$status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
$error = curl_error($curl);
curl_close($curl);

if ($result !== false && $status >= 200 && $status < 400) {
    echo "OK - HTTP $status - $url\n";
} else {
    http_response_code(502);
    echo "KO - " . ($error !== '' ? $error : "HTTP $status") . " - $url\n";
}
