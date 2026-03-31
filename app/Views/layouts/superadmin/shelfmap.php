<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="absolute inset-0 z-0" id="shelfMapApp">
    <div class="ml-20 transition-all duration-300" id="mainContent">
        <!-- Header -->
        <div class="p-4 bg-white border-b border-gray-200">
            <div class="flex items-center">
                <img src="<?= base_url('images/EMB-Logo.png') ?>" alt="EMB Logo" class="w-10 h-10 mr-3">
                <div>
                    <h2 class="text-xl font-bold text-gray-900">Shelf Map & Search</h2>
                    <p class="text-gray-600 text-sm">Manage and search through document shelves</p>
                </div>
            </div>
        </div>

        <!-- Search -->
        <div class="p-4 bg-gray-50">
            <div class="w-[30%]">
                <div class="relative">
                    <input 
                        type="text" 
                        id="searchInput"
                        placeholder="Search shelf ID, name, or document..."
                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                    <svg class="absolute left-3 top-2.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <div id="clearSearch" class="absolute right-3 top-2.5 hidden">
                        <button class="text-gray-400 hover:text-gray-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                <div id="searchResults" class="mt-1 text-xs text-gray-600 hidden">
                    <span id="resultsCount">0</span> shelves found
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
                        >
                        
                        <!-- Hover Coordinates Display -->
                        <div id="hoverCoordinates" class="absolute top-2 left-2 bg-black bg-opacity-75 text-white text-xs px-2 py-1 rounded hidden">
                            <span id="hoverText">Hover: X=0, Y=0</span>
                        </div>
                        
                        <!-- Click Coordinates Display -->
                        <div id="clickCoordinates" class="absolute top-2 right-2 bg-blue-600 bg-opacity-75 text-white text-xs px-2 py-1 rounded hidden">
                            <span id="clickText">Click: X=0, Y=0</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Shelf Details Popup Container -->
        <div class="p-4 bg-gray-50">
            <div id="clickedAreaDetails" class="bg-white rounded-lg shadow-xl p-6 hidden">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h4 id="areaName" class="font-bold text-lg text-gray-900">Shelf Details</h4>
                        <p id="areaDescription" class="text-sm text-gray-600">Description</p>
                    </div>
                    <button id="closeDetails" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <!-- Shelf Data -->
                <div id="shelfData" class="space-y-4 hidden">
                    <!-- Occupancy Info -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="flex justify-between items-center mb-3">
                            <span class="text-sm font-medium text-gray-700">Occupancy</span>
                            <span id="occupancyStatus" class="text-xs px-2 py-1 rounded-full bg-green-100 text-green-800">Good</span>
                        </div>
                        <div class="text-xl font-bold text-gray-900">
                            <span id="occupiedCount">0</span> / <span id="capacityCount">0</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-3 mt-3">
                            <div id="occupancyBar" class="h-3 rounded-full transition-all duration-300 bg-green-500" style="width: 0%"></div>
                        </div>
                    </div>
                    
                    <!-- Rack Sections -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h5 class="text-sm font-medium text-gray-700 mb-3">Rack Sections</h5>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            <!-- Rack 1 -->
                            <div class="rack-section bg-white p-3 rounded border border-gray-200 cursor-pointer hover:shadow-md hover:border-blue-300 transition-all" data-rack="1">
                                <div class="flex items-center mb-2">
                                    <svg class="w-4 h-4 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                    </svg>
                                    <span class="font-semibold text-sm">Rack 1</span>
                                </div>
                                <div class="text-xs text-gray-600">25 documents</div>
                                <div class="text-xs text-green-600">Available: 5</div>
                                <div class="mt-2 text-xs">
                                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded">Good</span>
                                </div>
                            </div>
                            
                            <!-- Rack 2 -->
                            <div class="rack-section bg-white p-3 rounded border border-gray-200 cursor-pointer hover:shadow-md hover:border-blue-300 transition-all" data-rack="2">
                                <div class="flex items-center mb-2">
                                    <svg class="w-4 h-4 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6A2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                    </svg>
                                    <span class="font-semibold text-sm">Rack 2</span>
                                </div>
                                <div class="text-xs text-gray-600">30 documents</div>
                                <div class="text-xs text-yellow-600">Available: 0</div>
                                <div class="mt-2 text-xs">
                                    <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded">High</span>
                                </div>
                            </div>
                            
                            <!-- Rack 3 -->
                            <div class="rack-section bg-white p-3 rounded border border-gray-200 cursor-pointer hover:shadow-md hover:border-blue-300 transition-all" data-rack="3">
                                <div class="flex items-center mb-2">
                                    <svg class="w-4 h-4 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6A2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                    </svg>
                                    <span class="font-semibold text-sm">Rack 3</span>
                                </div>
                                <div class="text-xs text-gray-600">28 documents</div>
                                <div class="text-xs text-green-600">Available: 2</div>
                                <div class="mt-2 text-xs">
                                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded">Good</span>
                                </div>
                            </div>
                            
                            <!-- Rack 4 -->
                            <div class="rack-section bg-white p-3 rounded border border-gray-200 cursor-pointer hover:shadow-md hover:border-blue-300 transition-all" data-rack="4">
                                <div class="flex items-center mb-2">
                                    <svg class="w-4 h-4 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6A2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                    </svg>
                                    <span class="font-semibold text-sm">Rack 4</span>
                                </div>
                                <div class="text-xs text-gray-600">22 documents</div>
                                <div class="text-xs text-green-600">Available: 8</div>
                                <div class="mt-2 text-xs">
                                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded">Good</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Rack Folders Display -->
                    <div id="rackFoldersDisplay" class="bg-blue-50 p-4 rounded-lg border border-blue-200 hidden">
                        <div class="flex items-center justify-between mb-3">
                            <h5 class="text-sm font-medium text-blue-900">Rack <span id="selectedRackNumber">1</span> - Folders</h5>
                            <button id="closeRackFolders" class="text-blue-600 hover:text-blue-800">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                        <div id="rackFoldersGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                            <!-- Folders will be dynamically added here -->
                        </div>
                    </div>
                    
                    <!-- Documents -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h5 class="text-sm font-medium text-gray-700 mb-3">Recent Documents (<span id="documentCount">0</span>)</h5>
                        <div id="documentsList" class="space-y-2 max-h-20 overflow-y-auto">
                            <!-- Documents will be dynamically added here -->
                        </div>
                        <div id="noDocuments" class="text-xs text-gray-500 text-center py-2 hidden">
                            No documents in this shelf
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Shelf Details Modal -->
        <div id="shelfModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow max-w-lg w-full mx-4 max-h-[80vh] overflow-y-auto">
                <div class="p-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 id="modalShelfName" class="text-lg font-semibold text-gray-900">Shelf Details</h3>
                            <p id="modalShelfId" class="text-gray-500 text-sm">ID</p>
                        </div>
                        <button id="closeModal" class="text-gray-400 hover:text-gray-600">
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
                                <div id="modalOccupied" class="text-xl font-bold text-gray-900">0</div>
                                <div class="text-xs text-gray-500">Occupied</div>
                            </div>
                            <div class="bg-gray-50 p-3 rounded">
                                <div id="modalAvailable" class="text-xl font-bold text-gray-900">0</div>
                                <div class="text-xs text-gray-500">Available</div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <div class="flex justify-between text-xs mb-1">
                                <span class="text-gray-600">Occupancy</span>
                                <span id="modalOccupancyPercent" class="font-medium">0%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div id="modalOccupancyBar" class="h-2 rounded-full transition-all duration-300 bg-green-500" style="width: 0%"></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Documents -->
                    <div>
                        <h4 class="text-base font-medium text-gray-900 mb-3">Documents</h4>
                        <div id="modalDocumentsList" class="space-y-1">
                            <!-- Documents will be dynamically added here -->
                        </div>
                        <div id="modalNoDocuments" class="text-center py-4 text-gray-500 text-sm hidden">
                            No documents
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .rack-section {
        transition: all 0.2s ease;
    }
    
    .rack-section:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .fade-in {
        animation: fadeIn 0.3s ease;
    }
