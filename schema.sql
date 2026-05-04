-- Schema version is tracked via PRAGMA user_version (set in db.php)

-- Users (single-user app, but proper user table)
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    language TEXT NOT NULL DEFAULT 'en',
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Login attempts for basic rate limiting
CREATE TABLE IF NOT EXISTS login_attempts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    ip TEXT NOT NULL,
    attempted_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_login_attempts_ip ON login_attempts(ip, attempted_at);

-- Car models (Mégane 3, Civic 9...)
CREATE TABLE IF NOT EXISTS car_models (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL UNIQUE
);

-- Global maintenance type library
CREATE TABLE IF NOT EXISTS maintenance_types (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL UNIQUE
);

-- Recurring maintenance per model
CREATE TABLE IF NOT EXISTS model_maintenance (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    car_model_id INTEGER NOT NULL,
    maintenance_type_id INTEGER NOT NULL,
    recurrence_km INTEGER,
    recurrence_months INTEGER,
    FOREIGN KEY (car_model_id) REFERENCES car_models(id) ON DELETE CASCADE,
    FOREIGN KEY (maintenance_type_id) REFERENCES maintenance_types(id) ON DELETE CASCADE,
    UNIQUE (car_model_id, maintenance_type_id)
);

-- Cars
CREATE TABLE IF NOT EXISTS cars (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    license_plate TEXT,
    car_model_id INTEGER NOT NULL,
    service_date TEXT NOT NULL,
    current_km INTEGER NOT NULL DEFAULT 0,
    current_km_date TEXT NOT NULL,
    notes TEXT,
    FOREIGN KEY (car_model_id) REFERENCES car_models(id)
);

-- Maintenance history / invoices
CREATE TABLE IF NOT EXISTS maintenance_records (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    car_id INTEGER NOT NULL,
    maintenance_type_id INTEGER,
    date TEXT NOT NULL,
    km INTEGER,
    cost REAL,
    notes TEXT,
    invoice_file TEXT,
    FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE,
    FOREIGN KEY (maintenance_type_id) REFERENCES maintenance_types(id) ON DELETE SET NULL
);

CREATE INDEX IF NOT EXISTS idx_records_car ON maintenance_records(car_id);
CREATE INDEX IF NOT EXISTS idx_records_type ON maintenance_records(maintenance_type_id);
