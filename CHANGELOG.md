# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.1.0] - 2026-05-02

### Added
- Initial release
- Car fleet dashboard with recurring-maintenance alerts (overdue / soon / ok)
- Car CRUD with model, license plate, in-service date, mileage tracking
- Car models with per-model recurring maintenance plan (km and/or months)
- Global library of maintenance types
- Maintenance history per car with cost, notes, and PDF/image invoice upload
- Single-user authentication: first-run setup wizard, bcrypt password hashing
- Login rate limiting (5 attempts / IP / 10 minutes)
- Multi-language UI (English, French) with per-user preference
- Settings page: change password, change language
- Single-container Docker image (nginx + PHP-FPM + supervisord on Alpine)
- docker-compose with persistent volumes for data and uploads
- Manual installation guide for nginx / PHP-FPM / YunoHost
