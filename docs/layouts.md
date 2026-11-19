---
title: Layouts
weight: 30
description: Choose which layouts a table offers, which one it starts in, and how they are remembered.
---

A table's layouts are set with three methods on the table:

| Method | What it does |
|---|---|
| `card(Card $card)` | The card each record is shown as. Defining one offers both layouts. |
| `availableLayouts(TableLayout \| array $layouts)` | The layouts to offer, in the switcher's order. |
| `defaultLayout(TableLayout $layout)` | The layout shown until someone picks another. |

```php
use Webwizardsusa\TableLayouts\Enums\TableLayout;

// Table (default) and cards, starting in the table
$table->columns([...])->card($card);

// Table and cards, starting in cards
$table->columns([...])->card($card)->defaultLayout(TableLayout::Card);

// Cards only, with no switcher
$table->card($card)->availableLayouts(TableLayout::Card);

// Table only: don't define a card, or keep it but offer only the table
$table->columns([...])->card($card)->availableLayouts(TableLayout::Table);
```

The methods can be called in any order. Offering `TableLayout::Card` without a card throws a `LogicException`.

## The switcher

When a table offers more than one layout, the switcher sits at the end of its toolbar, after the search field,
filters and column manager. The toolbar is kept shown for it even when the table has nothing else there.

## Remembering the layout

The layout someone picks is stored in their session under the table's session key, the same one Filament uses for
persisted filters and sorting. It is kept per Livewire component and, when the table has one, per
`identifier()`, so two tables on the same page are remembered separately. A remembered layout the table no longer
offers is ignored and the default is shown.

## Searching and sorting

In the card view the table's own columns stay on the table, unrendered, so the search field, the sort dropdown and
eager loading work exactly as in the table view. Make columns searchable and sortable in `columns()`; the card's
columns are only for display. Where a card column has the same name as a table column, the table's one is used for
searching and sorting.

The column manager is hidden in the card view, as the columns it would toggle aren't shown there.
