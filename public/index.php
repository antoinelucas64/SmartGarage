<?php
// public/index.php — entry point and router

require __DIR__ . '/../src/db.php';
require __DIR__ . '/../src/i18n.php';
require __DIR__ . '/../src/auth.php';
require __DIR__ . '/../src/helpers.php';

$pdo = db();

$route  = $_GET['p']  ?? 'home';
$action = $_GET['a']  ?? null;
$id     = isset($_GET['id']) ? (int)$_GET['id'] : null;
$method = $_SERVER['REQUEST_METHOD'];

// 1. Setup gate: no users yet → force setup
if (needs_setup($pdo)) {
    if ($route !== 'setup') redirect('?p=setup');
}

// 2. Determine user/lang for everything else
start_session();
$user = current_user($pdo);

// Anonymous default lang from Accept-Language
if (!$user) {
    $lang = 'en';
    $accept = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
    if (str_contains(strtolower($accept), 'fr')) $lang = 'fr';
    if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'fr'])) {
        $lang = $_GET['lang'];
    }
} else {
    $lang = $user['language'] ?: 'en';
}
I18n::load($lang);

// 3. Public routes (no auth)
$publicRoutes = ['setup', 'login'];

// 4. Auth gate
if (!in_array($route, $publicRoutes, true) && !$user) {
    redirect('?p=login');
}

// 5. Dispatch
try {
    switch ($route) {
        case 'setup':
            require __DIR__ . '/../src/controllers/setup.php';
            break;
        case 'login':
            require __DIR__ . '/../src/controllers/login.php';
            break;
        case 'logout':
            logout();
            redirect('?p=login');
        case 'settings':
            require __DIR__ . '/../src/controllers/settings.php';
            break;
        case 'home':
            require __DIR__ . '/../src/views/home.php';
            break;
        case 'car':
            require __DIR__ . '/../src/controllers/car.php';
            break;
        case 'model':
            require __DIR__ . '/../src/controllers/model.php';
            break;
        case 'maintenance_type':
            require __DIR__ . '/../src/controllers/maintenance_type.php';
            break;
        case 'record':
            require __DIR__ . '/../src/controllers/record.php';
            break;
        case 'invoice':
            require __DIR__ . '/../src/controllers/invoice.php';
            break;
        default:
            http_response_code(404);
            echo 'Not found';
    }
} catch (Throwable $ex) {
    http_response_code(500);
    if (getenv('APP_DEBUG') === '1') {
        echo '<pre>' . e($ex->getMessage()) . "\n" . e($ex->getTraceAsString()) . '</pre>';
    } else {
        echo '<h1>Internal error</h1><p>Please check the server logs.</p>';
        error_log('[fleet-track] ' . $ex->getMessage() . "\n" . $ex->getTraceAsString());
    }
}
