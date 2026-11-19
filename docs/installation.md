---
title: Installation
weight: 20
description: Install the package and, optionally, publish its views and translations.
---

```bash
composer require webwizardsusa/filament-table-layouts
php artisan filament:assets
```

The service provider is registered by package discovery. There is no config file, migration or panel plugin. The
stylesheet is registered as a Filament asset, so `filament:assets` publishes it (Filament's `composer` hooks usually
run it for you).

## Translations and views (optional)

The labels live in the `table-layouts` translation namespace and the switcher in the `table-layouts` view namespace.
Publish them to change the wording or the markup:

```bash
php artisan vendor:publish --tag=table-layouts-translations
php artisan vendor:publish --tag=table-layouts-views
```
