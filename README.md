# Laravel ERD Generator Package

[![Tests](https://github.com/akbarhossainr/laravel-erd-generator/actions/workflows/php.yml/badge.svg)](https://github.com/akbarhossainr/laravel-erd-generator/actions)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/akbarhossainr/laravel-erd-generator.svg?style=flat-square)](https://packagist.org/packages/akbarhossainr/laravel-erd-generator)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

This package provides an Artisan command to generate Entity-Relationship Diagrams (ERDs) from your Eloquent models. Supported formats: Mermaid.js, PlantUML, and Graphviz.

## Features

- Auto-detects all Eloquent models in `app/Models`.
- Maps relationships: `hasMany`, `belongsTo`, `morphTo`, etc.
- Optionally shows table columns (`--columns` flag).
- Supports pivot tables.

## Usage

```bash
php artisan make:erd --format=mermaid --output=docs/erd.md
```

- `--format`: Output format (`mermaid`, `plantuml`, `graphviz`). Default: `mermaid`.
- `--output`: File path to save the ERD. If omitted, outputs to console.
- `--columns`: Include table columns in the diagram.

## Installation

1. Require the package via Composer:

   ```bash
   composer require akbarhossainr/laravel-erd-generator
   ```

2. Use the command as shown above.

## License

MIT
