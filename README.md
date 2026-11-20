# Filament Table Layouts

> **Under active development.** This package is still changing and may not be ready for production use yet. Until
> 1.0, a minor release can include breaking changes, so check the changelog before you upgrade.

![Table Layouts: a Filament table shown as rows and as a grid of cards with images](https://raw.githubusercontent.com/webwizardsusa/filament-table-layouts/main/art/table-layouts-banner.webp)

[![Latest version on Packagist](https://img.shields.io/packagist/v/webwizardsusa/filament-table-layouts.svg)](https://packagist.org/packages/webwizardsusa/filament-table-layouts)
[![Tests](https://github.com/webwizardsusa/filament-table-layouts/actions/workflows/tests.yml/badge.svg?branch=main)](https://github.com/webwizardsusa/filament-table-layouts/actions/workflows/tests.yml)
[![Total downloads](https://img.shields.io/packagist/dt/webwizardsusa/filament-table-layouts.svg)](https://packagist.org/packages/webwizardsusa/filament-table-layouts/stats)
[![PHP version](https://img.shields.io/packagist/dependency-v/webwizardsusa/filament-table-layouts/php.svg)](https://packagist.org/packages/webwizardsusa/filament-table-layouts)
[![Filament version](https://img.shields.io/packagist/dependency-v/webwizardsusa/filament-table-layouts/filament/filament.svg?label=Filament)](https://filamentphp.com)
[![License](https://img.shields.io/packagist/l/webwizardsusa/filament-table-layouts.svg)](https://github.com/webwizardsusa/filament-table-layouts/blob/main/LICENSE.md)

Let people switch a Filament table between rows and a grid of cards, with a switcher in the table's toolbar. Each card
has an image across the top, the details below it, then the record actions, with the bulk select checkbox over the top
left corner. The layout someone picks is remembered in their session, for each table.

<p align="center">
  <img src="https://raw.githubusercontent.com/webwizardsusa/filament-table-layouts/main/art/table-layouts-cards.webp" alt="Products as cards, each with its image across the top and two of them selected" width="32%">
  <img src="https://raw.githubusercontent.com/webwizardsusa/filament-table-layouts/main/art/table-layouts-text-cards.webp" alt="Tasks as cards without images, with status and priority badges" width="32%">
  <img src="https://raw.githubusercontent.com/webwizardsusa/filament-table-layouts/main/art/table-layouts-table.webp" alt="The same products table shown as rows" width="32%">
</p>

Requires PHP 8.3+ and Filament 5.

```bash
composer require webwizardsusa/filament-table-layouts
```

```php
use Webwizardsusa\TableLayouts\Card;

public static function table(Table $table): Table
{
    return $table
        ->columns([
            ImageColumn::make('cover'),
            TextColumn::make('title')->searchable()->sortable(),
            TextColumn::make('author.name'),
        ])
        ->card(
            Card::make([
                TextColumn::make('title')->weight(FontWeight::Bold),
                TextColumn::make('author.name')->color('gray'),
            ])->image('cover'),
        );
}
```

Defining a card offers both layouts, starting in the table. To start in cards, or offer only cards:

```php
use Webwizardsusa\TableLayouts\Enums\TableLayout;

$table->card($card)->defaultLayout(TableLayout::Card);
$table->card($card)->availableLayouts(TableLayout::Card);
```

The full documentation is in [docs/](docs/introduction.md).

## Testing

```bash
composer test
```

## About Us

This package is developed and maintained by [WebWizardsUSA](https://webwizardsusa.com/), a top rated web development agency on Upwork.

## License

The MIT License (MIT). See [LICENSE.md](LICENSE.md).
