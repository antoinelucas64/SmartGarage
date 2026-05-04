<?php
// src/controllers/car.php — CRUD voiture

if ($action === 'new' || $action === 'edit') {
    $car = null;
    if ($action === 'edit' && $id) {
        $stmt = $pdo->prepare('SELECT * FROM cars WHERE id = ?');
        $stmt->execute([$id]);
        $car = $stmt->fetch();
        if (!$car) { http_response_code(404); echo 'Voiture introuvable'; return; }
    }

    if ($method === 'POST') {
        $data = [
            'name'           => trim($_POST['name'] ?? ''),
            'license_plate'  => trim($_POST['license_plate'] ?? '') ?: null,
            'car_model_id'   => (int)($_POST['car_model_id'] ?? 0),
            'service_date'   => $_POST['service_date'] ?? '',
            'current_km'     => (int)($_POST['current_km'] ?? 0),
            'current_km_date'=> $_POST['current_km_date'] ?? date('Y-m-d'),
            'notes'          => trim($_POST['notes'] ?? '') ?: null,
        ];
        if ($car) {
            $stmt = $pdo->prepare('UPDATE cars SET name=?, license_plate=?, car_model_id=?, service_date=?, current_km=?, current_km_date=?, notes=? WHERE id=?');
            $stmt->execute([...array_values($data), $car['id']]);
            redirect('?p=car&id=' . $car['id']);
        } else {
            $stmt = $pdo->prepare('INSERT INTO cars (name, license_plate, car_model_id, service_date, current_km, current_km_date, notes) VALUES (?,?,?,?,?,?,?)');
            $stmt->execute(array_values($data));
            redirect('?p=car&id=' . $pdo->lastInsertId());
        }
    }

    $models = $pdo->query('SELECT * FROM car_models ORDER BY name')->fetchAll();
    require __DIR__ . '/../views/car_form.php';
    return;
}

if ($action === 'update_km' && $method === 'POST' && $id) {
    $stmt = $pdo->prepare('UPDATE cars SET current_km = ?, current_km_date = ? WHERE id = ?');
    $stmt->execute([(int)$_POST['current_km'], $_POST['current_km_date'] ?? date('Y-m-d'), $id]);
    redirect('?p=car&id=' . $id);
}

if ($action === 'delete' && $method === 'POST' && $id) {
    $pdo->prepare('DELETE FROM cars WHERE id = ?')->execute([$id]);
    redirect('?p=home');
}

// Vue détail par défaut
if (!$id) { redirect('?p=home'); }

$stmt = $pdo->prepare('
    SELECT c.*, m.name AS model_name
    FROM cars c JOIN car_models m ON m.id = c.car_model_id
    WHERE c.id = ?
');
$stmt->execute([$id]);
$car = $stmt->fetch();
if (!$car) { http_response_code(404); echo 'Voiture introuvable'; return; }

$stmt = $pdo->prepare('
    SELECT r.*, mt.name AS type_name
    FROM maintenance_records r
    LEFT JOIN maintenance_types mt ON mt.id = r.maintenance_type_id
    WHERE r.car_id = ?
    ORDER BY r.date DESC, r.id DESC
');
$stmt->execute([$id]);
$records = $stmt->fetchAll();

$alerts = computeAlerts($pdo, $car);

require __DIR__ . '/../views/car.php';
