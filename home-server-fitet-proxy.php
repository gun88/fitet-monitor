<?php
declare(strict_types=1);

// Upload this file to the home server's web directory, not to Register.
// The public IP used by the Register server in the connection test.
$allowed_client_ip = '213.158.92.214';

if (($_SERVER['REMOTE_ADDR'] ?? '') !== $allowed_client_ip) {
    http_response_code(403);
    exit('Forbidden');
}

foreach (['INCONTRO', 'CAM', 'FORMULA'] as $parameter) {
    if (!isset($_GET[$parameter]) || !is_string($_GET[$parameter]) || !ctype_digit($_GET[$parameter])) {
        http_response_code(400);
        exit('Invalid parameter');
    }
}

if (!function_exists('curl_init')) {
    http_response_code(500);
    exit('cURL unavailable');
}

$url = 'https://portale.fitet.org/risultati/campionati/giornata.php?' . http_build_query([
    'INCONTRO' => $_GET['INCONTRO'],
    'CAM' => $_GET['CAM'],
    'FORMULA' => $_GET['FORMULA'],
]);

$curl = curl_init($url);
curl_setopt_array($curl, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CONNECTTIMEOUT => 5,
    CURLOPT_TIMEOUT => 20,
    CURLOPT_USERAGENT => 'Fitet Monitor match proxy',
]);
$body = curl_exec($curl);
$status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
$error = curl_error($curl);
curl_close($curl);

header('Content-Type: text/html; charset=Windows-1252');
header('Cache-Control: no-store');

if ($body === false || $status !== 200) {
    http_response_code(502);
    exit('FITeT request failed: ' . ($error !== '' ? $error : "HTTP $status"));
}

echo $body;