</style>

<script>
class ShelfMapApp {
    constructor() {
        this.sidebarOpen = localStorage.getItem('sidebarOpen') !== 'false';
        this.searchQuery = '';
        this.selectedShelf = null;
        this.selectedRack = null;
        this.rackFolders = [];
        this.clickX = 0;
        this.clickY = 0;
        this.hoverX = 0;
        this.hoverY = 0;
        this.showCoordinates = false;
        this.showHoverCoordinates = true;
        this.clickedArea = null;
        
        // Shelf areas data
        this.areas = [
            // Yellow Shelves (Bottom Row)
            { 
                id: 'shelf1-yellow', 
                name: 'Shelf 1 (Yellow)', 
                description: 'Yellow section - Contains 85/100 documents - High occupancy',
                bounds: { x1: 45, y1: 75, x2: 49, y2: 79 },
                data: { capacity: 100, occupied: 85, documents: ['DOC-001', 'DOC-002', 'DOC-003'], status: 'High', color: 'yellow' }
            },
            { 
                id: 'shelf2-yellow', 
                name: 'Shelf 2 (Yellow)', 
                description: 'Yellow section - Contains 92/100 documents - Critical occupancy',
                bounds: { x1: 53, y1: 75, x2: 57, y2: 79 },
                data: { capacity: 100, occupied: 92, documents: ['DOC-004', 'DOC-005'], status: 'Critical', color: 'yellow' }
            },
            { 
                id: 'shelf3-yellow', 
                name: 'Shelf 3 (Yellow)', 
                description: 'Yellow section - Contains 120/150 documents - High occupancy',
                bounds: { x1: 61, y1: 74, x2: 65, y2: 78 },
                data: { capacity: 150, occupied: 120, documents: ['DOC-006', 'DOC-007', 'DOC-008'], status: 'High', color: 'yellow' }
            },
            
            // Blue Shelves (Top Row)
            { 
                id: 'shelf1-blue', 
                name: 'Shelf 1 (Blue)', 

                bounds: { x1: 51, y1: 28, x2: 55, y2: 32 },
                data: { capacity: 150, occupied: 75, documents: ['DOC-009'], status: 'Good', color: 'blue' }
            },
            { 
                id: 'shelf2-blue', 
                name: 'Shelf 2 (Blue)', 
                description: 'Blue section - Contains 60/80 documents - Good occupancy',
                bounds: { x1: 60, y1: 28, x2: 64, y2: 32 },
                data: { capacity: 80, occupied: 60, documents: ['DOC-010', 'DOC-011'], status: 'Good', color: 'blue' }
            },
            { 
                id: 'shelf3-blue', 
                name: 'Shelf 3 (Blue)', 
                description: 'Blue section - Contains 45/90 documents - Good occupancy',
                bounds: { x1: 71, y1: 29, x2: 75, y2: 33 },
                data: { capacity: 90, occupied: 45, documents: ['DOC-012', 'DOC-013'], status: 'Good', color: 'blue' }
            },
            { 
                id: 'shelf4-blue', 
                name: 'Shelf 4 (Blue)', 
                description: 'Blue section - Contains 30/70 documents - Good occupancy',
                bounds: { x1: 83, y1: 28, x2: 87, y2: 32 },
                data: { capacity: 70, occupied: 30, documents: ['DOC-014'], status: 'Good', color: 'blue' }
            },
            { 
                id: 'shelf5-blue', 
                name: 'Shelf 5 (Blue)', 
                description: 'Blue section - Contains 25/60 documents - Good occupancy',
                bounds: { x1: 90, y1: 30, x2: 94, y2: 34 },
                data: { capacity: 60, occupied: 25, documents: [], status: 'Good', color: 'blue' }
            },
            { 
                id: 'shelf6-blue', 
                name: 'Shelf 6 (Blue)', 
                description: 'Blue section - Contains 40/80 documents - Good occupancy',
                bounds: { x1: 46, y1: 42, x2: 50, y2: 46 },
                data: { capacity: 80, occupied: 40, documents: ['DOC-015', 'DOC-016'], status: 'Good', color: 'blue' }
            },
            
            // Red Shelves (Bottom Right Row)
            { 
                id: 'shelf1-red', 
                name: 'Shelf 1 (Red)', 
                description: 'Red section - Contains 95/100 documents - Critical occupancy',
                bounds: { x1: 70, y1: 75, x2: 74, y2: 79 },
                data: { capacity: 100, occupied: 95, documents: ['DOC-017', 'DOC-018', 'DOC-019'], status: 'Critical', color: 'red' }
            },
            { 
                id: 'shelf2-red', 
                name: 'Shelf 2 (Red)', 
                description: 'Red section - Contains 88/100 documents - High occupancy',
                bounds: { x1: 77, y1: 74, x2: 81, y2: 78 },
                data: { capacity: 100, occupied: 88, documents: ['DOC-020', 'DOC-021'], status: 'High', color: 'red' }
            },
            { 
                id: 'shelf3-red', 
                name: 'Shelf 3 (Red)', 
                description: 'Red section - Contains 70/90 documents - High occupancy',
                bounds: { x1: 85, y1: 74, x2: 89, y2: 78 },
                data: { capacity: 90, occupied: 70, documents: ['DOC-022', 'DOC-023', 'DOC-024'], status: 'High', color: 'red' }
            }
        ];
        
        this.shelves = [
            { id: 'A1', name: 'Shelf A-1', capacity: 100, occupied: 85, documents: ['DOC-001', 'DOC-002', 'DOC-003'] },
            { id: 'A2', name: 'Shelf A-2', capacity: 100, occupied: 92, documents: ['DOC-004', 'DOC-005'] },
            { id: 'B1', name: 'Shelf B-1', capacity: 150, occupied: 120, documents: ['DOC-006', 'DOC-007', 'DOC-008'] },
            { id: 'B2', name: 'Shelf B-2', capacity: 150, occupied: 75, documents: ['DOC-009'] },
            { id: 'C1', name: 'Shelf C-1', capacity: 80, occupied: 60, documents: ['DOC-010', 'DOC-011'] },
            { id: 'C2', name: 'Shelf C-2', capacity: 80, occupied: 40, documents: [] }
        ];
        
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.updateSidebarState();
    }
    
