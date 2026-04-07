<x-filament-panels::page>
    {{ $this->form }}

    <x-filament::tabs label="نوع المواد">
        <x-filament::tabs.item
            :active="$this->activeTab === 'raw'"
            wire:click="$set('activeTab', 'raw')"
        >
            مواد خام
        </x-filament::tabs.item>

        <x-filament::tabs.item
            :active="$this->activeTab === 'manufactured'"
            wire:click="$set('activeTab', 'manufactured')"
        >
            مواد مصنعة
        </x-filament::tabs.item>
    </x-filament::tabs>

    <x-filament::section :heading="$this->activeTab === 'raw' ? 'عناصر إخراج المواد الخام' : 'عناصر إخراج المواد المصنعة'">
        {{ $this->table }}
    </x-filament::section>
</x-filament-panels::page>
