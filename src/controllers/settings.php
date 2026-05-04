<?php
// src/controllers/settings.php

$flash = null;
$error = null;

if ($method === 'POST') {
    if (($_POST['action'] ?? '') === 'language') {
        $newLang = $_POST['language'] ?? 'en';
        if (in_array($newLang, ['en', 'fr'])) {
            $stmt = $pdo->prepare('UPDATE users SET language = ? WHERE id = ?');
            $stmt->execute([$newLang, $user['id']]);
            I18n::load($newLang);
            $flash = t('settings.language_saved');
            $user['language'] = $newLang;
        }
    } elseif (($_POST['action'] ?? '') === 'password') {
        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password'] ?? '';
        $confirm = $_POST['password_confirm'] ?? '';

        $row = $pdo->prepare('SELECT password_hash FROM users WHERE id = ?');
        $row->execute([$user['id']]);
        $hash = $row->fetchColumn();

        if (!password_verify($current, $hash)) {
            $error = t('settings.wrong_current_password');
        } elseif (strlen($new) < 8) {
            $error = t('auth.password_too_short');
        } elseif ($new !== $confirm) {
            $error = t('auth.password_mismatch');
        } else {
            $newHash = password_hash($new, PASSWORD_DEFAULT);
            $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?')->execute([$newHash, $user['id']]);
            $flash = t('settings.password_changed');
        }
    }
}

require __DIR__ . '/../views/settings.php';
