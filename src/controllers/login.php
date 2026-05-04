<?php
// src/controllers/login.php

if ($user) redirect('?p=home');

$error = null;
if ($method === 'POST') {
    if (is_rate_limited($pdo)) {
        $error = t('auth.too_many');
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        if (login($pdo, $username, $password)) {
            clear_attempts($pdo);
            // Reload language preference of the user
            $u = current_user($pdo);
            redirect('?p=home');
        } else {
            record_failed_attempt($pdo);
            $error = t('auth.invalid');
        }
    }
}

require __DIR__ . '/../views/login.php';
