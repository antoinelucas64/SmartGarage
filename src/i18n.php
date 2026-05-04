<?php
// src/i18n.php — translation loader

class I18n {
    private static array $strings = [];
    private static string $current = 'en';
    private static array $available = ['en' => 'English', 'fr' => 'Français'];

    public static function load(string $lang): void {
        if (!isset(self::$available[$lang])) $lang = 'en';
        $file = __DIR__ . '/lang/' . $lang . '.php';
        self::$strings = require $file;
        self::$current = $lang;
    }

    public static function current(): string {
        return self::$current;
    }

    public static function available(): array {
        return self::$available;
    }

    public static function t(string $key, ...$args): string {
        $s = self::$strings[$key] ?? $key;
        if ($args) {
            $s = vsprintf($s, $args);
        }
        return $s;
    }
}

function t(string $key, ...$args): string {
    return I18n::t($key, ...$args);
}

function et(string $key, ...$args): void {
    echo htmlspecialchars(I18n::t($key, ...$args), ENT_QUOTES, 'UTF-8');
}
