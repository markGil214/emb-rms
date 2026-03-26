<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="absolute inset-0 z-0">
    <div class="ml-20 transition-all duration-300" x-data="{ 
        sidebarOpen: localStorage.getItem('sidebarOpen') !== 'false',
        searchQuery: '',
        selectedShelf: null,
        clickX: 0,
        clickY: 0,
        hoverX: 0,
        hoverY: 0,
        showCoordinates: false,
        showHoverCoordinates: true,
        clickedArea: null,
        areas: [
            // Yellow Shelves (Bottom Row)
            { 
                id: 'shelf1-yellow', 
                name: 'Shelf 1 (Yellow)', 
                description: 'Yellow section - Contains 85/100 documents - High occupancy',
                bounds: { x1: 45, y1: 75, x2: 49, y2: 79 }, // Exactly at X=47, Y=77
                data: { capacity: 100, occupied: 85, documents: ['DOC-001', 'DOC-002', 'DOC-003'], status: 'High', color: 'yellow' }
            },
            { 
                id: 'shelf2-yellow', 
                name: 'Shelf 2 (Yellow)', 
                description: 'Yellow section - Contains 92/100 documents - Critical occupancy',
                bounds: { x1: 53, y1: 75, x2: 57, y2: 79 }, // Exactly at X=55, Y=77
                data: { capacity: 100, occupied: 92, documents: ['DOC-004', 'DOC-005'], status: 'Critical', color: 'yellow' }
            },
            { 
                id: 'shelf3-yellow', 
                name: 'Shelf 3 (Yellow)', 
                description: 'Yellow section - Contains 120/150 documents - High occupancy',
                bounds: { x1: 61, y1: 74, x2: 65, y2: 78 }, // Exactly at X=63, Y=76
                data: { capacity: 150, occupied: 120, documents: ['DOC-006', 'DOC-007', 'DOC-008'], status: 'High', color: 'yellow' }
            },
            
            // Blue Shelves (Top Row)
            { 
                id: 'shelf1-blue', 
                name: 'Shelf 1 (Blue)', 
                description: 'Blue section - Contains 75/150 documents - Good occupancy',
                bounds: { x1: 51, y1: 28, x2: 55, y2: 32 }, // Exactly at X=53, Y=30
                data: { capacity: 150, occupied: 75, documents: ['DOC-009'], status: 'Good', color: 'blue' }
            },
            { 
                id: 'shelf2-blue', 
                name: 'Shelf 2 (Blue)', 
                description: 'Blue section - Contains 60/80 documents - Good occupancy',
                bounds: { x1: 60, y1: 28, x2: 64, y2: 32 }, // Exactly at X=62, Y=30
                data: { capacity: 80, occupied: 60, documents: ['DOC-010', 'DOC-011'], status: 'Good', color: 'blue' }
            },
            { 
                id: 'shelf3-blue', 
                name: 'Shelf 3 (Blue)', 
                description: 'Blue section - Contains 45/90 documents - Good occupancy',
                bounds: { x1: 71, y1: 29, x2: 75, y2: 33 }, // Exactly at X=73, Y=31
                data: { capacity: 90, occupied: 45, documents: ['DOC-012', 'DOC-013'], status: 'Good', color: 'blue' }
            },
            { 
                id: 'shelf4-blue', 
                name: 'Shelf 4 (Blue)', 
                description: 'Blue section - Contains 30/70 documents - Good occupancy',
                bounds: { x1: 83, y1: 28, x2: 87, y2: 32 }, // Exactly at X=85, Y=30
                data: { capacity: 70, occupied: 30, documents: ['DOC-014'], status: 'Good', color: 'blue' }
            },
            { 
                id: 'shelf5-blue', 
                name: 'Shelf 5 (Blue)', 
                description: 'Blue section - Contains 25/60 documents - Good occupancy',
                bounds: { x1: 90, y1: 30, x2: 94, y2: 34 }, // Exactly at X=92, Y=32
                data: { capacity: 60, occupied: 25, documents: [], status: 'Good', color: 'blue' }
            },
            { 
                id: 'shelf6-blue', 
                name: 'Shelf 6 (Blue)', 
                description: 'Blue section - Contains 40/80 documents - Good occupancy',
                bounds: { x1: 46, y1: 42, x2: 50, y2: 46 }, // Exactly at X=48, Y=44
                data: { capacity: 80, occupied: 40, documents: ['DOC-015', 'DOC-016'], status: 'Good', color: 'blue' }
            },
            
            // Red Shelves (Bottom Right Row)
            { 
                id: 'shelf1-red', 
                name: 'Shelf 1 (Red)', 
                description: 'Red section - Contains 95/100 documents - Critical occupancy',
                bounds: { x1: 70, y1: 75, x2: 74, y2: 79 }, // Exactly at X=72, Y=77
                data: { capacity: 100, occupied: 95, documents: ['DOC-017', 'DOC-018', 'DOC-019'], status: 'Critical', color: 'red' }
            },
            { 
                id: 'shelf2-red', 
                name: 'Shelf 2 (Red)', 
                description: 'Red section - Contains 88/100 documents - High occupancy',
                bounds: { x1: 77, y1: 74, x2: 81, y2: 78 }, // Exactly at X=79, Y=76
                data: { capacity: 100, occupied: 88, documents: ['DOC-020', 'DOC-021'], status: 'High', color: 'red' }
            },
            { 
                id: 'shelf3-red', 
                name: 'Shelf 3 (Red)', 
                description: 'Red section - Contains 70/90 documents - High occupancy',
                bounds: { x1: 85, y1: 74, x2: 89, y2: 78 }, // Exactly at X=87, Y=76
                data: { capacity: 90, occupied: 70, documents: ['DOC-022', 'DOC-023', 'DOC-024'], status: 'High', color: 'red' }
            }
        ],
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
        },
        handleImageClick(event) {
            const img = document.getElementById('rmsLayoutImage');
            const rect = img.getBoundingClientRect();
            
            // Calculate click coordinates relative to image
            const x = ((event.clientX - rect.left) / rect.width) * 100;
            const y = ((event.clientY - rect.top) / rect.height) * 100;
            
            this.clickX = Math.round(x);
            this.clickY = Math.round(y);
            this.showCoordinates = true;
            
            // Hide coordinates after 3 seconds
            setTimeout(() => {
                this.showCoordinates = false;
            }, 3000);
            
            // Find which shelf was clicked
            this.clickedArea = this.getClickedArea(x, y);
        },
        handleImageHover(event) {
            const img = document.getElementById('rmsLayoutImage');
            const rect = img.getBoundingClientRect();
            
            // Calculate hover coordinates relative to image
            const x = ((event.clientX - rect.left) / rect.width) * 100;
            const y = ((event.clientY - rect.top) / rect.height) * 100;
            
            this.hoverX = Math.round(x);
            this.hoverY = Math.round(y);
        },
        getClickedArea(x, y) {
            return this.areas.find(area => 
                x >= area.bounds.x1 && 
                x <= area.bounds.x2 && 
                y >= area.bounds.y1 && 
                y <= area.bounds.y2
            );
        },
        viewShelfDetails(shelf) {
            console.log('Viewing details for:', shelf.name);
            // You can add navigation or modal functionality here
            alert(`Viewing details for ${shelf.name}\n\nCapacity: ${shelf.data.capacity}\nOccupied: ${shelf.data.occupied}\nDocuments: ${shelf.data.documents.length}`);
        },
        manageShelf(shelf) {
            console.log('Managing shelf:', shelf.name);
            // You can add management functionality here
            alert(`Managing ${shelf.name}\n\nStatus: ${shelf.data.status}\nAvailable space: ${shelf.data.capacity - shelf.data.occupied}`);
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

        <!-- RMS Layout Image -->
        <div class="p-4 bg-gray-100">
            <div class="bg-white rounded-lg shadow p-4">
                <div class="mb-3 text-center">
                    <h3 class="text-base font-semibold text-gray-900">RMS Layout</h3>
                    <p class="text-xs text-gray-600">Document management system layout</p>
                </div>
                
                <!-- Interactive Layout Image -->
                <div class="relative bg-gray-50 rounded p-4">
                    <div class="flex justify-center relative">
                        <img 
                            id="rmsLayoutImage"
                            src="<?= base_url('images/rmslayout.png') ?>" 
                            alt="RMS Layout" 
                            class="max-w-full h-auto rounded-lg shadow-md cursor-crosshair"
                            style="max-height: 500px;"
                            @click="handleImageClick($event)"
                            @mousemove="handleImageHover($event)"
                            @mouseleave="showHoverCoordinates = false"
                            @mouseenter="showHoverCoordinates = true"
                        >
                        
                        <!-- Hover Coordinates Display -->
                        <div x-show="showHoverCoordinates" 
                             class="absolute top-2 left-2 bg-black bg-opacity-75 text-white text-xs px-2 py-1 rounded">
                            <span x-text="`Hover: X=${hoverX}, Y=${hoverY}`"></span>
                        </div>
                        
                        <!-- Click Coordinates Display -->
                        <div x-show="showCoordinates" 
                             class="absolute top-2 right-2 bg-blue-600 bg-opacity-75 text-white text-xs px-2 py-1 rounded">
                            <span x-text="`Click: X=${clickX}, Y=${clickY}`"></span>
                        </div>
                        
                                                
                        <!-- Clicked Shelf Info -->
                        <div x-show="clickedArea" 
                             x-transition:enter="transition ease-out duration-200"
                             class="absolute bottom-2 left-2 bg-white rounded-lg shadow-lg p-4 max-w-xs">
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="font-bold text-lg text-gray-900" x-text="clickedArea?.name"></h4>
                                <button @click="clickedArea = null" class="text-gray-400 hover:text-gray-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                            
                            <!-- Shelf Data -->
                            <div x-show="clickedArea?.data" class="space-y-3">
                                <!-- Occupancy Info -->
                                <div class="bg-gray-50 p-3 rounded">
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-sm font-medium text-gray-700">Occupancy</span>
                                        <span class="text-xs px-2 py-1 rounded"
                                              :class="clickedArea.data.status === 'Critical' ? 'bg-red-100 text-red-800' : 
                                                      clickedArea.data.status === 'High' ? 'bg-yellow-100 text-yellow-800' : 
                                                      'bg-green-100 text-green-800'"
                                              x-text="clickedArea.data.status"></span>
                                    </div>
                                    <div class="text-lg font-bold text-gray-900">
                                        <span x-text="clickedArea.data.occupied"></span> / 
                                        <span x-text="clickedArea.data.capacity"></span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                                        <div class="h-2 rounded-full transition-all duration-300"
                                             :class="clickedArea.data.status === 'Critical' ? 'bg-red-500' : 
                                                      clickedArea.data.status === 'High' ? 'bg-yellow-500' : 
                                                      'bg-green-500'"
                                             :style="`width: ${(clickedArea.data.occupied / clickedArea.data.capacity) * 100}%`"></div>
                                    </div>
                                </div>
                                
                                <!-- Documents -->
                                <div class="bg-gray-50 p-3 rounded">
                                    <h5 class="text-sm font-medium text-gray-700 mb-2">Documents</h5>
                                    <div class="space-y-1">
                                        <template x-for="doc in clickedArea.data.documents" :key="doc">
                                            <div class="flex items-center justify-between text-xs">
                                                <span class="text-gray-600" x-text="doc"></span>
                                                <button class="text-blue-600 hover:text-blue-800">View</button>
                                            </div>
                                        </template>
                                        <div x-show="clickedArea.data.documents.length === 0" class="text-xs text-gray-500">
                                            No documents
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <p class="text-xs text-gray-600" x-text="clickedArea?.description"></p>
                            
                            <div class="mt-3 flex space-x-2">
                                <button @click="viewShelfDetails(clickedArea)" 
                                        class="text-xs bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600">
                                    View Details
                                </button>
                                <button @click="manageShelf(clickedArea)" 
                                        class="text-xs bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600">
                                    Manage
                                </button>
                            </div>
                        </div>
                    </div>
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
