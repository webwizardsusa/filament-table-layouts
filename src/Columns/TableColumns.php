<?php

namespace Webwizardsusa\TableLayouts\Columns;

use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Tables\Columns\Layout\Component;
use Illuminate\Support\HtmlString;

/**
 * Keeps a table's own columns on the table while its cards are shown, without rendering them, so searching, sorting
 * and eager loading carry on working the same in both layouts.
 */
class TableColumns extends Component
{
    /**
     * @param  array<Column | Component | ColumnGroup>  $tableComponents
     */
    final public function __construct(protected array $tableComponents) {}

    /**
     * @param  array<Column | Component | ColumnGroup>  $components
     */
    public static function make(array $components): static
    {
        return app(static::class, ['tableComponents' => $components]);
    }

    /**
     * The columns keep the table and layout they were given when the table view set them, so switching back to it
     * finds them as they were.
     *
     * @return array<string, Column>
     */
    public function getColumns(): array
    {
        $columns = [];

        foreach ($this->tableComponents as $component) {
            if ($component instanceof Column) {
                $columns[$component->getName()] = $component;

                continue;
            }

            $columns = [...$columns, ...$component->getColumns()];
        }

        return $columns;
    }

    /**
     * @return array<Column | Component | ColumnGroup>
     */
    public function getTableComponents(): array
    {
        return $this->tableComponents;
    }

    public function renderInLayout(): ?HtmlString
    {
        return null;
    }
}
