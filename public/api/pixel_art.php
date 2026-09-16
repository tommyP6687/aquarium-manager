<?php
require_once __DIR__ . '/../../includes/auth.php';

requireApiLogin();

$pdo = get_db();
$userId = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];

const ALLOWED_GRID_SIZES = [16, 24, 32];

function validatePixelData($pixelData, int $gridSize): bool
{
    if (!is_array($pixelData) || count($pixelData) !== $gridSize) {
        return false;
    }

    foreach ($pixelData as $row) {
        if (!is_array($row) || count($row) !== $gridSize) {
            return false;
        }

        foreach ($row as $hex) {
            if (!is_string($hex) || !preg_match('/^#[0-9a-f]{6}$/i', $hex)) {
                return false;
            }
        }
    }

    return true;
}

try {
    switch ($method) {
        case 'GET':
            $stmt = $pdo->prepare('SELECT * FROM PixelArt WHERE user_id = ? ORDER BY created_at DESC');
            $stmt->execute([$userId]);
            send_json($stmt->fetchAll());
            break;

        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true) ?? [];

            $spriteName = trim($input['sprite_name'] ?? '');
            if ($spriteName === '') {
                send_json(['error' => 'sprite_name is required'], 400);
            }

            $gridSize = (int) ($input['grid_size'] ?? 0);
            if (!in_array($gridSize, ALLOWED_GRID_SIZES, true)) {
                send_json(['error' => 'grid_size must be one of: ' . implode(', ', ALLOWED_GRID_SIZES)], 400);
            }

            if (!validatePixelData($input['pixel_data'] ?? null, $gridSize)) {
                send_json(['error' => 'pixel_data must be a ' . $gridSize . 'x' . $gridSize . ' grid of hex color strings'], 400);
            }

            $stmt = $pdo->prepare(
                'INSERT INTO PixelArt (user_id, sprite_name, grid_size, pixel_data, source_type) VALUES (?, ?, ?, ?, ?)'
            );
            $stmt->execute([$userId, $spriteName, $gridSize, json_encode($input['pixel_data']), 'hand_drawn']);

            $newId = (int) $pdo->lastInsertId();
            $stmt = $pdo->prepare('SELECT * FROM PixelArt WHERE id = ? AND user_id = ?');
            $stmt->execute([$newId, $userId]);

            send_json($stmt->fetch(), 201);
            break;

        default:
            send_json(['error' => 'Method not allowed'], 405);
    }
} catch (PDOException $e) {
    error_log('PixelArt API anomaly: ' . $e->getMessage());
    send_json(['error' => 'An infrastructure resource issue occurred. Please retry.'], 500);
}
