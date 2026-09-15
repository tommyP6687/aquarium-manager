<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/compatibility.php';

requireApiLogin();

$pdo = get_db();
$userId = $_SESSION['user_id'];
$tankId = isset($_GET['tank_id']) ? (int) $_GET['tank_id'] : null;

try {
    send_json(computeCompatibilityWarnings($pdo, $userId, $tankId));
} catch (PDOException $e) {
    error_log('Compatibility warnings anomaly: ' . $e->getMessage());
    send_json(['error' => 'An infrastructure resource issue occurred. Please retry.'], 500);
}
