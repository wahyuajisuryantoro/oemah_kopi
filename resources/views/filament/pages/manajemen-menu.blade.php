<x-filament-panels::page>
    <div class="space-y-4">
        <h1 class="text-2xl font-bold">Manajemen Menu</h1>
        <div class="grid grid-cols-2 gap-6">
            <a href="{{ route('filament.resources.menu-kategoris.index') }}" class="block p-6 bg-white border rounded-lg shadow hover:bg-gray-100">
                <h2 class="text-lg font-bold">Kategori Menu</h2>
                <p>Kelola kategori menu restoran.</p>
            </a>
            <a href="{{ route('filament.resources.menus.index') }}" class="block p-6 bg-white border rounded-lg shadow hover:bg-gray-100">
                <h2 class="text-lg font-bold">Menu</h2>
                <p>Kelola menu restoran.</p>
            </a>
        </div>
    </div>
</x-filament-panels::page>
