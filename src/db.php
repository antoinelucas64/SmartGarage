<?php
// src/db.php — PDO connection, schema init and seeding

const SCHEMA_VERSION = 1;

function db_path(): string {
    $env = getenv('DATA_DIR');
    $dir = $env ?: (__DIR__ . '/../data');
    if (!is_dir($dir)) @mkdir($dir, 0775, true);
    return $dir . '/smart-garage.sqlite';
}

function db(): PDO {
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    $dbPath = db_path();
    $isNew  = !file_exists($dbPath);

    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec('PRAGMA foreign_keys = ON');
    $pdo->exec('PRAGMA journal_mode = WAL');

    if ($isNew) {
        $schema = file_get_contents(__DIR__ . '/../schema.sql');
        $pdo->exec($schema);
        seed_default_types($pdo);
        $pdo->exec('PRAGMA user_version = ' . SCHEMA_VERSION);
    } else {
        migrate($pdo);
    }

    return $pdo;
}

function seed_default_types(PDO $pdo): void {
    // Default seed uses English keys; the UI translates them via lang files
    // when the names match. We seed in the user's setup language (or English).
    // For simplicity, we always seed in English; user can edit/translate later.
    $defaults = ['Oil change', 'Timing belt', 'Brake pads', 'Brake discs',
                 'Air filter', 'Cabin filter', 'Tires', 'Vehicle inspection',
                 'Brake fluid', 'Spark plugs', 'Battery'];
    $stmt = $pdo->prepare('INSERT OR IGNORE INTO maintenance_types (name) VALUES (?)');
    foreach ($defaults as $t) $stmt->execute([$t]);
}

function migrate(PDO $pdo): void {
    $current = (int)$pdo->query('PRAGMA user_version')->fetchColumn();
    if ($current >= SCHEMA_VERSION) return;

    // Future migrations go here.
    // Example:
    // if ($current < 2) {
    //     $pdo->exec('ALTER TABLE cars ADD COLUMN color TEXT');
    // }

    $pdo->exec('PRAGMA user_version = ' . SCHEMA_VERSION);
}

/** True if no user has been created yet. */
function needs_setup(PDO $pdo): bool {
    return (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn() === 0;
}
