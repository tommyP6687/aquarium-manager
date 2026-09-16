<?php
require_once __DIR__ . '/../../includes/auth.php';

requireApiLogin();

$pdo = get_db();
$userId = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];
$id = isset($_GET['id']) ? (int) $_GET['id'] : null;

$updatableColumns = ['custom_name', 'health_status', 'growth_stage', 'current_size_inches', 'notes'];

const ORGANISM_SELECT = '
    SELECT Organism.*, Tanks.custom_name AS tank_name,
           Species.common_name AS species_common_name, Species.scientific_name AS species_scientific_name,
           Species.adult_size_inches AS species_adult_size_inches, Species.organism_type AS species_organism_type
    FROM Organism
    JOIN Tanks ON Organism.tank_id = Tanks.id
    JOIN Species ON Organism.species_id = Species.id
';

function withGrowthEstimate(array $organism): array
{
    $current = $organism['current_size_inches'];
    $adult = $organism['species_adult_size_inches'];

    if ($current === null || $adult === null || (float) $adult <= 0) {
        $organism['estimated_growth_stage'] = null;
        return $organism;
    }

    $ratio = (float) $current / (float) $adult;

    if ($ratio < 0.4) {
        $organism['estimated_growth_stage'] = 'Juvenile';
    } elseif ($ratio < 0.8) {
        $organism['estimated_growth_stage'] = 'Sub-adult';
    } else {
        $organism['estimated_growth_stage'] = 'Adult';
    }

    return $organism;
}

function findOwnedOrganism(PDO $pdo, int $id, int $userId): ?array
{
    $stmt = $pdo->prepare(ORGANISM_SELECT . ' WHERE Organism.id = ? AND Organism.user_id = ? LIMIT 1');
    $stmt->execute([$id, $userId]);
    $organism = $stmt->fetch();
    return $organism ? withGrowthEstimate($organism) : null;
}

function isOwnedRow(PDO $pdo, string $table, int $id, int $userId): bool
{
    $stmt = $pdo->prepare("SELECT id FROM $table WHERE id = ? AND user_id = ? LIMIT 1");
    $stmt->execute([$id, $userId]);
    return (bool) $stmt->fetch();
}

function findOrCreateSpecies(PDO $pdo, array $input): int
{
    if (!empty($input['species_id'])) {
        return (int) $input['species_id'];
    }

    $scientificName = trim($input['scientific_name'] ?? '');

    $stmt = $pdo->prepare('SELECT id FROM Species WHERE scientific_name = ? LIMIT 1');
    $stmt->execute([$scientificName]);
    $existing = $stmt->fetch();

    if ($existing) {
        return (int) $existing['id'];
    }

    $stmt = $pdo->prepare(
        'INSERT INTO Species (scientific_name, common_name, organism_type, salinity_type, external_taxon_id) VALUES (?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $scientificName,
        $input['common_name'] ?? null,
        $input['organism_type'] ?? null,
        $input['salinity_type'] ?? null,
        $input['external_taxon_id'] ?? null,
    ]);

    return (int) $pdo->lastInsertId();
}

try {
    switch ($method) {
        case 'GET':
            if ($id === null) {
                $sql = ORGANISM_SELECT . ' WHERE Organism.user_id = ?';
                $params = [$userId];

                if (isset($_GET['tank_id'])) {
                    $sql .= ' AND Organism.tank_id = ?';
                    $params[] = (int) $_GET['tank_id'];
                }

                $sql .= ' ORDER BY Organism.created_at DESC';

                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                send_json(array_map('withGrowthEstimate', $stmt->fetchAll()));
            }

            $organism = findOwnedOrganism($pdo, $id, $userId);
            if (!$organism) {
                send_json(['error' => 'Organism not found'], 404);
            }
            send_json($organism);
            break;

        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true) ?? [];

            if (trim($input['custom_name'] ?? '') === '') {
                send_json(['error' => 'custom_name is required'], 400);
            }

            $tankId = (int) ($input['tank_id'] ?? 0);
            if (!$tankId || !isOwnedRow($pdo, 'Tanks', $tankId, $userId)) {
                send_json(['error' => 'tank_id must reference one of your own tanks'], 400);
            }

            $pixelArtId = (int) ($input['pixel_art_id'] ?? 0);
            if (!$pixelArtId || !isOwnedRow($pdo, 'PixelArt', $pixelArtId, $userId)) {
                send_json(['error' => 'pixel_art_id must reference one of your own sprites'], 400);
            }

            if (empty($input['species_id']) && trim($input['scientific_name'] ?? '') === '') {
                send_json(['error' => 'species_id or scientific_name is required'], 400);
            }

            $speciesId = findOrCreateSpecies($pdo, $input);

            $stmt = $pdo->prepare(
                'INSERT INTO Organism (user_id, tank_id, species_id, pixel_art_id, custom_name, date_added, health_status, growth_stage, current_size_inches, notes)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $userId,
                $tankId,
                $speciesId,
                $pixelArtId,
                trim($input['custom_name']),
                $input['date_added'] ?? date('Y-m-d'),
                $input['health_status'] ?? 'Healthy',
                $input['growth_stage'] ?? null,
                $input['current_size_inches'] ?? null,
                $input['notes'] ?? null,
            ]);

            send_json(findOwnedOrganism($pdo, (int) $pdo->lastInsertId(), $userId), 201);
            break;

        case 'PUT':
        case 'PATCH':
            if ($id === null) {
                send_json(['error' => 'id is required'], 400);
            }

            if (!findOwnedOrganism($pdo, $id, $userId)) {
                send_json(['error' => 'Organism not found'], 404);
            }

            $input = json_decode(file_get_contents('php://input'), true) ?? [];

            $setClauses = [];
            $values = [];

            foreach ($updatableColumns as $column) {
                if (array_key_exists($column, $input)) {
                    $setClauses[] = "$column = ?";
                    $values[] = $input[$column];
                }
            }

            if (empty($setClauses)) {
                send_json(['error' => 'No updatable fields provided'], 400);
            }

            $values[] = $id;
            $values[] = $userId;

            $sql = 'UPDATE Organism SET ' . implode(', ', $setClauses) . ' WHERE id = ? AND user_id = ?';
            $stmt = $pdo->prepare($sql);
            $stmt->execute($values);

            send_json(findOwnedOrganism($pdo, $id, $userId));
            break;

        case 'DELETE':
            if ($id === null) {
                send_json(['error' => 'id is required'], 400);
            }

            if (!findOwnedOrganism($pdo, $id, $userId)) {
                send_json(['error' => 'Organism not found'], 404);
            }

            $stmt = $pdo->prepare('DELETE FROM Organism WHERE id = ? AND user_id = ?');
            $stmt->execute([$id, $userId]);

            send_json(['success' => true]);
            break;

        default:
            send_json(['error' => 'Method not allowed'], 405);
    }
} catch (PDOException $e) {
    error_log('Organisms API anomaly: ' . $e->getMessage());
    send_json(['error' => 'An infrastructure resource issue occurred. Please retry.'], 500);
}
