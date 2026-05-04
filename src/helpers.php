<?php
// src/helpers.php — utility + alert computation

function e(?string $s): string {
    return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
}

function fmtKm(?int $km): string {
    if ($km === null) return '—';
    $sep = I18n::current() === 'fr' ? ' ' : ',';
    return number_format($km, 0, '.', $sep) . ' ' . t('common.km');
}

function fmtDate(?string $d): string {
    if (!$d) return '—';
    $ts = strtotime($d);
    if (!$ts) return '—';
    return I18n::current() === 'fr' ? date('d/m/Y', $ts) : date('Y-m-d', $ts);
}

function fmtMoney(?float $v): string {
    if ($v === null) return '—';
    if (I18n::current() === 'fr') {
        return number_format($v, 2, ',', ' ') . ' €';
    }
    return '€ ' . number_format($v, 2, '.', ',');
}

/** Translate a default seeded type name if it matches a known key. */
function translateTypeName(string $name): string {
    $map = [
        'Oil change'         => 'default_type.oil_change',
        'Timing belt'        => 'default_type.timing_belt',
        'Brake pads'         => 'default_type.brake_pads',
        'Brake discs'        => 'default_type.brake_discs',
        'Air filter'         => 'default_type.air_filter',
        'Cabin filter'       => 'default_type.cabin_filter',
        'Tires'              => 'default_type.tires',
        'Vehicle inspection' => 'default_type.inspection',
        'Brake fluid'        => 'default_type.brake_fluid',
        'Spark plugs'        => 'default_type.spark_plugs',
        'Battery'            => 'default_type.battery',
    ];
    return isset($map[$name]) ? t($map[$name]) : $name;
}

function computeAlerts(PDO $pdo, array $car): array {
    $alerts = [];

    $stmt = $pdo->prepare('
        SELECT mm.*, mt.name AS type_name
        FROM model_maintenance mm
        JOIN maintenance_types mt ON mt.id = mm.maintenance_type_id
        WHERE mm.car_model_id = ?
    ');
    $stmt->execute([$car['car_model_id']]);
    $recurrences = $stmt->fetchAll();
    if (!$recurrences) return $alerts;

    $stmt = $pdo->prepare('
        SELECT maintenance_type_id, MAX(date) AS last_date,
               (SELECT km FROM maintenance_records r2
                WHERE r2.car_id = r.car_id AND r2.maintenance_type_id = r.maintenance_type_id
                ORDER BY date DESC LIMIT 1) AS last_km
        FROM maintenance_records r
        WHERE car_id = ? AND maintenance_type_id IS NOT NULL
        GROUP BY maintenance_type_id
    ');
    $stmt->execute([$car['id']]);
    $lastByType = [];
    foreach ($stmt->fetchAll() as $row) {
        $lastByType[$row['maintenance_type_id']] = $row;
    }

    $today = new DateTimeImmutable('today');
    $currentKm = (int)$car['current_km'];

    foreach ($recurrences as $r) {
        $last = $lastByType[$r['maintenance_type_id']] ?? null;

        $refDate = $last ? $last['last_date'] : $car['service_date'];
        $refKm   = $last ? (int)$last['last_km'] : 0;

        $nextDate = null; $nextKm = null;
        $daysLeft = null; $kmLeft = null;

        if ($r['recurrence_months']) {
            $nextDate = (new DateTimeImmutable($refDate))
                ->modify('+' . (int)$r['recurrence_months'] . ' months');
            $daysLeft = (int)$today->diff($nextDate)->format('%r%a');
        }
        if ($r['recurrence_km']) {
            $nextKm = $refKm + (int)$r['recurrence_km'];
            $kmLeft = $nextKm - $currentKm;
        }

        $status = 'ok';
        if (($daysLeft !== null && $daysLeft < 0) || ($kmLeft !== null && $kmLeft < 0)) {
            $status = 'overdue';
        } elseif (($daysLeft !== null && $daysLeft <= 30) || ($kmLeft !== null && $kmLeft <= 1000)) {
            $status = 'warn';
        }

        $alerts[] = [
            'car_id'    => $car['id'],
            'car_name'  => $car['name'],
            'type_name' => translateTypeName($r['type_name']),
            'next_date' => $nextDate ? $nextDate->format('Y-m-d') : null,
            'next_km'   => $nextKm,
            'days_left' => $daysLeft,
            'km_left'   => $kmLeft,
            'status'    => $status,
        ];
    }

    usort($alerts, function($a, $b) {
        $order = ['overdue' => 0, 'warn' => 1, 'ok' => 2];
        if ($order[$a['status']] !== $order[$b['status']]) {
            return $order[$a['status']] - $order[$b['status']];
        }
        $aMin = min(array_filter([$a['km_left'], $a['days_left'] !== null ? $a['days_left'] * 50 : null], fn($x) => $x !== null) ?: [PHP_INT_MAX]);
        $bMin = min(array_filter([$b['km_left'], $b['days_left'] !== null ? $b['days_left'] * 50 : null], fn($x) => $x !== null) ?: [PHP_INT_MAX]);
        return $aMin <=> $bMin;
    });

    return $alerts;
}

function allAlerts(PDO $pdo): array {
    $cars = $pdo->query('SELECT * FROM cars')->fetchAll();
    $all = [];
    foreach ($cars as $car) {
        foreach (computeAlerts($pdo, $car) as $a) $all[] = $a;
    }
    return array_filter($all, fn($a) => $a['status'] !== 'ok');
}

function statusBadge(string $status): string {
    $label = match($status) {
        'overdue' => t('alert.overdue'),
        'warn'    => t('alert.warn'),
        default   => t('alert.ok'),
    };
    return '<span class="badge badge-' . $status . '">' . e($label) . '</span>';
}

function formatDue(array $a): string {
    $parts = [];
    if ($a['next_km'] !== null) {
        $sign = $a['km_left'] < 0 ? '−' : t('common.in');
        $parts[] = $sign . ' ' . fmtKm(abs($a['km_left'])) . ' (' . fmtKm($a['next_km']) . ')';
    }
    if ($a['next_date'] !== null) {
        $sign = $a['days_left'] < 0 ? '−' : t('common.in');
        $parts[] = $sign . ' ' . abs($a['days_left']) . ' ' . t('common.days') . ' (' . fmtDate($a['next_date']) . ')';
    }
    return implode(' · ', $parts);
}

function redirect(string $url): never {
    header('Location: ' . $url);
    exit;
}

function uploads_dir(): string {
    $env = getenv('UPLOADS_DIR');
    $dir = $env ?: (__DIR__ . '/../public/uploads');
    if (!is_dir($dir)) @mkdir($dir, 0775, true);
    return $dir;
}
