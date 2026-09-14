<?php
require_once __DIR__ . '/../../includes/auth.php';

requireApiLogin();

$pdo = get_db();
$userId = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];
$id = isset($_GET['id']) ? (int) $_GET['id'] : null;

$updatableColumns = ['performed_at', 'task_type', 'details', 'notes'];

const MAINTENANCE_LOG_SELECT = '
    SELECT MaintenanceLog.*, Tanks.custom_name AS tank_name
    FROM MaintenanceLog
    JOIN Tanks ON MaintenanceLog.tank_id = Tanks.id
';

function findOwnedMaintenanceLog(PDO $pdo, int $id, int $userId): ?array
{
    $stmt = $pdo->prepare(MAINTENANCE_LOG_SELECT . ' WHERE MaintenanceLog.id = ? AND MaintenanceLog.user_id = ? LIMIT 1');
    $stmt->execute([$id, $userId]);
    $log = $stmt->fetch();
    return $log ?: null;
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
                $sql = MAINTENANCE_LOG_SELECT . ' WHERE MaintenanceLog.user_id = ?';
                $params = [$userId];

                if (isset($_GET['tank_id'])) {
                    $sql .= ' AND MaintenanceLog.tank_id = ?';
                    $params[] = (int) $_GET['tank_id'];
                }

                $sql .= ' ORDER BY MaintenanceLog.performed_at DESC';

                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                send_json($stmt->fetchAll());
            }

            $log = findOwnedMaintenanceLog($pdo, $id, $userId);
            if (!$log) {
                send_json(['error' => 'Maintenance log not found'], 404);
            }
            send_json($log);
            break;

        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true) ?? [];

            $tankId = (int) ($input['tank_id'] ?? 0);
            if (!$tankId || !isOwnedTank($pdo, $tankId, $userId)) {
                send_json(['error' => 'tank_id must reference one of your own tanks'], 400);
            }

            if (trim($input['task_type'] ?? '') === '') {
                send_json(['error' => 'task_type is required'], 400);
            }

            $stmt = $pdo->prepare(
                'INSERT INTO MaintenanceLog (user_id, tank_id, performed_at, task_type, details, notes)
                 VALUES (?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $userId,
                $tankId,
                $input['performed_at'] ?? date('Y-m-d H:i:s'),
                trim($input['task_type']),
                $input['details'] ?? null,
                $input['notes'] ?? null,
            ]);

            send_json(findOwnedMaintenanceLog($pdo, (int) $pdo->lastInsertId(), $userId), 201);
            break;

        case 'PUT':
        case 'PATCH':
            if ($id === null) {
                send_json(['error' => 'id is required'], 400);
            }

            if (!findOwnedMaintenanceLog($pdo, $id, $userId)) {
                send_json(['error' => 'Maintenance log not found'], 404);
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

            $sql = 'UPDATE MaintenanceLog SET ' . implode(', ', $setClauses) . ' WHERE id = ? AND user_id = ?';
            $stmt = $pdo->prepare($sql);
            $stmt->execute($values);

            send_json(findOwnedMaintenanceLog($pdo, $id, $userId));
            break;

        case 'DELETE':
            if ($id === null) {
                send_json(['error' => 'id is required'], 400);
            }

            if (!findOwnedMaintenanceLog($pdo, $id, $userId)) {
                send_json(['error' => 'Maintenance log not found'], 404);
            }

            $stmt = $pdo->prepare('DELETE FROM MaintenanceLog WHERE id = ? AND user_id = ?');
            $stmt->execute([$id, $userId]);

            send_json(['success' => true]);
            break;

        default:
            send_json(['error' => 'Method not allowed'], 405);
    }
} catch (PDOException $e) {
    error_log('MaintenanceLog API anomaly: ' . $e->getMessage());
    send_json(['error' => 'An infrastructure resource issue occurred. Please retry.'], 500);
}
