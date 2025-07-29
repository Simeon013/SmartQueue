@props(['name' => 'icon', 'value' => 'fas fa-cog', 'id' => 'icon'])

<div x-data="{
    showPicker: false,
    search: '',
    selectedIcon: '{{ $value }}',
    icons: [
        'fas fa-user', 'fas fa-users', 'fas fa-user-tie', 'fas fa-user-md', 'fas fa-user-nurse',
        'fas fa-file-invoice', 'fas fa-file-alt', 'fas fa-file-invoice-dollar', 'fas fa-receipt',
        'fas fa-calculator', 'fas fa-percent', 'fas fa-euro-sign', 'fas fa-money-bill-wave',
        'fas fa-credit-card', 'fas fa-credit-card', 'fas fa-money-check', 'fas fa-wallet',
        'fas fa-question-circle', 'fas fa-info-circle', 'fas fa-exclamation-circle',
        'fas fa-headset', 'fas fa-phone-alt', 'fas fa-phone-volume', 'fas fa-comments',
        'fas fa-envelope', 'fas fa-inbox', 'fas fa-paper-plane', 'fas fa-print',
        'fas fa-folder', 'fas fa-folder-open', 'fas fa-archive', 'fas fa-box',
        'fas fa-tools', 'fas fa-cog', 'fas fa-cogs', 'fas fa-wrench', 'fas fa-screwdriver',
        'fas fa-clipboard', 'fas fa-clipboard-check', 'fas fa-clipboard-list',
        'fas fa-calendar', 'fas fa-calendar-alt', 'fas fa-calendar-check',
        'fas fa-clock', 'fas fa-hourglass', 'fas fa-stopwatch',
        'fas fa-home', 'fas fa-building', 'fas fa-warehouse', 'fas fa-store',
        'fas fa-map-marker-alt', 'fas fa-location-arrow', 'fas fa-directions',
        'fas fa-truck', 'fas fa-shipping-fast', 'fas fa-boxes',
        'fas fa-bell', 'fas fa-bell-slash', 'fas fa-exclamation', 'fas fa-exclamation-triangle',
        'fas fa-check', 'fas fa-check-circle', 'fas fa-check-square',
        'fas fa-plus', 'fas fa-plus-circle', 'fas fa-plus-square',
        'fas fa-minus', 'fas fa-minus-circle', 'fas fa-minus-square'
    ],
    get filteredIcons() {
        if (!this.search) return this.icons;
        const searchTerm = this.search.toLowerCase();
        return this.icons.filter(icon => 
            icon.toLowerCase().includes(searchTerm)
        );
    },
    init() {
        this.$nextTick(() => {
            // S'assurer que la liste est initialisée avec toutes les icônes au chargement
            this.$refs.iconInput.dispatchEvent(new Event('input'));
        });
    },
    selectIcon(icon) {
        this.selectedIcon = icon;
        this.showPicker = false;
        this.$refs.iconInput.value = icon;
        this.$refs.iconPreview.className = icon;
    }
}">
    <div class="relative">
        <label class="block text-sm font-medium text-gray-700 mb-1">Icône</label>
        <div class="mt-1 flex rounded-md shadow-sm">
            <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">
                <i x-ref="iconPreview" :class="selectedIcon"></i>
            </span>
            <input type="text" 
                   x-ref="iconInput"
                   name="{{ $name }}" 
                   id="{{ $id }}" 
                   x-model="selectedIcon"
                   @click="showPicker = !showPicker"
                   class="focus:ring-blue-500 focus:border-blue-500 flex-1 block w-full rounded-none rounded-r-md sm:text-sm border-gray-300"
                   placeholder="Sélectionner une icône"
                   readonly>
        </div>
        
        <!-- Picker Dropdown -->
        <div x-show="showPicker" 
             @click.away="showPicker = false"
             class="absolute z-10 mt-1 w-full rounded-md bg-white shadow-lg"
             x-cloak>
            <div class="p-2 border-b">
                <input type="text" 
                       x-model="search"
                       @click.stop
                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                       placeholder="Rechercher une icône...">
            </div>
            <div class="max-h-60 overflow-y-auto p-2 grid grid-cols-6 gap-2">
                <template x-for="icon in filteredIcons" :key="icon">
                    <button type="button"
                            @click="selectIcon(icon)"
                            class="p-2 rounded-md hover:bg-gray-100 flex flex-col items-center justify-center"
                            :class="{ 'bg-blue-50 border border-blue-200': selectedIcon === icon }">
                        <i :class="icon + ' text-lg mb-1'"></i>
                        <span class="text-xs text-gray-500 truncate w-full text-center" x-text="icon.split('fa-')[1]"></span>
                    </button>
                </template>
                <div x-show="filteredIcons.length === 0" class="col-span-6 text-center py-4 text-sm text-gray-500">
                    Aucune icône trouvée
                </div>
            </div>
            <div class="p-2 border-t text-xs text-gray-500 text-center">
                <a href="https://fontawesome.com/icons" target="_blank" class="text-blue-600 hover:text-blue-800">
                    Plus d'icônes sur Font Awesome
                </a>
            </div>
        </div>
    </div>
    
    <style>
        [x-cloak] { display: none !important; }
    </style>
</div>
