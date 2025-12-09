<?php
session_start();
header('Content-Type: application/json');

require_once 'ApiClient.php';

$api = new ApiClient(300);
$apiUrl = 'https://api.hh.ru/areas';
$apiData = $api->request($apiUrl, true);

$_SESSION['api_data'] = $apiData;

if ($apiData['success']) {
    echo json_encode([
        'success' => true,
        'regions' => $apiData['data'],
        'timestamp' => $apiData['timestamp']
    ], JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode([
        'success' => false,
        'error' => $apiData['error']
    ], JSON_UNESCAPED_UNICODE);
}
?>