    bindEvents() {
        // Search functionality
        const searchInput = document.getElementById('searchInput');
        const clearSearch = document.getElementById('clearSearch');
        
        searchInput.addEventListener('input', (e) => {
            this.searchQuery = e.target.value;
            this.updateSearchResults();
        });
        
        clearSearch.addEventListener('click', () => {
            this.searchQuery = '';
            searchInput.value = '';
            this.updateSearchResults();
        });
        
        // Image interactions
        const rmsImage = document.getElementById('rmsLayoutImage');
        rmsImage.addEventListener('click', (e) => this.handleImageClick(e));
        rmsImage.addEventListener('mousemove', (e) => this.handleImageHover(e));
        rmsImage.addEventListener('mouseleave', () => {
            this.showHoverCoordinates = false;
            document.getElementById('hoverCoordinates').classList.add('hidden');
        });
        rmsImage.addEventListener('mouseenter', () => {
            this.showHoverCoordinates = true;
        });
        
        // Rack sections
        document.querySelectorAll('.rack-section').forEach(rack => {
            rack.addEventListener('click', (e) => {
                const rackNumber = parseInt(e.currentTarget.dataset.rack);
                this.selectedRack = rackNumber;
                this.showRackFolders(rackNumber);
            });
        });
        
        // Modal controls
        document.getElementById('closeDetails').addEventListener('click', () => {
            this.clickedArea = null;
            document.getElementById('clickedAreaDetails').classList.add('hidden');
        });
        
        document.getElementById('closeRackFolders').addEventListener('click', () => {
            this.selectedRack = null;
            document.getElementById('rackFoldersDisplay').classList.add('hidden');
        });
        
        document.getElementById('closeModal').addEventListener('click', () => {
            this.selectedShelf = null;
            document.getElementById('shelfModal').classList.add('hidden');
            document.getElementById('shelfModal').classList.remove('flex');
        });
        
        // Storage event listener
        window.addEventListener('storage', (e) => {
            if (e.key === 'sidebarOpen') {
                this.sidebarOpen = e.newValue !== 'false';
                this.updateSidebarState();
            }
        });
    }
    
