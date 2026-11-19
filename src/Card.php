<?php

namespace Webwizardsusa\TableLayouts;

use Closure;
use Filament\Support\Components\Contracts\HasEmbeddedView;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Component;

/**
 * How each record looks in a table's card view: an optional image across the top of the card, then the details, with
 * the record actions below and the bulk select checkbox over the top left corner. The details are columns and column
 * layouts (Stack, Split, Grid…), as in any Filament content layout.
 *
 * ```php
 * Card::make([
 *     TextColumn::make('title')->weight(FontWeight::Bold),
 *     TextColumn::make('author.name'),
 * ])
 *     ->image('cover')
 *     ->grid(['md' => 2, 'xl' => 4])
 * ```
 */
class Card extends Component implements HasEmbeddedView
{
    protected ?Column $image = null;

    protected string|Closure|null $imageHeight = '12rem';

    /**
     * @var array<string, int | null> | Closure
     */
    protected array|Closure $grid = ['default' => 1, 'md' => 2, 'xl' => 3];

    /**
     * @param  array<Column | Component> | Closure  $schema
     */
    final public function __construct(array|Closure $schema = [])
    {
        $this->schema($schema);
    }

    /**
     * @param  array<Column | Component> | Closure  $schema
     */
    public static function make(array|Closure $schema = []): static
    {
        $static = app(static::class, ['schema' => $schema]);
        $static->configure();

        return $static;
    }

    /**
     * The image across the top of the card: the name of an attribute holding the image's path or URL, which becomes an
     * ImageColumn, or a column of your own (an ImageColumn with a disk, say, or a ViewColumn).
     */
    public function image(string|Column|null $image): static
    {
        $this->image = is_string($image) ? ImageColumn::make($image) : $image;

        return $this;
    }

    /**
     * The height of the image area, as a CSS length. The image covers it, cropped to fit.
     */
    public function imageHeight(string|Closure|null $height): static
    {
        $this->imageHeight = $height;

        return $this;
    }

    /**
     * The number of cards across, per breakpoint, as with a table's `contentGrid()`; an int is that many from the
     * `md` breakpoint up and one below it.
     *
     * @param  int | array<string, int | null> | Closure  $grid
     */
    public function grid(int|array|Closure $grid): static
    {
        $this->grid = is_int($grid) ? ['default' => 1, 'md' => $grid] : $grid;

        return $this;
    }

    public function getImage(): ?Column
    {
        return $this->image?->layout($this);
    }

    public function getImageHeight(): ?string
    {
        return $this->evaluate($this->imageHeight);
    }

    /**
     * @return array<string, int | null>
     */
    public function getGrid(): array
    {
        return $this->evaluate($this->grid);
    }

    /**
     * @return array<string, Column>
     */
    public function getColumns(): array
    {
        $image = $this->getImage();

        return [
            ...($image ? [$image->getName() => $image] : []),
            ...parent::getColumns(),
        ];
    }

    public function toEmbeddedHtml(): string
    {
        $record = $this->getRecord();
        $recordKey = $this->getRecordKey();
        $rowLoop = $this->getRowLoop();

        $image = $this->getImage();
        $imageHeight = $this->getImageHeight();

        $attributes = $this->getExtraAttributeBag()->class(['ftl-card']);

        ob_start(); ?>

        <div <?= $attributes->toHtml() ?>>
            <?php if ($image) { ?>
                <div class="ftl-card-image"<?= filled($imageHeight) ? ' style="height: '.e($imageHeight).'"' : '' ?>>
                    <?= $image->record($record)->recordKey($recordKey)->rowLoop($rowLoop)->renderInLayout() ?>
                </div>
            <?php } ?>

            <div class="ftl-card-details">
                <?php foreach ($this->getComponents() as $component) { ?>
                    <?= $component->record($record)->recordKey($recordKey)->rowLoop($rowLoop)->renderInLayout() ?>
                <?php } ?>
            </div>
        </div>

        <?php return ob_get_clean();
    }
}
