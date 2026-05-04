<?php
// src/controllers/record.php

if ($action === 'new' || $action === 'edit') {
    $record = null;
    $carId = (int)($_GET['car_id'] ?? 0);

    if ($action === 'edit' && $id) {
        $stmt = $pdo->prepare('SELECT * FROM maintenance_records WHERE id = ?');
        $stmt->execute([$id]);
        $record = $stmt->fetch();
        if (!$record) { http_response_code(404); echo 'Not found'; return; }
        $carId = (int)$record['car_id'];
    }

    if ($method === 'POST') {
        $invoiceFile = $record['invoice_file'] ?? null;
        $uploadsDir  = uploads_dir();

        if (!empty($_FILES['invoice']['name']) && $_FILES['invoice']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['pdf', 'jpg', 'jpeg', 'png', 'webp'];
            $ext = strtolower(pathinfo($_FILES['invoice']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed, true)) {
                die(t('record.bad_file_type'));
            }
            $newName = bin2hex(random_bytes(8)) . '.' . $ext;
            $dest = $uploadsDir . '/' . $newName;
            if (!move_uploaded_file($_FILES['invoice']['tmp_name'], $dest)) {
                die(t('record.upload_error'));
            }
            if ($invoiceFile && file_exists($uploadsDir . '/' . $invoiceFile)) {
                @unlink($uploadsDir . '/' . $invoiceFile);
            }
            $invoiceFile = $newName;
        }

        $typeId = $_POST['maintenance_type_id'] ?? '';
        $typeId = $typeId === '' ? null : (int)$typeId;

        $data = [
            'car_id'              => (int)$_POST['car_id'],
            'maintenance_type_id' => $typeId,
            'date'                => $_POST['date'] ?? date('Y-m-d'),
            'km'                  => $_POST['km'] !== '' ? (int)$_POST['km'] : null,
            'cost'                => $_POST['cost'] !== '' ? (float)str_replace(',', '.', $_POST['cost']) : null,
            'notes'               => trim($_POST['notes'] ?? '') ?: null,
            'invoice_file'        => $invoiceFile,
        ];

        if ($record) {
            $stmt = $pdo->prepare('UPDATE maintenance_records SET car_id=?, maintenance_type_id=?, date=?, km=?, cost=?, notes=?, invoice_file=? WHERE id=?');
            $stmt->execute([...array_values($data), $record['id']]);
        } else {
            $stmt = $pdo->prepare('INSERT INTO maintenance_records (car_id, maintenance_type_id, date, km, cost, notes, invoice_file) VALUES (?,?,?,?,?,?,?)');
            $stmt->execute(array_values($data));
            if ($data['km'] !== null) {
                $pdo->prepare('UPDATE cars SET current_km = ?, current_km_date = ? WHERE id = ? AND current_km < ?')
                    ->execute([$data['km'], $data['date'], $data['car_id'], $data['km']]);
            }
        }
        redirect('?p=car&id=' . $data['car_id']);
    }

    if (!$carId) { redirect('?p=home'); }
    $stmt = $pdo->prepare('SELECT * FROM cars WHERE id = ?');
    $stmt->execute([$carId]);
    $car = $stmt->fetch();
    if (!$car) { http_response_code(404); echo 'Not found'; return; }

    $types = $pdo->query('SELECT * FROM maintenance_types ORDER BY name')->fetchAll();

    require __DIR__ . '/../views/record_form.php';
    return;
}

if ($action === 'delete' && $method === 'POST' && $id) {
    $stmt = $pdo->prepare('SELECT * FROM maintenance_records WHERE id = ?');
    $stmt->execute([$id]);
    $r = $stmt->fetch();
    if ($r) {
        if ($r['invoice_file']) {
            @unlink(uploads_dir() . '/' . $r['invoice_file']);
        }
        $pdo->prepare('DELETE FROM maintenance_records WHERE id = ?')->execute([$id]);
        redirect('?p=car&id=' . $r['car_id']);
    }
    redirect('?p=home');
}

redirect('?p=home');
