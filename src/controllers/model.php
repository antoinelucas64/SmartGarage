<?php
// src/controllers/model.php — modèles de voitures + entretiens récurrents

if ($action === 'new') {
    if ($method === 'POST') {
        $name = trim($_POST['name'] ?? '');
        if ($name === '') { redirect('?p=model'); }
        $stmt = $pdo->prepare('INSERT INTO car_models (name) VALUES (?)');
        $stmt->execute([$name]);
        redirect('?p=model&id=' . $pdo->lastInsertId());
    }
    require __DIR__ . '/../views/model_new.php';
    return;
}

if ($action === 'delete' && $method === 'POST' && $id) {
    // Empêche suppression si voitures liées
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM cars WHERE car_model_id = ?');
    $stmt->execute([$id]);
    if ($stmt->fetchColumn() > 0) {
        die('Impossible : des voitures utilisent ce modèle.');
    }
    $pdo->prepare('DELETE FROM car_models WHERE id = ?')->execute([$id]);
    redirect('?p=model');
}

if ($action === 'add_maintenance' && $method === 'POST' && $id) {
    $typeId = (int)($_POST['maintenance_type_id'] ?? 0);
    $rkm = $_POST['recurrence_km'] !== '' ? (int)$_POST['recurrence_km'] : null;
    $rmo = $_POST['recurrence_months'] !== '' ? (int)$_POST['recurrence_months'] : null;
    if ($typeId && ($rkm || $rmo)) {
        try {
            $stmt = $pdo->prepare('INSERT INTO model_maintenance (car_model_id, maintenance_type_id, recurrence_km, recurrence_months) VALUES (?,?,?,?)');
            $stmt->execute([$id, $typeId, $rkm, $rmo]);
        } catch (PDOException $e) {
            // Doublon : on update
            $stmt = $pdo->prepare('UPDATE model_maintenance SET recurrence_km=?, recurrence_months=? WHERE car_model_id=? AND maintenance_type_id=?');
            $stmt->execute([$rkm, $rmo, $id, $typeId]);
        }
    }
    redirect('?p=model&id=' . $id);
}

if ($action === 'remove_maintenance' && $method === 'POST' && $id) {
    $mmId = (int)($_POST['mm_id'] ?? 0);
    $pdo->prepare('DELETE FROM model_maintenance WHERE id = ? AND car_model_id = ?')->execute([$mmId, $id]);
    redirect('?p=model&id=' . $id);
}

// Vue détail d'un modèle
if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM car_models WHERE id = ?');
    $stmt->execute([$id]);
    $model = $stmt->fetch();
    if (!$model) { http_response_code(404); echo 'Modèle introuvable'; return; }

    $stmt = $pdo->prepare('
        SELECT mm.*, mt.name AS type_name
        FROM model_maintenance mm
        JOIN maintenance_types mt ON mt.id = mm.maintenance_type_id
        WHERE mm.car_model_id = ?
        ORDER BY mt.name
    ');
    $stmt->execute([$id]);
    $maintenances = $stmt->fetchAll();

    $allTypes = $pdo->query('SELECT * FROM maintenance_types ORDER BY name')->fetchAll();
    $usedTypeIds = array_column($maintenances, 'maintenance_type_id');
    $availableTypes = array_filter($allTypes, fn($t) => !in_array($t['id'], $usedTypeIds));

    require __DIR__ . '/../views/model.php';
    return;
}

// Liste
$models = $pdo->query('
    SELECT m.*, COUNT(c.id) AS car_count
    FROM car_models m
    LEFT JOIN cars c ON c.car_model_id = m.id
    GROUP BY m.id
    ORDER BY m.name
')->fetchAll();
require __DIR__ . '/../views/model_list.php';
