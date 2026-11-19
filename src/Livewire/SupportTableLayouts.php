<?php

namespace Webwizardsusa\TableLayouts\Livewire;

use Closure;
use Filament\Tables\Contracts\HasTable;
use Livewire\ComponentHook;
use ReflectionProperty;
use Webwizardsusa\TableLayouts\Enums\TableLayout;
use Webwizardsusa\TableLayouts\TableLayouts;

/**
 * Shows each table component's active layout once the component has built its table, and answers the layout
 * switcher's `__switchTableLayout` call, the way Livewire answers `__dispatch`, so a page needs no trait to switch.
 *
 * Livewire's own hooks may run after this one at mount and hydration, before the table exists, so the layout is
 * applied before each property update and method call instead, and again before rendering, which also covers a table
 * rebuilt during the request by `resetTable()`.
 */
class SupportTableLayouts extends ComponentHook
{
    public const SWITCH_METHOD = '__switchTableLayout';

    public function skip(): bool
    {
        return ! $this->component instanceof HasTable;
    }

    public function update(): void
    {
        $this->applyLayout();
    }

    public function call(string $method, array $params, Closure $returnEarly): void
    {
        $this->applyLayout();

        if ($method !== self::SWITCH_METHOD) {
            return;
        }

        $returnEarly();

        $layout = TableLayout::tryFrom((string) ($params[0] ?? ''));

        if ($layout) {
            $this->getTableLayouts()?->switchTo($layout);
        }
    }

    public function render(): void
    {
        $this->applyLayout();
    }

    protected function applyLayout(): void
    {
        $this->getTableLayouts()?->apply();
    }

    protected function getTableLayouts(): ?TableLayouts
    {
        /** @var HasTable $component */
        $component = $this->component;

        if (! (new ReflectionProperty($component, 'table'))->isInitialized($component)) {
            return null;
        }

        return TableLayouts::find($component->getTable());
    }
}
