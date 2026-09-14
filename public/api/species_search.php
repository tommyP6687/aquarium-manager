<?php
require_once __DIR__ . '/../../includes/auth.php';

requireApiLogin();

$query = trim($_GET['q'] ?? '');

if ($query === '') {
    send_json([]);
}

$iconicTaxonMap = [
    'Actinopterygii' => 'Fish',
    'Chondrichthyes' => 'Fish',
    'Mollusca' => 'Invertebrate',
    'Arachnida' => 'Invertebrate',
    'Insecta' => 'Invertebrate',
    'Plantae' => 'Plant',
];

$url = 'https://api.inaturalist.org/v1/taxa?' . http_build_query([
    'q' => $query,
    'rank' => 'species',
    'per_page' => 10,
]);

$context = stream_context_create(['http' => ['timeout' => 5]]);
$response = @file_get_contents($url, false, $context);

if ($response === false) {
    send_json(['error' => 'Species lookup is temporarily unavailable.'], 502);
}

$data = json_decode($response, true);
$results = [];

foreach ($data['results'] ?? [] as $taxon) {
    $iconicName = $taxon['iconic_taxon_name'] ?? null;

    $results[] = [
        'scientific_name' => $taxon['name'] ?? null,
        'common_name' => $taxon['preferred_common_name'] ?? null,
        'organism_type_guess' => $iconicTaxonMap[$iconicName] ?? 'Other',
        'external_taxon_id' => $taxon['id'] ?? null,
        'photo_url' => $taxon['default_photo']['medium_url'] ?? null,
    ];
}

send_json($results);
