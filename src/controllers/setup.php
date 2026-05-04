<?php
// src/controllers/setup.php — first-run user creation

// Pick language from query/accept
if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'fr'])) {
    $lang = $_GET['lang'];
} else {
    $lang = 'en';
    if (str_contains(strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? ''), 'fr')) {
        $lang = 'fr';
    }
}
I18n::load($lang);

$errors = [];
if ($method === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['password_confirm'] ?? '';
    $lang     = in_array($_POST['language'] ?? '', ['en', 'fr']) ? $_POST['language'] : 'en';

    if ($username === '')               $errors[] = t('auth.username_required');
    if (strlen($password) < 8)          $errors[] = t('auth.password_too_short');
    if ($password !== $confirm)         $errors[] = t('auth.password_mismatch');

    if (!$errors) {
        create_user($pdo, $username, $password, $lang);
        I18n::load($lang);
        // auto-login
        login($pdo, $username, $password);
        redirect('?p=home');
    }
}

require __DIR__ . '/../views/setup.php';
