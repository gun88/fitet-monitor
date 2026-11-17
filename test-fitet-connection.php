<?php
declare(strict_types=1);

header('Content-Type: text/plain; charset=UTF-8');
header('Cache-Control: no-store');

if (!function_exists('curl_init')) {
    echo "KO - estensione PHP cURL non disponibile\n";
    exit;
}

$targets = [
    'IP pubblico in uscita' => 'https://api.ipify.org/',
    'Sito di controllo' => 'https://www.google.com/generate_204',
    'Portale FITeT' => 'https://portale.fitet.org/',
    'Archivio FITeT' => 'http://archivio.fitet.org/',
    'Referto FITeT' => 'https://portale.fitet.org/risultati/campionati/giornata.php?INCONTRO=388127&CAM=417&FORMULA=19',
];

foreach ($targets as $label => $url) {
    $host = parse_url($url, PHP_URL_HOST);
    $dns_ip = gethostbyname($host);
    echo "$label\n$url\nDNS: " . ($dns_ip === $host ? 'KO' : $dns_ip) . "\n";

    $curl = curl_init($url);
    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT => 12,
        CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
        CURLOPT_USERAGENT => 'Fitet Monitor connection test',
    ]);

    $body = curl_exec($curl);
    $errno = curl_errno($curl);
    $error = curl_error($curl);
    $status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
    $remote_ip = curl_getinfo($curl, CURLINFO_PRIMARY_IP);
    $seconds = (float) curl_getinfo($curl, CURLINFO_TOTAL_TIME);
    curl_close($curl);

    if ($errno !== 0) {
        echo "KO - cURL $errno: $error\n";
    } else {
        echo "Raggiunto - HTTP $status";
        if ($label === 'IP pubblico in uscita' && $status === 200) {
            echo ' - IP: ' . trim($body);
        }
        echo "\n";
    }
    echo 'IP remoto: ' . ($remote_ip ?: '-') . "\n";
    echo 'Tempo: ' . number_format($seconds, 2, '.', '') . " s\n\n";
}
