<?php

namespace Webwizardsusa\TableLayouts;

use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentView;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\View\TablesRenderHook;
use Illuminate\Contracts\View\View;
use Livewire\Livewire;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Webwizardsusa\TableLayouts\Enums\TableLayout;
use Webwizardsusa\TableLayouts\Livewire\SupportTableLayouts;

/**
 * Registers the package's views and translations under the `table-layouts` namespace, its stylesheet, the Table
 * macros, the Livewire hook that applies and switches layouts, and the switcher at the end of the table toolbar.
 */
class TableLayoutsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('table-layouts')
            ->hasTranslations()
            ->hasViews();
    }

    public function packageRegistered(): void
    {
        $this->app->booting(static function (): void {
            Livewire::componentHook(SupportTableLayouts::class);
        });
    }

    public function packageBooted(): void
    {
        $this->registerTableMacros();

        FilamentAsset::register([
            Css::make('table-layouts', __DIR__.'/../resources/css/table-layouts.css'),
        ], 'webwizardsusa/filament-table-layouts');

        FilamentView::registerRenderHook(
            TablesRenderHook::TOOLBAR_END,
            static function (): ?View {
                $livewire = Livewire::current();

                if (! $livewire instanceof HasTable) {
                    return null;
                }

                $layouts = TableLayouts::find($livewire->getTable());

                if (! $layouts?->isSwitchable()) {
                    return null;
                }

                return view('table-layouts::switcher', [
                    'activeLayout' => $layouts->getAppliedLayout() ?? $layouts->getActiveLayout(),
                    'layouts' => $layouts->getAvailableLayouts(),
                ]);
            },
        );
    }

    protected function registerTableMacros(): void
    {
        /**
         * The card each record is shown as in the card view. Defining one offers both views, starting in the table,
         * unless `availableLayouts()` says otherwise.
         */
        Table::macro('card', function (?Card $card): Table {
            /** @var Table $this */
            TableLayouts::of($this)->card($card);

            return $this;
        });

        /**
         * The layouts people can switch between: `TableLayout::Card` alone shows only cards.
         *
         * @param  TableLayout | array<TableLayout>  $layouts
         */
        Table::macro('availableLayouts', function (TableLayout|array $layouts): Table {
            /** @var Table $this */
            TableLayouts::of($this)->availableLayouts($layouts);

            return $this;
        });

        /**
         * The layout shown until someone picks another one; the table view, if it is available, otherwise the card.
         */
        Table::macro('defaultLayout', function (TableLayout $layout): Table {
            /** @var Table $this */
            TableLayouts::of($this)->defaultLayout($layout);

            return $this;
        });
    }
}
