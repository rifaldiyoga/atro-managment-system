<x-filament::page>
    <x-filament::tabs
        :tabs="[
            'salesmen' => 'Salesmen',
            'salesman-groups' => 'Salesman Groups',
        ]"
    />

    <div x-show="$store.filamentTabs.activeTab === 'salesmen'" x-cloak>
        @livewire(\App\Livewire\SalesmanTableComponent::class)
    </div>

    <div x-show="$store.filamentTabs.activeTab === 'salesman-groups'" x-cloak>
        @livewire(\App\Livewire\SalesmanGroupTableComponent::class)
    </div>
</x-filament::page>