    updateSidebarState() {
        const mainContent = document.getElementById('mainContent');
        if (this.sidebarOpen) {
            mainContent.classList.remove('ml-20');
            mainContent.classList.add('ml-72');
        } else {
            mainContent.classList.remove('ml-72');
            mainContent.classList.add('ml-20');
        }
    }
    
    get filteredShelves() {
        if (!this.searchQuery) return this.shelves;
        
        return this.shelves.filter(shelf => 
            shelf.id.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
            shelf.name.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
            shelf.documents.some(doc => doc.toLowerCase().includes(this.searchQuery.toLowerCase()))
        );
    }
    
    updateSearchResults() {
        const resultsCount = document.getElementById('resultsCount');
        const searchResults = document.getElementById('searchResults');
        const clearButton = document.getElementById('clearSearch');
        
        const filtered = this.filteredShelves;
        resultsCount.textContent = filtered.length;
        
        if (this.searchQuery) {
            searchResults.classList.remove('hidden');
            clearButton.classList.remove('hidden');
        } else {
            searchResults.classList.add('hidden');
            clearButton.classList.add('hidden');
        }
    }
    
    handleImageClick(event) {
        const img = document.getElementById('rmsLayoutImage');
        const rect = img.getBoundingClientRect();
        
        // Calculate click coordinates relative to image
        const x = ((event.clientX - rect.left) / rect.width) * 100;
        const y = ((event.clientY - rect.top) / rect.height) * 100;
        
        this.clickX = Math.round(x);
        this.clickY = Math.round(y);
        this.showCoordinates = true;
        
        // Update coordinates display
        const clickCoords = document.getElementById('clickCoordinates');
        const clickText = document.getElementById('clickText');
        clickText.textContent = `Click: X=${this.clickX}, Y=${this.clickY}`;
        clickCoords.classList.remove('hidden');
        
        // Hide coordinates after 3 seconds
        setTimeout(() => {
            this.showCoordinates = false;
            clickCoords.classList.add('hidden');
        }, 3000);
        
        // Find which shelf was clicked
        this.clickedArea = this.getClickedArea(x, y);
        if (this.clickedArea) {
            this.showAreaDetails();
        }
    }
    
