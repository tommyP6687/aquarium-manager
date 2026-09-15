<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/compatibility.php';

requireApiLogin();

$pdo = get_db();
$userId = $_SESSION['user_id'];

const RISING_PARAMETERS = ['Ammonia', 'Nitrite', 'Nitrate'];

function statusForScore(int $score): string
{
    if ($score >= 80) {
        return 'Stable';
    }
    if ($score >= 50) {
        return 'Needs Attention';
    }
    return 'Critical';
}

function hasRisingReading(PDO $pdo, int $userId, int $tankId): ?string
{
    foreach (RISING_PARAMETERS as $parameter) {
        $stmt = $pdo->prepare(
            'SELECT value FROM WaterTest WHERE user_id = ? AND tank_id = ? AND parameter_name = ?
             ORDER BY tested_at DESC LIMIT 2'
        );
        $stmt->execute([$userId, $tankId, $parameter]);
        $readings = $stmt->fetchAll();

        if (count($readings) === 2 && (float) $readings[0]['value'] > (float) $readings[1]['value']) {
            return $parameter;
        }
    }

    return null;
}

try {
    $sql = 'SELECT * FROM Tanks WHERE user_id = ?';
    $params = [$userId];

    if (isset($_GET['tank_id'])) {
        $sql .= ' AND id = ?';
        $params[] = (int) $_GET['tank_id'];
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $tanks = $stmt->fetchAll();

    $allWarnings = computeCompatibilityWarnings($pdo, $userId, isset($_GET['tank_id']) ? (int) $_GET['tank_id'] : null);
    $warningsByTank = [];
    foreach ($allWarnings as $warning) {
        $warningsByTank[$warning['tank_id']][] = $warning;
    }

    $results = [];

    foreach ($tanks as $tank) {
        $tankId = (int) $tank['id'];
        $score = 100;
        $reasons = [];

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM Organism WHERE tank_id = ? AND health_status != 'Healthy'");
        $stmt->execute([$tankId]);
        $unhealthyCount = (int) $stmt->fetchColumn();
        if ($unhealthyCount > 0) {
            $score -= 10 * $unhealthyCount;
            $reasons[] = ['priority' => 1, 'text' => 'One or more organisms need attention.'];
        }

        $warningCount = count($warningsByTank[$tankId] ?? []);
        if ($warningCount > 0) {
            $score -= min(5 * $warningCount, 20);
            $reasons[] = ['priority' => 6, 'text' => 'There are compatibility warnings for this tank worth reviewing.'];
        }

        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM Reminder WHERE tank_id = ? AND is_completed = 0 AND due_at < NOW()"
        );
        $stmt->execute([$tankId]);
        $overdueCount = (int) $stmt->fetchColumn();
        if ($overdueCount > 0) {
            $score -= min(5 * $overdueCount, 20);
            $reasons[] = ['priority' => 2, 'text' => 'You have overdue reminders for this tank.'];
        }

        $tankAgeDays = (strtotime('now') - strtotime($tank['created_at'])) / 86400;

        if ($tankAgeDays > 30) {
            $stmt = $pdo->prepare(
                'SELECT COUNT(*) FROM MaintenanceLog WHERE tank_id = ? AND performed_at > DATE_SUB(NOW(), INTERVAL 30 DAY)'
            );
            $stmt->execute([$tankId]);
            if ((int) $stmt->fetchColumn() === 0) {
                $score -= 10;
                $reasons[] = ['priority' => 5, 'text' => "It's been a while since you logged maintenance for this tank."];
            }
        }

        if ($tankAgeDays > 14) {
            $stmt = $pdo->prepare(
                'SELECT COUNT(*) FROM WaterTest WHERE tank_id = ? AND tested_at > DATE_SUB(NOW(), INTERVAL 14 DAY)'
            );
            $stmt->execute([$tankId]);
            if ((int) $stmt->fetchColumn() === 0) {
                $score -= 10;
                $reasons[] = ['priority' => 4, 'text' => "It's been a while since you tested the water."];
            }
        }

        $risingParameter = hasRisingReading($pdo, $userId, $tankId);
        if ($risingParameter !== null) {
            $score -= 10;
            $reasons[] = ['priority' => 3, 'text' => "{$risingParameter} has been rising. Consider a water change soon."];
        }

        $score = max(0, $score);

        usort($reasons, fn($a, $b) => $a['priority'] <=> $b['priority']);
        $suggestion = $reasons[0]['text'] ?? 'Everything looks great!';

        $results[] = [
            'tank_id' => $tank['id'],
            'tank_name' => $tank['custom_name'],
            'score' => $score,
            'status' => statusForScore($score),
            'suggestion' => $suggestion,
        ];
    }

    send_json($results);
} catch (PDOException $e) {
    error_log('Tank health anomaly: ' . $e->getMessage());
    send_json(['error' => 'An infrastructure resource issue occurred. Please retry.'], 500);
}
