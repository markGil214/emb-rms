<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="absolute inset-0 z-0">
    <div class="ml-20 transition-all duration-300" x-data="{ 
        sidebarOpen: localStorage.getItem('sidebarOpen') !== 'false',
        searchQuery: '',
        selectedShelf: null,
        shelves: [
            { id: 'A1', name: 'Shelf A-1', capacity: 100, occupied: 85, documents: ['DOC-001', 'DOC-002', 'DOC-003'] },
            { id: 'A2', name: 'Shelf A-2', capacity: 100, occupied: 92, documents: ['DOC-004', 'DOC-005'] },
            { id: 'B1', name: 'Shelf B-1', capacity: 150, occupied: 120, documents: ['DOC-006', 'DOC-007', 'DOC-008'] },
            { id: 'B2', name: 'Shelf B-2', capacity: 150, occupied: 75, documents: ['DOC-009'] },
            { id: 'C1', name: 'Shelf C-1', capacity: 80, occupied: 60, documents: ['DOC-010', 'DOC-011'] },
            { id: 'C2', name: 'Shelf C-2', capacity: 80, occupied: 40, documents: [] }
        ],
        get filteredShelves() {
            if (!this.searchQuery) return this.shelves;
            return this.shelves.filter(shelf => 
                shelf.id.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                shelf.name.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                shelf.documents.some(doc => doc.toLowerCase().includes(this.searchQuery.toLowerCase()))
            );
        },
        selectShelf(shelf) {
            this.selectedShelf = shelf;
        },
        getOccupancyColor(percentage) {
            if (percentage >= 90) return 'bg-red-500';
            if (percentage >= 75) return 'bg-yellow-500';
            return 'bg-green-500';
        },
        getOccupancyText(percentage) {
            if (percentage >= 90) return 'Critical';
            if (percentage >= 75) return 'High';
            return 'Good';
        }
    }" x-init="
        window.addEventListener('storage', (e) => { 
            if (e.key === 'sidebarOpen') {
                this.sidebarOpen = e.newValue !== 'false';
            }
        });
    " :class="sidebarOpen ? 'ml-72' : 'ml-20'">
        
        <!-- Header -->
        <div class="p-4 bg-white border-b border-gray-200">
            <h2 class="text-xl font-bold text-gray-900">Shelf Map & Search</h2>
            <p class="text-gray-600 text-sm">Manage and search through document shelves</p>
        </div>

        <!-- Search -->
        <div class="p-4 bg-gray-50">
            <div class="w-[30%]">
                <div class="relative">
                    <input 
                        type="text" 
                        x-model="searchQuery"
                        placeholder="Search shelf ID, name, or document..."
                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                    <svg class="absolute left-3 top-2.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <div x-show="searchQuery" class="absolute right-3 top-2.5">
                        <button @click="searchQuery = ''" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                <div x-show="searchQuery" class="mt-1 text-xs text-gray-600">
                    <span x-text="filteredShelves.length"></span> shelves found
                </div>
            </div>
        </div>

        <!-- 2D Warehouse Map -->
        <div class="p-4 bg-gray-100">
            <div class="bg-white rounded-lg shadow p-4">
                <div class="mb-3 text-center">
                    <h3 class="text-base font-semibold text-gray-900">Warehouse Layout</h3>
                    <div class="flex items-center justify-center space-x-4 text-xs">
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-green-500 rounded mr-1"></div>
                            <span>Good</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-yellow-500 rounded mr-1"></div>
                            <span>High</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-red-500 rounded mr-1"></div>
                            <span>Critical</span>
                        </div>
                    </div>
                </div>
                
                <!-- 2D Layout -->
                <div class="relative bg-gray-50 rounded p-4" style="min-height: 400px;">
                    <div class="relative w-full h-full">
                        <!-- Aisle Labels -->
                        <div class="absolute top-0 left-8 text-xs font-bold text-gray-600">AISLE A</div>
                        <div class="absolute top-0 left-40 text-xs font-bold text-gray-600">AISLE B</div>
                        <div class="absolute top-0 left-72 text-xs font-bold text-gray-600">AISLE C</div>
                        
                        <!-- Row Labels -->
                        <div class="absolute top-6 left-0 text-xs font-bold text-gray-600">ROW 1</div>
                        <div class="absolute top-24 left-0 text-xs font-bold text-gray-600">ROW 2</div>
                        
                        <!-- Shelves Grid -->
                        <div class="grid grid-cols-3 gap-2 mt-6 ml-8">
                            <!-- All Shelves -->
                            <template x-for="shelf in filteredShelves" :key="shelf.id">
                                <div @click="selectShelf(shelf)" 
                                     class="relative border-2 rounded p-2 cursor-pointer transition-all hover:shadow"
                                     :class="[
                                         selectedShelf?.id === shelf.id ? 'border-blue-500 ring-1 ring-blue-200' : 'border-gray-300',
                                         getOccupancyColor((shelf.occupied / shelf.capacity) * 100)
                                     ]">
                                    <div class="absolute -top-1 -left-1 bg-gray-800 text-white text-xs px-1 rounded">
                                        <span x-text="shelf.id"></span>
                                    </div>
                                    <div class="flex flex-col items-center">
                                        <div class="text-xs font-bold text-white mb-1" x-text="shelf.name"></div>
                                        <div class="grid grid-cols-5 gap-0.5 mb-1">
                                            <template x-for="i in 5" :key="i">
                                                <div class="w-2 h-2 rounded"
                                                     :class="i <= Math.ceil((shelf.occupied / shelf.capacity) * 5) ? 'bg-white' : 'bg-gray-300'"></div>
                                            </template>
                                        </div>
                                        <div class="text-xs text-white text-center">
                                            <span x-text="shelf.occupied"></span>/<span x-text="shelf.capacity"></span>
                                        </div>
                                    </div>
                                </div>
                            </template>
                            
                            <!-- Empty spaces for grid consistency -->
                            <template x-for="i in (6 - filteredShelves.length % 6)" :key="'empty-' + i">
                                <div class="border-2 border-dashed border-gray-300 rounded p-2 opacity-30">
                                    <div class="text-center text-gray-400 text-xs">
                                        <svg class="w-6 h-6 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                        Empty
                                    </div>
                                </div>
                            </template>
                        </div>
                        
                        <!-- Navigation Path -->
                        <div class="absolute top-16 left-32 right-32 h-0.5 bg-blue-200 opacity-50"></div>
                        
                        <!-- Entry/Exit -->
                        <div class="absolute bottom-2 left-1/2 transform -translate-x-1/2">
                            <div class="bg-green-500 text-white text-xs px-2 py-0.5 rounded-full font-bold">
                                ENTRY/EXIT
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Controls -->
                <div class="mt-3 flex justify-between items-center text-sm">
                    <div class="text-gray-600">
                        <span x-text="shelves.length"></span> shelves | 
                        <span x-text="shelves.reduce((sum, s) => sum + s.occupied, 0)"></span> / 
                        <span x-text="shelves.reduce((sum, s) => sum + s.capacity, 0)"></span> occupied
                    </div>
                    <button @click="selectedShelf = null" class="text-blue-600 hover:text-blue-800">
                        Clear
                    </button>
                </div>
            </div>
        </div>

        <!-- Shelf Details Modal -->
        <div x-show="selectedShelf" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="transform opacity-0 scale-95"
             x-transition:enter-end="transform opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-75"
             x-transition:leave-start="transform opacity-100 scale-100"
             x-transition:leave-end="transform opacity-0 scale-95"
             class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
             @click.self="selectedShelf = null">
            <div class="bg-white rounded-lg shadow max-w-lg w-full mx-4 max-h-[80vh] overflow-y-auto">
                <div class="p-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900" x-text="selectedShelf?.name"></h3>
                            <p class="text-gray-500 text-sm" x-text="selectedShelf?.id"></p>
                        </div>
                        <button @click="selectedShelf = null" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <div class="p-4">
                    <!-- Occupancy -->
                    <div class="mb-4">
                        <h4 class="text-base font-medium text-gray-900 mb-3">Occupancy</h4>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="bg-gray-50 p-3 rounded">
                                <div class="text-xl font-bold text-gray-900" x-text="selectedShelf?.occupied"></div>
                                <div class="text-xs text-gray-500">Occupied</div>
                            </div>
                            <div class="bg-gray-50 p-3 rounded">
                                <div class="text-xl font-bold text-gray-900" x-text="selectedShelf?.capacity - selectedShelf?.occupied"></div>
                                <div class="text-xs text-gray-500">Available</div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <div class="flex justify-between text-xs mb-1">
                                <span class="text-gray-600">Occupancy</span>
                                <span class="font-medium" x-text="Math.round((selectedShelf?.occupied / selectedShelf?.capacity) * 100) + '%'"></span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="h-2 rounded-full transition-all duration-300"
                                     :class="getOccupancyColor((selectedShelf?.occupied / selectedShelf?.capacity) * 100)"
                                     :style="`width: ${(selectedShelf?.occupied / selectedShelf?.capacity) * 100}%`"></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Documents -->
                    <div>
                        <h4 class="text-base font-medium text-gray-900 mb-3">Documents</h4>
                        <div x-show="selectedShelf?.documents.length > 0">
                            <div class="space-y-1">
                                <template x-for="document in selectedShelf?.documents" :key="document">
                                    <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            <span class="font-medium text-gray-900 text-sm" x-text="document"></span>
                                        </div>
                                        <button class="text-blue-600 hover:text-blue-800 text-xs">View</button>
                                    </div>
                                </template>
                            </div>
                        </div>
                        <div x-show="selectedShelf?.documents.length === 0" class="text-center py-4 text-gray-500 text-sm">
                            No documents
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