    handleImageHover(event) {
        if (!this.showHoverCoordinates) return;
        
        const img = document.getElementById('rmsLayoutImage');
        const rect = img.getBoundingClientRect();
        
        // Calculate hover coordinates relative to image
        const x = ((event.clientX - rect.left) / rect.width) * 100;
        const y = ((event.clientY - rect.top) / rect.height) * 100;
        
        this.hoverX = Math.round(x);
        this.hoverY = Math.round(y);
        
        // Update hover display
        const hoverCoords = document.getElementById('hoverCoordinates');
        const hoverText = document.getElementById('hoverText');
        hoverText.textContent = `Hover: X=${this.hoverX}, Y=${this.hoverY}`;
        hoverCoords.classList.remove('hidden');
    }
    
    getClickedArea(x, y) {
        return this.areas.find(area => 
            x >= area.bounds.x1 && 
            x <= area.bounds.x2 && 
            y >= area.bounds.y1 && 
            y <= area.bounds.y2
        );
    }
    
    showAreaDetails() {
        const details = document.getElementById('clickedAreaDetails');
        const areaName = document.getElementById('areaName');
        const areaDescription = document.getElementById('areaDescription');
        const shelfData = document.getElementById('shelfData');
        const documentsList = document.getElementById('documentsList');
        const noDocuments = document.getElementById('noDocuments');
        
        areaName.textContent = this.clickedArea.name;
        areaDescription.textContent = this.clickedArea.description;
        
        // Update occupancy info
        const occupancyStatus = document.getElementById('occupancyStatus');
        const occupiedCount = document.getElementById('occupiedCount');
        const capacityCount = document.getElementById('capacityCount');
        const occupancyBar = document.getElementById('occupancyBar');
        
        occupancyStatus.textContent = this.clickedArea.data.status;
        occupancyStatus.className = `text-xs px-2 py-1 rounded-full ${
            this.clickedArea.data.status === 'Critical' ? 'bg-red-100 text-red-800' : 
            this.clickedArea.data.status === 'High' ? 'bg-yellow-100 text-yellow-800' : 
            'bg-green-100 text-green-800'
        }`;
        
        occupiedCount.textContent = this.clickedArea.data.occupied;
        capacityCount.textContent = this.clickedArea.data.capacity;
        
        const occupancyPercentage = (this.clickedArea.data.occupied / this.clickedArea.data.capacity) * 100;
        occupancyBar.style.width = `${occupancyPercentage}%`;
        occupancyBar.className = `h-3 rounded-full transition-all duration-300 ${
            this.clickedArea.data.status === 'Critical' ? 'bg-red-500' : 
            this.clickedArea.data.status === 'High' ? 'bg-yellow-500' : 
            'bg-green-500'
        }`;
        
        // Update documents list
        documentsList.innerHTML = '';
        if (this.clickedArea.data.documents.length > 0) {
            this.clickedArea.data.documents.forEach(doc => {
                const docItem = document.createElement('div');
                docItem.className = 'flex items-center justify-between text-xs p-2 bg-white rounded border border-gray-200';
                docItem.innerHTML = `
                    <span class="text-gray-700">${doc}</span>
                    <button class="text-blue-600 hover:text-blue-800 font-medium">View</button>
                `;
                documentsList.appendChild(docItem);
            });
            noDocuments.classList.add('hidden');
        } else {
            noDocuments.classList.remove('hidden');
        }
        
        document.getElementById('documentCount').textContent = this.clickedArea.data.documents.length;
        shelfData.classList.remove('hidden');
        details.classList.remove('hidden');
    }
    
