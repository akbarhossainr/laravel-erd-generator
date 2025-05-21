# Laravel ERD Generator Package

This package provides an Artisan command to generate Entity-Relationship Diagrams (ERDs) from your Eloquent models. Supported formats: Mermaid.js, PlantUML, and Graphviz.

## Features
- Auto-detects all Eloquent models in `app/Models`.
- Maps relationships: `hasMany`, `belongsTo`, `morphTo`, etc.
- Optionally shows table columns (`--columns` flag).
- Supports pivot tables.

## Usage

```
php artisan make:erd --format=mermaid --output=docs/erd.md
```

- `--format`: Output format (`mermaid`, `plantuml`, `graphviz`). Default: `mermaid`.
- `--output`: File path to save the ERD. If omitted, outputs to console.
- `--columns`: Include table columns in the diagram.

## Installation

1. Require the package via Composer (after publishing to Packagist or using VCS):
   ```bash
   composer require your-vendor/laravel-erd-generator
   ```
2. Publish config (if available) and use the command as shown above.

## License
MIT
