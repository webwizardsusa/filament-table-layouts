<?php

namespace Webwizardsusa\TableLayouts;

use Closure;
use Filament\Actions\Action;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use LogicException;
use WeakMap;
use Webwizardsusa\TableLayouts\Columns\TableColumns;
use Webwizardsusa\TableLayouts\Enums\TableLayout;

/**
 * The layouts one table offers, which of them it starts in, and the one it shows. The Table macros (`card()`,
 * `availableLayouts()`, `defaultLayout()`) fill it in while the table is defined; once the Livewire component has
 * built its table, `apply()` shows the layout remembered in the session for that table, and `switchTo()` changes it.
 *
 * The table's own columns are what `columns()` set. In the card view they stay on the table, unrendered, beside the
 * Card, so searching and sorting work the same in both views.
 */
class TableLayouts
{
    /**
     * @var WeakMap<Table, self> | null
     */
    protected static ?WeakMap $configurations = null;

    protected ?Card $card = null;

    /**
     * @var array<TableLayout> | null
     */
    protected ?array $availableLayouts = null;

    protected ?TableLayout $defaultLayout = null;

    protected ?TableLayout $appliedLayout = null;

    /**
     * What the table view had before the cards replaced it, to put back when switching to it.
     *
     * @var array{components: array<mixed>, contentGrid: mixed, columnManager: bool | Closure | null} | null
     */
    protected ?array $tableView = null;

    final public function __construct(protected Table $table) {}

    public static function of(Table $table): static
    {
        static::$configurations ??= new WeakMap;

        return static::$configurations[$table] ??= new static($table);
    }

    public static function find(Table $table): ?static
    {
        return static::$configurations[$table] ?? null;
    }

    public function card(?Card $card): static
    {
        $this->card = $card;

        return $this;
    }

    /**
     * @param  TableLayout | array<TableLayout>  $layouts
     */
    public function availableLayouts(TableLayout|array $layouts): static
    {
        $this->availableLayouts = array_values(array_unique(array_map(
            fn (TableLayout|string $layout): TableLayout => $layout instanceof TableLayout ? $layout : TableLayout::from($layout),
            is_array($layouts) ? $layouts : [$layouts],
        ), SORT_REGULAR));

        return $this;
    }

    public function defaultLayout(TableLayout $layout): static
    {
        $this->defaultLayout = $layout;

        return $this;
    }

    public function getCard(): ?Card
    {
        return $this->card;
    }

    /**
     * The layouts people can pick from, in the order the switcher shows them: the ones given to
     * `availableLayouts()`, otherwise both when there is a card and only the table when there isn't.
     *
     * @return array<TableLayout>
     */
    public function getAvailableLayouts(): array
    {
        $layouts = $this->availableLayouts ?? ($this->card ? [TableLayout::Table, TableLayout::Card] : [TableLayout::Table]);

        if (in_array(TableLayout::Card, $layouts, true) && (! $this->card)) {
            throw new LogicException('The table offers the card layout but has no card: define one with `->card(Card::make([...]))`.');
        }

        return $layouts;
    }

    public function isAvailable(TableLayout $layout): bool
    {
        return in_array($layout, $this->getAvailableLayouts(), true);
    }

    public function isSwitchable(): bool
    {
        return count($this->getAvailableLayouts()) > 1;
    }

    public function getDefaultLayout(): TableLayout
    {
        if ($this->defaultLayout && $this->isAvailable($this->defaultLayout)) {
            return $this->defaultLayout;
        }

        return $this->isAvailable(TableLayout::Table) ? TableLayout::Table : $this->getAvailableLayouts()[0];
    }

    /**
     * The layout this table shows: the one last picked for it in this session, if it still offers it, otherwise its
     * default.
     */
    public function getActiveLayout(): TableLayout
    {
        $remembered = TableLayout::tryFrom((string) session()->get($this->getSessionKey()));

        return ($remembered && $this->isAvailable($remembered)) ? $remembered : $this->getDefaultLayout();
    }

    public function getAppliedLayout(): ?TableLayout
    {
        return $this->appliedLayout;
    }

    /**
     * Shows the given layout and remembers it for this table for the rest of the session.
     */
    public function switchTo(TableLayout $layout): void
    {
        if (! $this->isAvailable($layout)) {
            return;
        }

        session()->put($this->getSessionKey(), $layout->value);

        $this->apply();

        $livewire = $this->table->getLivewire();

        if (method_exists($livewire, 'flushCachedTableRecords')) {
            $livewire->flushCachedTableRecords();
        }
    }

    /**
     * Sets the table up for its active layout. It is safe to call again: nothing changes unless the layout has.
     */
    public function apply(): void
    {
        $layout = $this->getActiveLayout();

        if ($layout === $this->appliedLayout) {
            return;
        }

        if ($this->appliedLayout === null) {
            $this->table->extraAttributes(fn (): array => [
                'class' => ($this->appliedLayout === TableLayout::Card) ? 'ftl-cards' : null,
            ], merge: true);

            // Filament hides the toolbar of a table with nothing in it but bulk actions, and the switcher with it.
            // A toolbar action that renders nothing keeps it shown.
            if ($this->isSwitchable()) {
                $this->table->pushToolbarActions([
                    Action::make('tableLayoutSwitcher')->view('table-layouts::toolbar-placeholder'),
                ]);
            }
        }

        match ($layout) {
            TableLayout::Card => $this->showCards(),
            TableLayout::Table => $this->showTable(),
        };

        $this->appliedLayout = $layout;
    }

    protected function showCards(): void
    {
        $table = $this->table;

        $components = array_values(array_filter([
            ...$table->getColumnsLayout(),
            $table->getCollapsibleColumnsLayout(),
        ]));

        $this->tableView = [
            'components' => $components,
            'contentGrid' => (fn (): mixed => $this->contentGrid)->call($table),
            'columnManager' => (fn (): bool|Closure|null => $this->hasColumnManager)->call($table),
        ];

        // The table's columns go last so that, where the card has a column of the same name, the table's one is
        // the one searched and sorted by.
        $table
            ->columns([
                $this->card,
                ...($components ? [TableColumns::make($components)] : []),
            ])
            ->contentGrid(fn (): array => $this->card->getGrid())
            ->columnManager(false);
    }

    protected function showTable(): void
    {
        if (! $this->tableView) {
            return;
        }

        $this->table
            ->columns($this->tableView['components'])
            ->contentGrid($this->tableView['contentGrid'])
            ->columnManager($this->tableView['columnManager']);

        $this->tableView = null;
    }

    protected function getSessionKey(): string
    {
        /** @var HasTable $livewire */
        $livewire = $this->table->getLivewire();

        return $livewire->getTableSessionKey('layout');
    }
}
