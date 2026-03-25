<x-filament-panels::page>
	<style>
		.fi-ta {
			padding-top: 10px;
			padding-bottom: 10px;
		}
    </style>
    <div class="space-y-6">
        <form wire:submit="loadData">
            {{ $this->form }}
        </form>

        <div class="fi-ta">
            {{ $this->table }}
        </div>
    </div>
</x-filament-panels::page>
