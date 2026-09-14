<?php
require_once __DIR__ . '/../../includes/auth.php';

requireApiLogin();

$pdo = get_db();
$userId = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];
$id = isset($_GET['id']) ? (int) $_GET['id'] : null;

$writableColumns = [
    'custom_name', 'tank_type', 'salinity_type', 'tank_shape', 'volume_gallons',
    'has_filter', 'filter_type', 'has_co2', 'co2_type', 'has_fertilizer', 'fertilizer_routine',
    'has_lighting', 'lighting_type', 'lighting_schedule', 'lighting_intensity',
    'has_substrate', 'substrate_type', 'has_heater', 'heater_type',
    'cycle_start_date', 'is_cycled', 'notes',
];

function findOwnedTank(PDO $pdo, int $id, int $userId): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM Tanks WHERE id = ? AND user_id = ? LIMIT 1');
    $stmt->execute([$id, $userId]);
    $tank = $stmt->fetch();
    return $tank ?: null;
}

function normalizeValue($value)
{
    // PDO's execute() casts scalars to strings for binding, and (string) false
    // is '' rather than '0', which MySQL rejects for BOOLEAN/TINYINT columns.
    return is_bool($value) ? (int) $value : $value;
}

try {
    switch ($method) {
        case 'GET':
            if ($id === null) {
                $stmt = $pdo->prepare('SELECT * FROM Tanks WHERE user_id = ? ORDER BY created_at DESC');
                $stmt->execute([$userId]);
                send_json($stmt->fetchAll());
            }

            $tank = findOwnedTank($pdo, $id, $userId);
            if (!$tank) {
                send_json(['error' => 'Tank not found'], 404);
            }
            send_json($tank);
            break;

        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true) ?? [];

            if (trim($input['custom_name'] ?? '') === '') {
                send_json(['error' => 'custom_name is required'], 400);
            }

            $columns = ['user_id'];
            $placeholders = ['?'];
            $values = [$userId];

            foreach ($writableColumns as $column) {
                if (array_key_exists($column, $input)) {
                    $columns[] = $column;
                    $placeholders[] = '?';
                    $values[] = normalizeValue($input[$column]);
                }
            }

            $sql = sprintf(
                'INSERT INTO Tanks (%s) VALUES (%s)',
                implode(', ', $columns),
                implode(', ', $placeholders)
            );

            $stmt = $pdo->prepare($sql);
            $stmt->execute($values);

            send_json(findOwnedTank($pdo, (int) $pdo->lastInsertId(), $userId), 201);
            break;

        case 'PUT':
        case 'PATCH':
            if ($id === null) {
                send_json(['error' => 'id is required'], 400);
            }

            if (!findOwnedTank($pdo, $id, $userId)) {
                send_json(['error' => 'Tank not found'], 404);
            }

            $input = json_decode(file_get_contents('php://input'), true) ?? [];

            $setClauses = [];
            $values = [];

            foreach ($writableColumns as $column) {
                if (array_key_exists($column, $input)) {
                    $setClauses[] = "$column = ?";
                    $values[] = normalizeValue($input[$column]);
                }
            }

            if (empty($setClauses)) {
                send_json(['error' => 'No updatable fields provided'], 400);
            }

            $values[] = $id;
            $values[] = $userId;

            $sql = 'UPDATE Tanks SET ' . implode(', ', $setClauses) . ' WHERE id = ? AND user_id = ?';
            $stmt = $pdo->prepare($sql);
            $stmt->execute($values);

            send_json(findOwnedTank($pdo, $id, $userId));
            break;

        case 'DELETE':
            if ($id === null) {
                send_json(['error' => 'id is required'], 400);
            }

            if (!findOwnedTank($pdo, $id, $userId)) {
                send_json(['error' => 'Tank not found'], 404);
            }

            $stmt = $pdo->prepare('DELETE FROM Tanks WHERE id = ? AND user_id = ?');
            $stmt->execute([$id, $userId]);

            send_json(['success' => true]);
            break;

        default:
            send_json(['error' => 'Method not allowed'], 405);
    }
} catch (PDOException $e) {
    error_log('Tanks API anomaly: ' . $e->getMessage());
    send_json(['error' => 'An infrastructure resource issue occurred. Please retry.'], 500);
}
