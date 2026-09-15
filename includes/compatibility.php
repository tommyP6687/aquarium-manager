<?php

function makeCompatibilityWarning(array $organism, string $message): array
{
    return [
        'tank_id' => $organism['tank_id'],
        'tank_name' => $organism['tank_name'],
        'organism_id' => $organism['organism_id'],
        'custom_name' => $organism['custom_name'],
        'warning' => $message,
    ];
}

function computeCompatibilityWarnings(PDO $pdo, int $userId, ?int $tankId = null): array
{
    $sql = "
        SELECT Organism.id AS organism_id, Organism.custom_name, Organism.tank_id, Organism.species_id,
               Tanks.custom_name AS tank_name, Tanks.volume_gallons,
               Species.common_name AS species_common_name, Species.organism_type,
               Species.min_temp_f, Species.max_temp_f, Species.min_ph, Species.max_ph,
               Species.min_tank_size_gallons, Species.adult_size_inches, Species.temperament,
               Species.min_group_size, Species.care_level, Species.is_plant_safe, Species.is_shrimp_safe
        FROM Organism
        JOIN Tanks ON Organism.tank_id = Tanks.id
        JOIN Species ON Organism.species_id = Species.id
        WHERE Organism.user_id = ?
    ";
    $params = [$userId];

    if ($tankId !== null) {
        $sql .= ' AND Organism.tank_id = ?';
        $params[] = $tankId;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $organisms = $stmt->fetchAll();

    $stmt = $pdo->prepare(
        "SELECT tank_id, parameter_name, value FROM WaterTest
         WHERE user_id = ? AND parameter_name IN ('Temperature', 'pH')
         ORDER BY tested_at DESC"
    );
    $stmt->execute([$userId]);

    $latestByTankParam = [];
    foreach ($stmt->fetchAll() as $row) {
        $key = $row['tank_id'] . '|' . $row['parameter_name'];
        if (!isset($latestByTankParam[$key])) {
            $latestByTankParam[$key] = (float) $row['value'];
        }
    }

    $organismsByTank = [];
    foreach ($organisms as $o) {
        $organismsByTank[$o['tank_id']][] = $o;
    }

    $warnings = [];

    foreach ($organisms as $o) {
        $tankMates = $organismsByTank[$o['tank_id']];
        $sameSpeciesCount = count(array_filter($tankMates, fn($m) => $m['species_id'] === $o['species_id']));
        $hasInvertebrate = count(array_filter($tankMates, fn($m) => $m['organism_type'] === 'Invertebrate')) > 0;
        $hasPlant = count(array_filter($tankMates, fn($m) => $m['organism_type'] === 'Plant')) > 0;

        if ($o['min_tank_size_gallons'] !== null && $o['volume_gallons'] !== null
            && (float) $o['min_tank_size_gallons'] > (float) $o['volume_gallons']) {
            $warnings[] = makeCompatibilityWarning($o, "This tank may be too small — {$o['species_common_name']} needs at least {$o['min_tank_size_gallons']} gallons.");
        }

        if ($o['temperament'] && in_array($o['temperament'], ['Aggressive', 'Semi-aggressive'], true) && count($tankMates) > 1) {
            $warnings[] = makeCompatibilityWarning($o, "{$o['species_common_name']} can be {$o['temperament']} — keep an eye on tankmates.");
        }

        if ($o['min_group_size'] !== null && (int) $o['min_group_size'] > 1 && $sameSpeciesCount < (int) $o['min_group_size']) {
            $warnings[] = makeCompatibilityWarning($o, "{$o['species_common_name']} does best in groups of {$o['min_group_size']} or more (currently {$sameSpeciesCount}).");
        }

        if ($o['is_shrimp_safe'] !== null && !$o['is_shrimp_safe'] && $hasInvertebrate) {
            $warnings[] = makeCompatibilityWarning($o, "{$o['species_common_name']} may not be safe with the invertebrates in this tank.");
        }

        if ($o['is_plant_safe'] !== null && !$o['is_plant_safe'] && $hasPlant) {
            $warnings[] = makeCompatibilityWarning($o, "{$o['species_common_name']} may eat the plants in this tank.");
        }

        $temp = $latestByTankParam[$o['tank_id'] . '|Temperature'] ?? null;
        if ($temp !== null && $o['min_temp_f'] !== null && $o['max_temp_f'] !== null
            && ($temp < (float) $o['min_temp_f'] || $temp > (float) $o['max_temp_f'])) {
            $warnings[] = makeCompatibilityWarning($o, "Tank temperature ({$temp}°F) is outside {$o['species_common_name']}'s comfortable range ({$o['min_temp_f']}-{$o['max_temp_f']}°F).");
        }

        $ph = $latestByTankParam[$o['tank_id'] . '|pH'] ?? null;
        if ($ph !== null && $o['min_ph'] !== null && $o['max_ph'] !== null
            && ($ph < (float) $o['min_ph'] || $ph > (float) $o['max_ph'])) {
            $warnings[] = makeCompatibilityWarning($o, "Tank pH ({$ph}) is outside {$o['species_common_name']}'s comfortable range ({$o['min_ph']}-{$o['max_ph']}).");
        }

        if ($o['care_level'] && $o['care_level'] !== 'Beginner') {
            $warnings[] = makeCompatibilityWarning($o, "{$o['species_common_name']} is rated {$o['care_level']} care — good to research before committing.");
        }
    }

    foreach ($organismsByTank as $tid => $mates) {
        $totalInches = 0.0;
        $hasSize = false;

        foreach ($mates as $m) {
            if ($m['adult_size_inches'] !== null) {
                $totalInches += (float) $m['adult_size_inches'];
                $hasSize = true;
            }
        }

        $volume = $mates[0]['volume_gallons'] ?? null;

        if ($hasSize && $volume !== null && $totalInches > (float) $volume) {
            $warnings[] = [
                'tank_id' => $tid,
                'tank_name' => $mates[0]['tank_name'],
                'organism_id' => null,
                'custom_name' => null,
                'warning' => "This tank may be overstocked (roughly {$totalInches}\" of adult fish for a {$volume} gallon tank).",
            ];
        }
    }

    return $warnings;
}
