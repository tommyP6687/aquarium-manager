<?php
require_once __DIR__ . '/../../includes/auth.php';

requireApiLogin();

$query = trim($_GET['q'] ?? '');

if ($query === '') {
    send_json([]);
}

$apiKey = getenv('SERPAPI_KEY');

if (!$apiKey) {
    send_json(['error' => 'Price search is not configured (SERPAPI_KEY is not set)'], 500);
}

$url = 'https://serpapi.com/search.json?' . http_build_query([
    'engine' => 'google_shopping',
    'q' => $query,
    'api_key' => $apiKey,
]);

// ignore_errors so a non-2xx response body (SerpApi's own error message) is
// still readable instead of file_get_contents silently returning false.
$context = stream_context_create(['http' => ['timeout' => 15, 'ignore_errors' => true]]);
$response = @file_get_contents($url, false, $context);

if ($response === false) {
    error_log('Price search: no response from SerpApi (network/DNS failure)');
    send_json(['error' => 'Price search is temporarily unavailable.'], 502);
}

$data = json_decode($response, true);

if (isset($data['error'])) {
    error_log('Price search: SerpApi error - ' . $data['error']);
    send_json(['error' => $data['error']], 502);
}

$results = [];

// Field names confirmed against SerpApi's published Google Shopping API docs.
foreach ($data['shopping_results'] ?? [] as $item) {
    $results[] = [
        'title' => $item['title'] ?? null,
        'price' => $item['extracted_price'] ?? null,
        'store' => $item['source'] ?? null,
        'link' => $item['product_link'] ?? null,
    ];
}

send_json($results);
