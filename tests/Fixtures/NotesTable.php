<?php

namespace Webwizardsusa\TableLayouts\Tests\Fixtures;

use Closure;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\DeleteBulkAction;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * A table of notes with a searchable, sortable `title` column and a bulk action, set up further by `$configureUsing`
 * for each test.
 */
class NotesTable extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    public static ?Closure $configureUsing = null;

    public function table(Table $table): Table
    {
        $table
            ->query(Note::query())
            ->columns([
                TextColumn::make('title')->searchable()->sortable(),
                TextColumn::make('created_at')->toggleable(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);

        return static::$configureUsing ? (static::$configureUsing)($table) : $table;
    }

    public function render(): View
    {
        return view('table-layouts-tests::notes-table');
    }
}
