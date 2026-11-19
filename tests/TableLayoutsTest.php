<?php

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Livewire\Livewire;
use Webwizardsusa\TableLayouts\Card;
use Webwizardsusa\TableLayouts\Enums\TableLayout;
use Webwizardsusa\TableLayouts\Livewire\SupportTableLayouts;
use Webwizardsusa\TableLayouts\Tests\Fixtures\Note;
use Webwizardsusa\TableLayouts\Tests\Fixtures\NotesTable;
use Webwizardsusa\TableLayouts\Tests\TestCase;

uses(TestCase::class);

function withCard(?Closure $configure = null): void
{
    NotesTable::$configureUsing = function (Table $table) use ($configure): Table {
        $table->card(Card::make([TextColumn::make('title')->prefix('Card: ')])->image('cover'));

        return $configure ? $configure($table) : $table;
    };
}

beforeEach(function (): void {
    Note::query()->create(['title' => 'First', 'cover' => 'https://example.com/first.jpg']);
    Note::query()->create(['title' => 'Second']);
});

it('shows a table without a card as a table, with no switcher', function (): void {
    Livewire::test(NotesTable::class)
        ->assertSee('First')
        ->assertDontSee('ftl-switcher')
        ->assertDontSee('ftl-card');
});

it('starts a table with a card in the table view, with a switcher for both layouts', function (): void {
    withCard();

    Livewire::test(NotesTable::class)
        ->assertSee('ftl-switcher')
        ->assertSeeHtml('data-table-layout="table"')
        ->assertSeeHtml('data-table-layout="card"')
        ->assertDontSee('Card: First')
        ->assertDontSee('ftl-cards');
});

it('switches to cards, with the image above the details', function (): void {
    withCard();

    Livewire::test(NotesTable::class)
        ->call(SupportTableLayouts::SWITCH_METHOD, 'card')
        ->assertSee('ftl-cards')
        ->assertSeeInOrder(['ftl-card-image', 'first.jpg', 'ftl-card-details', 'Card: First'])
        ->assertSee('Card: Second');
});

it('puts a bulk select checkbox on each card', function (): void {
    withCard();

    Livewire::test(NotesTable::class)
        ->call(SupportTableLayouts::SWITCH_METHOD, 'card')
        ->assertSeeInOrder(['fi-ta-record-checkbox', 'ftl-card-image', 'Card: First', 'fi-ta-record-checkbox', 'Card: Second']);
});

it('switches back to the table view', function (): void {
    withCard();

    Livewire::test(NotesTable::class)
        ->call(SupportTableLayouts::SWITCH_METHOD, 'card')
        ->call(SupportTableLayouts::SWITCH_METHOD, 'table')
        ->assertDontSee('Card: First')
        ->assertDontSee('ftl-cards')
        ->assertSee('First');
});

it('remembers the picked layout in the session for the next visit', function (): void {
    withCard();

    Livewire::test(NotesTable::class)->call(SupportTableLayouts::SWITCH_METHOD, 'card');

    Livewire::test(NotesTable::class)->assertSee('Card: First');
});

it('remembers the layout for each table separately', function (): void {
    withCard(fn (Table $table): Table => $table->identifier('notes'));

    Livewire::test(NotesTable::class)->call(SupportTableLayouts::SWITCH_METHOD, 'card');

    withCard(fn (Table $table): Table => $table->identifier('archived-notes'));

    Livewire::test(NotesTable::class)->assertDontSee('Card: First');
});

it('starts in the default layout', function (): void {
    withCard(fn (Table $table): Table => $table->defaultLayout(TableLayout::Card));

    Livewire::test(NotesTable::class)->assertSee('Card: First');
});

it('shows only cards, with no switcher, when cards are the only layout', function (): void {
    withCard(fn (Table $table): Table => $table->availableLayouts(TableLayout::Card));

    Livewire::test(NotesTable::class)
        ->assertSee('Card: First')
        ->assertDontSee('ftl-switcher')
        ->call(SupportTableLayouts::SWITCH_METHOD, 'table')
        ->assertSee('Card: First');
});

it('ignores a remembered layout the table no longer offers', function (): void {
    withCard();

    Livewire::test(NotesTable::class)->call(SupportTableLayouts::SWITCH_METHOD, 'card');

    withCard(fn (Table $table): Table => $table->availableLayouts(TableLayout::Table));

    Livewire::test(NotesTable::class)->assertDontSee('Card: First');
});

it('searches and sorts the cards by the table columns', function (): void {
    withCard();

    Livewire::test(NotesTable::class)
        ->call(SupportTableLayouts::SWITCH_METHOD, 'card')
        ->searchTable('Second')
        ->assertSee('Card: Second')
        ->assertDontSee('Card: First')
        ->searchTable('')
        ->assertSee('Card: First')
        ->sortTable('title', 'desc')
        ->assertSeeInOrder(['Card: Second', 'Card: First']);
});

it('refuses the card layout when the table has no card', function (): void {
    NotesTable::$configureUsing = fn (Table $table): Table => $table->availableLayouts([TableLayout::Table, TableLayout::Card]);

    Livewire::test(NotesTable::class);
})->throws(Exception::class, 'has no card');
