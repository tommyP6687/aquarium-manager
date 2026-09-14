<?php
require_once __DIR__ . '/../../includes/auth.php';

requireApiLogin();

$pdo = get_db();
$userId = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];
$id = isset($_GET['id']) ? (int) $_GET['id'] : null;

$updatableColumns = ['tested_at', 'parameter_name', 'value', 'unit', 'notes'];

const WATER_TEST_SELECT = '
    SELECT WaterTest.*, Tanks.custom_name AS tank_name
    FROM WaterTest
    JOIN Tanks ON WaterTest.tank_id = Tanks.id
';

function findOwnedWaterTest(PDO $pdo, int $id, int $userId): ?array
{
    $stmt = $pdo->prepare(WATER_TEST_SELECT . ' WHERE WaterTest.id = ? AND WaterTest.user_id = ? LIMIT 1');
    $stmt->execute([$id, $userId]);
    $reading = $stmt->fetch();
    return $reading ?: null;
}

function isOwnedTank(PDO $pdo, int $tankId, int $userId): bool
{
    $stmt = $pdo->prepare('SELECT id FROM Tanks WHERE id = ? AND user_id = ? LIMIT 1');
    $stmt->execute([$tankId, $userId]);
    return (bool) $stmt->fetch();
}

try {
    switch ($method) {
        case 'GET':
            if ($id === null) {
                $sql = WATER_TEST_SELECT . ' WHERE WaterTest.user_id = ?';
                $params = [$userId];

                if (isset($_GET['tank_id'])) {
                    $sql .= ' AND WaterTest.tank_id = ?';
                    $params[] = (int) $_GET['tank_id'];
                }

                $sql .= ' ORDER BY WaterTest.tested_at DESC';

                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                send_json($stmt->fetchAll());
            }

            $reading = findOwnedWaterTest($pdo, $id, $userId);
            if (!$reading) {
                send_json(['error' => 'Water test reading not found'], 404);
            }
            send_json($reading);
            break;

        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true) ?? [];

            $tankId = (int) ($input['tank_id'] ?? 0);
            if (!$tankId || !isOwnedTank($pdo, $tankId, $userId)) {
                send_json(['error' => 'tank_id must reference one of your own tanks'], 400);
            }

            if (trim($input['parameter_name'] ?? '') === '') {
                send_json(['error' => 'parameter_name is required'], 400);
            }

            if (!isset($input['value']) || !is_numeric($input['value'])) {
                send_json(['error' => 'value is required and must be numeric'], 400);
            }

            $stmt = $pdo->prepare(
                'INSERT INTO WaterTest (user_id, tank_id, tested_at, parameter_name, value, unit, notes)
                 VALUES (?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $userId,
                $tankId,
                $input['tested_at'] ?? date('Y-m-d H:i:s'),
                trim($input['parameter_name']),
                $input['value'],
                $input['unit'] ?? null,
                $input['notes'] ?? null,
            ]);

            send_json(findOwnedWaterTest($pdo, (int) $pdo->lastInsertId(), $userId), 201);
            break;

        case 'PUT':
        case 'PATCH':
            if ($id === null) {
                send_json(['error' => 'id is required'], 400);
            }

            if (!findOwnedWaterTest($pdo, $id, $userId)) {
                send_json(['error' => 'Water test reading not found'], 404);
            }

            $input = json_decode(file_get_contents('php://input'), true) ?? [];

            if (array_key_exists('value', $input) && !is_numeric($input['value'])) {
                send_json(['error' => 'value must be numeric'], 400);
            }

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

            $sql = 'UPDATE WaterTest SET ' . implode(', ', $setClauses) . ' WHERE id = ? AND user_id = ?';
            $stmt = $pdo->prepare($sql);
            $stmt->execute($values);

            send_json(findOwnedWaterTest($pdo, $id, $userId));
            break;

        case 'DELETE':
            if ($id === null) {
                send_json(['error' => 'id is required'], 400);
            }

            if (!findOwnedWaterTest($pdo, $id, $userId)) {
                send_json(['error' => 'Water test reading not found'], 404);
            }

            $stmt = $pdo->prepare('DELETE FROM WaterTest WHERE id = ? AND user_id = ?');
            $stmt->execute([$id, $userId]);

            send_json(['success' => true]);
            break;

        default:
            send_json(['error' => 'Method not allowed'], 405);
    }
} catch (PDOException $e) {
    error_log('WaterTest API anomaly: ' . $e->getMessage());
    send_json(['error' => 'An infrastructure resource issue occurred. Please retry.'], 500);
}
