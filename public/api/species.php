<?php
require_once __DIR__ . '/../../includes/auth.php';

requireApiLogin();

$pdo = get_db();
$userId = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];
$id = isset($_GET['id']) ? (int) $_GET['id'] : null;

$updatableColumns = [
    'min_temp_f', 'max_temp_f', 'min_ph', 'max_ph', 'min_tank_size_gallons',
    'adult_size_inches', 'temperament', 'min_group_size', 'care_level',
    'is_plant_safe', 'is_shrimp_safe', 'care_notes',
];

function normalizeValue($value)
{
    // PDO's execute() casts scalars to strings for binding, and (string) false
    // is '' rather than '0', which MySQL rejects for BOOLEAN/numeric columns.
    if (is_bool($value)) {
        return (int) $value;
    }
    return $value === '' ? null : $value;
}

try {
    switch ($method) {
        case 'GET':
            if ($id === null) {
                $stmt = $pdo->prepare(
                    'SELECT DISTINCT Species.*
                     FROM Species
                     JOIN Organism ON Organism.species_id = Species.id
                     WHERE Organism.user_id = ?
                     ORDER BY Species.common_name, Species.scientific_name'
                );
                $stmt->execute([$userId]);
                send_json($stmt->fetchAll());
            }

            $stmt = $pdo->prepare('SELECT * FROM Species WHERE id = ? LIMIT 1');
            $stmt->execute([$id]);
            $species = $stmt->fetch();

            if (!$species) {
                send_json(['error' => 'Species not found'], 404);
            }
            send_json($species);
            break;

        case 'PUT':
        case 'PATCH':
            if ($id === null) {
                send_json(['error' => 'id is required'], 400);
            }

            $stmt = $pdo->prepare('SELECT id FROM Species WHERE id = ? LIMIT 1');
            $stmt->execute([$id]);
            if (!$stmt->fetch()) {
                send_json(['error' => 'Species not found'], 404);
            }

            $input = json_decode(file_get_contents('php://input'), true) ?? [];

            $setClauses = [];
            $values = [];

            foreach ($updatableColumns as $column) {
                if (array_key_exists($column, $input)) {
                    $setClauses[] = "$column = ?";
                    $values[] = normalizeValue($input[$column]);
                }
            }

            if (empty($setClauses)) {
                send_json(['error' => 'No updatable fields provided'], 400);
            }

            $values[] = $id;

            $sql = 'UPDATE Species SET ' . implode(', ', $setClauses) . ' WHERE id = ?';
            $stmt = $pdo->prepare($sql);
            $stmt->execute($values);

            $stmt = $pdo->prepare('SELECT * FROM Species WHERE id = ? LIMIT 1');
            $stmt->execute([$id]);
            send_json($stmt->fetch());
            break;

        default:
            send_json(['error' => 'Method not allowed'], 405);
    }
} catch (PDOException $e) {
    error_log('Species API anomaly: ' . $e->getMessage());
    send_json(['error' => 'An infrastructure resource issue occurred. Please retry.'], 500);
}