    showRackFolders(rackNumber) {
        // Simulate fetching folders for selected rack
        this.rackFolders = [
            { id: 1, file_code: 'F001', company_name: 'Company A', location_code: '1A', status: 'Available' },
            { id: 2, file_code: 'F002', company_name: 'Company B', location_code: '1A', status: 'Available' },
            { id: 3, file_code: 'F003', company_name: 'Company C', location_code: '1A', status: 'Borrowed' },
            { id: 4, file_code: 'F004', company_name: 'Company D', location_code: '1A', status: 'Available' },
            { id: 5, file_code: 'F005', company_name: 'Company E', location_code: '1A', status: 'Available' }
        ];
        
        // Customize folders based on rack number
        if (rackNumber === 2) {
            this.rackFolders = [
                { id: 6, file_code: 'F006', company_name: 'Company F', location_code: '1B', status: 'Available' },
                { id: 7, file_code: 'F007', company_name: 'Company G', location_code: '1B', status: 'Available' },
                { id: 8, file_code: 'F008', company_name: 'Company H', location_code: '1B', status: 'Archived' }
            ];
        } else if (rackNumber === 3) {
            this.rackFolders = [
                { id: 9, file_code: 'F009', company_name: 'Company I', location_code: '1C', status: 'Available' },
                { id: 10, file_code: 'F010', company_name: 'Company J', location_code: '1C', status: 'Available' },
                { id: 11, file_code: 'F011', company_name: 'Company K', location_code: '1C', status: 'Borrowed' },
                { id: 12, file_code: 'F012', company_name: 'Company L', location_code: '1C', status: 'Available' }
            ];
        } else if (rackNumber === 4) {
            this.rackFolders = [
                { id: 13, file_code: 'F013', company_name: 'Company M', location_code: '1D', status: 'Available' },
                { id: 14, file_code: 'F014', company_name: 'Company N', location_code: '1D', status: 'Available' },
                { id: 15, file_code: 'F015', company_name: 'Company O', location_code: '1D', status: 'Available' }
            ];
        }
        
        this.renderRackFolders();
    }
    
    renderRackFolders() {
        const rackFoldersDisplay = document.getElementById('rackFoldersDisplay');
        const selectedRackNumber = document.getElementById('selectedRackNumber');
        const rackFoldersGrid = document.getElementById('rackFoldersGrid');
        
        selectedRackNumber.textContent = this.selectedRack;
        rackFoldersGrid.innerHTML = '';
        
        this.rackFolders.forEach(folder => {
            const folderElement = document.createElement('div');
            folderElement.className = 'bg-white p-3 rounded border border-blue-200';
            folderElement.innerHTML = `
                <div class="flex items-center mb-2">
                    <svg class="w-4 h-4 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <span class="font-semibold text-sm text-gray-900">${folder.file_code}</span>
                </div>
                <div class="text-xs text-gray-600">${folder.company_name}</div>
                <div class="text-xs text-gray-500 mt-1">Location: ${folder.location_code}</div>
                <div class="mt-2 flex justify-between">
                    <span class="text-xs px-2 py-1 bg-green-100 text-green-800 rounded">${folder.status}</span>
                    <button class="text-xs text-blue-600 hover:text-blue-800 font-medium">View</button>
                </div>
            `;
            rackFoldersGrid.appendChild(folderElement);
        });
        
        rackFoldersDisplay.classList.remove('hidden');
    }
    
    getOccupancyColor(percentage) {
        if (percentage >= 90) return 'bg-red-500';
        if (percentage >= 75) return 'bg-yellow-500';
        return 'bg-green-500';
    }
    
    getOccupancyText(percentage) {
        if (percentage >= 90) return 'Critical';
        if (percentage >= 75) return 'High';
        return 'Good';
    }
}

// Initialize the app when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new ShelfMapApp();
});
</script>
<?= $this->endSection() ?>
