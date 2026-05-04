<?php
// src/controllers/maintenance_type.php — bibliothèque globale des types d'entretiens

if ($action === 'new') {
    if ($method === 'POST') {
        $name = trim($_POST['name'] ?? '');
        if ($name !== '') {
            try {
                $pdo->prepare('INSERT INTO maintenance_types (name) VALUES (?)')->execute([$name]);
            } catch (PDOException $e) {
                // déjà existant : on ignore
            }
        }
        redirect('?p=maintenance_type');
    }
    require __DIR__ . '/../views/maintenance_type_new.php';
    return;
}

if ($action === 'delete' && $method === 'POST' && $id) {
    // Si utilisé dans des records → on met juste maintenance_type_id à NULL via FK ON DELETE SET NULL
    // Mais on bloque si utilisé dans model_maintenance (cascade aurait supprimé)
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM model_maintenance WHERE maintenance_type_id = ?');
    $stmt->execute([$id]);
    if ($stmt->fetchColumn() > 0) {
        die('Impossible : ce type est utilisé comme entretien récurrent sur un modèle.');
    }
    $pdo->prepare('DELETE FROM maintenance_types WHERE id = ?')->execute([$id]);
    redirect('?p=maintenance_type');
}

$types = $pdo->query('
    SELECT mt.*,
        (SELECT COUNT(*) FROM model_maintenance WHERE maintenance_type_id = mt.id) AS used_in_models,
        (SELECT COUNT(*) FROM maintenance_records WHERE maintenance_type_id = mt.id) AS used_in_records
    FROM maintenance_types mt
    ORDER BY mt.name
')->fetchAll();

require __DIR__ . '/../views/maintenance_type_list.php';
