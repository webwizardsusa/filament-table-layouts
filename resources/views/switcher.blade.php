@php
    use Webwizardsusa\TableLayouts\Livewire\SupportTableLayouts;
@endphp

{{-- The table toolbar's layout switcher: one button per layout the table offers, the active one pressed. --}}
<div
    role="group"
    aria-label="{{ __('table-layouts::table-layouts.switcher.label') }}"
    class="fi-btn-group ftl-switcher"
>
    @foreach ($layouts as $layout)
        @php
            $isActive = $layout === $activeLayout;
            $label = __('table-layouts::table-layouts.switcher.show', ['layout' => $layout->getLabel()]);
        @endphp

        <x-filament::button
            :color="$isActive ? 'primary' : 'gray'"
            :icon="$layout->getIcon()"
            :tooltip="$label"
            label-sr-only
            :aria-pressed="$isActive ? 'true' : 'false'"
            wire:click="{{ SupportTableLayouts::SWITCH_METHOD }}('{{ $layout->value }}')"
            wire:loading.attr="disabled"
            :data-table-layout="$layout->value"
        >
            {{ $label }}
        </x-filament::button>
    @endforeach
</div>
