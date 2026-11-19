<?php

namespace Webwizardsusa\TableLayouts\Enums;

use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

/**
 * How a table shows its records: as rows of columns (Table) or as a grid of cards (Card).
 */
enum TableLayout: string implements HasIcon, HasLabel
{
    case Table = 'table';
    case Card = 'card';

    public function getLabel(): string
    {
        return __("table-layouts::table-layouts.layouts.{$this->value}");
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::Table => Heroicon::OutlinedTableCells,
            self::Card => Heroicon::OutlinedSquares2x2,
        };
    }
}
