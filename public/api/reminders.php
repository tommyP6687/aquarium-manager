<?php
require_once __DIR__ . '/../../includes/auth.php';

requireApiLogin();

$pdo = get_db();
$userId = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];
$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$action = $_GET['action'] ?? null;

$updatableColumns = ['title', 'task_type', 'due_at', 'repeat_interval_days', 'notes'];

const REMINDER_SELECT = '
    SELECT Reminder.*, Tanks.custom_name AS tank_name
    FROM Reminder
    JOIN Tanks ON Reminder.tank_id = Tanks.id
';

function findOwnedReminder(PDO $pdo, int $id, int $userId): ?array
{
    $stmt = $pdo->prepare(REMINDER_SELECT . ' WHERE Reminder.id = ? AND Reminder.user_id = ? LIMIT 1');
    $stmt->execute([$id, $userId]);
    $reminder = $stmt->fetch();
    return $reminder ?: null;
}

function isOwnedTank(PDO $pdo, int $tankId, int $userId): bool
{
    $stmt = $pdo->prepare('SELECT id FROM Tanks WHERE id = ? AND user_id = ? LIMIT 1');
    $stmt->execute([$tankId, $userId]);
    return (bool) $stmt->fetch();
}

try {
    if ($method === 'POST' && $id !== null && $action === 'complete') {
        $reminder = findOwnedReminder($pdo, $id, $userId);
        if (!$reminder) {
            send_json(['error' => 'Reminder not found'], 404);
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $createMaintenanceLog = !empty($input['create_maintenance_log']);

        $stmt = $pdo->prepare('UPDATE Reminder SET is_completed = 1, completed_at = NOW() WHERE id = ? AND user_id = ?');
        $stmt->execute([$id, $userId]);

        if ($createMaintenanceLog) {
            $stmt = $pdo->prepare(
                'INSERT INTO MaintenanceLog (user_id, tank_id, performed_at, task_type, notes)
                 VALUES (?, ?, NOW(), ?, ?)'
            );
            $stmt->execute([$userId, $reminder['tank_id'], $reminder['task_type'], $reminder['notes']]);
        }

        if ($reminder['repeat_interval_days'] !== null) {
            $nextDueAt = (new DateTime($reminder['due_at']))
                ->modify('+' . (int) $reminder['repeat_interval_days'] . ' days')
                ->format('Y-m-d H:i:s');

            $stmt = $pdo->prepare(
                'INSERT INTO Reminder (user_id, tank_id, title, task_type, due_at, repeat_interval_days, notes)
                 VALUES (?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $userId,
                $reminder['tank_id'],
                $reminder['title'],
                $reminder['task_type'],
                $nextDueAt,
                $reminder['repeat_interval_days'],
                $reminder['notes'],
            ]);
        }

        send_json(findOwnedReminder($pdo, $id, $userId));
    }

    switch ($method) {
        case 'GET':
            if ($id === null) {
                $sql = REMINDER_SELECT . ' WHERE Reminder.user_id = ?';
                $params = [$userId];

                if (isset($_GET['tank_id'])) {
                    $sql .= ' AND Reminder.tank_id = ?';
                    $params[] = (int) $_GET['tank_id'];
                }

                $sql .= ' ORDER BY Reminder.due_at ASC';

                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                send_json($stmt->fetchAll());
            }

            $reminder = findOwnedReminder($pdo, $id, $userId);
            if (!$reminder) {
                send_json(['error' => 'Reminder not found'], 404);
            }
            send_json($reminder);
            break;

        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true) ?? [];

            $tankId = (int) ($input['tank_id'] ?? 0);
            if (!$tankId || !isOwnedTank($pdo, $tankId, $userId)) {
                send_json(['error' => 'tank_id must reference one of your own tanks'], 400);
            }

            if (trim($input['title'] ?? '') === '') {
                send_json(['error' => 'title is required'], 400);
            }

            if (trim($input['task_type'] ?? '') === '') {
                send_json(['error' => 'task_type is required'], 400);
            }

            if (trim($input['due_at'] ?? '') === '') {
                send_json(['error' => 'due_at is required'], 400);
            }

            $stmt = $pdo->prepare(
                'INSERT INTO Reminder (user_id, tank_id, title, task_type, due_at, repeat_interval_days, notes)
                 VALUES (?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $userId,
                $tankId,
                trim($input['title']),
                trim($input['task_type']),
                $input['due_at'],
                $input['repeat_interval_days'] ?? null,
                $input['notes'] ?? null,
            ]);

            send_json(findOwnedReminder($pdo, (int) $pdo->lastInsertId(), $userId), 201);
            break;

        case 'PUT':
        case 'PATCH':
            if ($id === null) {
                send_json(['error' => 'id is required'], 400);
            }

            if (!findOwnedReminder($pdo, $id, $userId)) {
                send_json(['error' => 'Reminder not found'], 404);
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

            $sql = 'UPDATE Reminder SET ' . implode(', ', $setClauses) . ' WHERE id = ? AND user_id = ?';
            $stmt = $pdo->prepare($sql);
            $stmt->execute($values);

            send_json(findOwnedReminder($pdo, $id, $userId));
            break;

        case 'DELETE':
            if ($id === null) {
                send_json(['error' => 'id is required'], 400);
            }

            if (!findOwnedReminder($pdo, $id, $userId)) {
                send_json(['error' => 'Reminder not found'], 404);
            }

            $stmt = $pdo->prepare('DELETE FROM Reminder WHERE id = ? AND user_id = ?');
            $stmt->execute([$id, $userId]);

            send_json(['success' => true]);
            break;

        default:
            send_json(['error' => 'Method not allowed'], 405);
    }
} catch (PDOException $e) {
    error_log('Reminder API anomaly: ' . $e->getMessage());
    send_json(['error' => 'An infrastructure resource issue occurred. Please retry.'], 500);
}
