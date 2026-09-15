<?php
require_once __DIR__ . '/../../includes/auth.php';

requireApiLogin();

$pdo = get_db();
$userId = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];
$id = isset($_GET['id']) ? (int) $_GET['id'] : null;

$updatableColumns = [
    'tank_id', 'item_name', 'category', 'estimated_price', 'store_or_source',
    'priority', 'compatibility_notes', 'purchase_status', 'notes',
];

const WISHLIST_SELECT = '
    SELECT WishlistItem.*, Tanks.custom_name AS tank_name
    FROM WishlistItem
    LEFT JOIN Tanks ON WishlistItem.tank_id = Tanks.id
';

function findOwnedWishlistItem(PDO $pdo, int $id, int $userId): ?array
{
    $stmt = $pdo->prepare(WISHLIST_SELECT . ' WHERE WishlistItem.id = ? AND WishlistItem.user_id = ? LIMIT 1');
    $stmt->execute([$id, $userId]);
    $item = $stmt->fetch();
    return $item ?: null;
}

function isOwnedTank(PDO $pdo, int $tankId, int $userId): bool
{
    $stmt = $pdo->prepare('SELECT id FROM Tanks WHERE id = ? AND user_id = ? LIMIT 1');
    $stmt->execute([$tankId, $userId]);
    return (bool) $stmt->fetch();
}

function normalizeValue($value)
{
    // PDO's execute() casts scalars to strings for binding, and (string) false
    // is '' rather than '0', which MySQL rejects for numeric/empty-vs-null columns.
    if (is_bool($value)) {
        return (int) $value;
    }
    return $value === '' ? null : $value;
}

try {
    switch ($method) {
        case 'GET':
            if ($id === null) {
                $sql = WISHLIST_SELECT . ' WHERE WishlistItem.user_id = ?';
                $params = [$userId];

                if (isset($_GET['tank_id'])) {
                    $sql .= ' AND WishlistItem.tank_id = ?';
                    $params[] = (int) $_GET['tank_id'];
                }

                $sql .= ' ORDER BY WishlistItem.created_at DESC';

                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                send_json($stmt->fetchAll());
            }

            $item = findOwnedWishlistItem($pdo, $id, $userId);
            if (!$item) {
                send_json(['error' => 'Wishlist item not found'], 404);
            }
            send_json($item);
            break;

        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true) ?? [];

            if (trim($input['item_name'] ?? '') === '') {
                send_json(['error' => 'item_name is required'], 400);
            }

            if (trim($input['category'] ?? '') === '') {
                send_json(['error' => 'category is required'], 400);
            }

            $tankId = null;
            if (!empty($input['tank_id'])) {
                $tankId = (int) $input['tank_id'];
                if (!isOwnedTank($pdo, $tankId, $userId)) {
                    send_json(['error' => 'tank_id must reference one of your own tanks'], 400);
                }
            }

            $stmt = $pdo->prepare(
                'INSERT INTO WishlistItem (user_id, tank_id, item_name, category, estimated_price, store_or_source, priority, compatibility_notes, purchase_status, notes)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $userId,
                $tankId,
                trim($input['item_name']),
                trim($input['category']),
                normalizeValue($input['estimated_price'] ?? null),
                $input['store_or_source'] ?? null,
                $input['priority'] ?? 'Medium',
                $input['compatibility_notes'] ?? null,
                $input['purchase_status'] ?? 'Wanted',
                $input['notes'] ?? null,
            ]);

            send_json(findOwnedWishlistItem($pdo, (int) $pdo->lastInsertId(), $userId), 201);
            break;

        case 'PUT':
        case 'PATCH':
            if ($id === null) {
                send_json(['error' => 'id is required'], 400);
            }

            if (!findOwnedWishlistItem($pdo, $id, $userId)) {
                send_json(['error' => 'Wishlist item not found'], 404);
            }

            $input = json_decode(file_get_contents('php://input'), true) ?? [];

            if (array_key_exists('tank_id', $input) && !empty($input['tank_id'])) {
                if (!isOwnedTank($pdo, (int) $input['tank_id'], $userId)) {
                    send_json(['error' => 'tank_id must reference one of your own tanks'], 400);
                }
            }

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
            $values[] = $userId;

            $sql = 'UPDATE WishlistItem SET ' . implode(', ', $setClauses) . ' WHERE id = ? AND user_id = ?';
            $stmt = $pdo->prepare($sql);
            $stmt->execute($values);

            send_json(findOwnedWishlistItem($pdo, $id, $userId));
            break;

        case 'DELETE':
            if ($id === null) {
                send_json(['error' => 'id is required'], 400);
            }

            if (!findOwnedWishlistItem($pdo, $id, $userId)) {
                send_json(['error' => 'Wishlist item not found'], 404);
            }

            $stmt = $pdo->prepare('DELETE FROM WishlistItem WHERE id = ? AND user_id = ?');
            $stmt->execute([$id, $userId]);

            send_json(['success' => true]);
            break;

        default:
            send_json(['error' => 'Method not allowed'], 405);
    }
} catch (PDOException $e) {
    error_log('WishlistItem API anomaly: ' . $e->getMessage());
    send_json(['error' => 'An infrastructure resource issue occurred. Please retry.'], 500);
}
