<?php
require_once __DIR__ . '/../../includes/auth.php';

requireApiLogin();

$pdo = get_db();
$userId = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];

const GRID_SIZE = 16;

function generatePixelGrid(string $imagePath, int $gridSize): array
{
    $sourceImage = imagecreatefromstring(file_get_contents($imagePath));
    $width = imagesx($sourceImage);
    $height = imagesy($sourceImage);

    // No interactive crop UI yet -- crop to a centered square.
    $side = min($width, $height);
    $cropX = intdiv($width - $side, 2);
    $cropY = intdiv($height - $side, 2);

    $small = imagecreatetruecolor($gridSize, $gridSize);
    imagecopyresampled($small, $sourceImage, 0, 0, $cropX, $cropY, $gridSize, $gridSize, $side, $side);

    $palette = pixel_art_palette();
    $grid = [];

    for ($y = 0; $y < $gridSize; $y++) {
        $row = [];
        for ($x = 0; $x < $gridSize; $x++) {
            $rgb = imagecolorat($small, $x, $y);
            $r = ($rgb >> 16) & 0xFF;
            $g = ($rgb >> 8) & 0xFF;
            $b = $rgb & 0xFF;
            $row[] = rgb_to_hex(nearest_palette_color($r, $g, $b, $palette));
        }
        $grid[] = $row;
    }

    imagedestroy($sourceImage);
    imagedestroy($small);

    return $grid;
}

try {
    switch ($method) {
        case 'GET':
            $stmt = $pdo->prepare('SELECT * FROM PixelArt WHERE user_id = ? ORDER BY created_at DESC');
            $stmt->execute([$userId]);
            send_json($stmt->fetchAll());
            break;

        case 'POST':
            $spriteName = trim($_POST['sprite_name'] ?? '');

            if ($spriteName === '') {
                send_json(['error' => 'sprite_name is required'], 400);
            }

            if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
                send_json(['error' => 'A photo upload is required'], 400);
            }

            $grid = generatePixelGrid($_FILES['photo']['tmp_name'], GRID_SIZE);

            $stmt = $pdo->prepare(
                'INSERT INTO PixelArt (user_id, sprite_name, grid_size, pixel_data, source_type) VALUES (?, ?, ?, ?, ?)'
            );
            $stmt->execute([$userId, $spriteName, GRID_SIZE, json_encode($grid), 'photo_generated']);

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
