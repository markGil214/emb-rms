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
                    <h3 class="text-base font-semibold text-gray-900">Cabinet Layout</h3>
                    <p class="text-xs text-gray-600">Document storage cabinet visualization</p>
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
                        
                        <!-- Click Animation Effect -->
                        <div id="clickAnimation" class="absolute pointer-events-none hidden">
                            <div class="w-8 h-8 border-4 border-blue-500 rounded-full animate-ping"></div>
                        </div>
                        
                                                
                        <!-- Brenda Location Indicator - Hover Profile Card -->
                        <div 
                            id="brendaLocation"
                            class="absolute hidden"
                            style="left: 39%; top: 35%;"
                        >
                            <!-- Profile Card -->
                            <div class="absolute -top-4 left-1/2 transform -translate-x-1/2 bg-white rounded shadow-xl p-1 border border-purple-200 animate-bounce-in" style="width: 4%; height: 5%; min-width: 64px; min-height: 80px; max-width: 96px; max-height: 120px;">
                                <div class="flex flex-col items-center justify-center h-full">
                                    <!-- MB Icon -->
                                    <div class="w-6 h-6 bg-purple-100 rounded-full flex items-center justify-center mb-1">
                                        <span class="text-xs font-bold text-purple-700">MB</span>
                                    </div>
                                    <!-- Name -->
                                    <div class="text-center">
                                        <div class="font-semibold text-gray-900 text-xs">Ma'am Brenda</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Arlene Location Indicator - Hover Profile Card -->
                        <div 
                            id="arleneLocation"
                            class="absolute hidden"
                            style="left: 40%; top: 9%;"
                        >
                            <!-- Profile Card -->
                            <div class="absolute -top-4 left-1/2 transform -translate-x-1/2 bg-white rounded shadow-xl p-1 border border-pink-200 animate-bounce-in" style="width: 4%; height: 5%; min-width: 64px; min-height: 80px; max-width: 96px; max-height: 120px;">
                                <div class="flex flex-col items-center justify-center h-full">
                                    <!-- MA Icon -->
                                    <div class="w-6 h-6 bg-pink-100 rounded-full flex items-center justify-center mb-1">
                                        <span class="text-xs font-bold text-pink-700">MA</span>
                                    </div>
                                    <!-- Name -->
                                    <div class="text-center">
                                        <div class="font-semibold text-gray-900 text-xs">Ma'am Arlene</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Liza Location Indicator - Hover Profile Card -->
                        <div 
                            id="lizaLocation"
                            class="absolute hidden"
                            style="left: 19.5%; top: 70%;"
                        >
                            <!-- Profile Card -->
                            <div class="absolute -top-4 left-1/2 transform -translate-x-1/2 bg-white rounded shadow-xl p-1 border border-green-200 animate-bounce-in" style="width: 4%; height: 5%; min-width: 64px; min-height: 80px; max-width: 96px; max-height: 120px;">
                                <div class="flex flex-col items-center justify-center h-full">
                                    <!-- ML Icon -->
                                    <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center mb-1">
                                        <span class="text-xs font-bold text-green-700">ML</span>
                                    </div>
                                    <!-- Name -->
                                    <div class="text-center">
                                        <div class="font-semibold text-gray-900 text-xs">Ma'am Liza</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cabinet Visualization Popup Container -->
        <div class="p-4 bg-gray-50">
            <div id="cabinetVisualization" class="bg-white rounded-lg shadow-xl p-6 hidden">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h4 id="cabinetName" class="font-bold text-lg text-gray-900">Cabinet Details</h4>
                        <p id="cabinetDescription" class="text-sm text-gray-600">Click on a rack to view details</p>
                    </div>
                    <button id="closeCabinet" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <!-- Simplified Cabinet View -->
                <div id="cabinetContainer" class="bg-gray-100 rounded-lg p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Storage Section (Left) -->
                        <div>
                            <h5 class="font-bold text-gray-800 mb-4 text-center">Storage Racks</h5>
                            <div class="space-y-3">
                                <!-- Rack A -->
                                <div class="rack-slot bg-blue-100 border-2 border-blue-300 rounded-lg p-3 cursor-pointer hover:bg-blue-200 transition-all duration-200 hover:shadow-lg" data-rack="A">
                                    <div class="flex items-center justify-between">
                                        <div class="text-lg font-bold text-blue-800">Rack A</div>
                                        <div class="text-sm text-gray-600">
                                            <span class="rack-occupied">0</span>/<span class="rack-capacity">100</span>
                                        </div>
                                    </div>
                                    <div class="w-full bg-gray-300 rounded-full h-2 mt-2">
                                        <div class="rack-occupancy-bar bg-blue-500 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                                    </div>
                                </div>
                                
                                <!-- Rack B -->
                                <div class="rack-slot bg-green-100 border-2 border-green-300 rounded-lg p-3 cursor-pointer hover:bg-green-200 transition-all duration-200 hover:shadow-lg" data-rack="B">
                                    <div class="flex items-center justify-between">
                                        <div class="text-lg font-bold text-green-800">Rack B</div>
                                        <div class="text-sm text-gray-600">
                                            <span class="rack-occupied">0</span>/<span class="rack-capacity">100</span>
                                        </div>
                                    </div>
                                    <div class="w-full bg-gray-300 rounded-full h-2 mt-2">
                                        <div class="rack-occupancy-bar bg-green-500 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                                    </div>
                                </div>
                                
                                <!-- Rack C -->
                                <div class="rack-slot bg-yellow-100 border-2 border-yellow-300 rounded-lg p-3 cursor-pointer hover:bg-yellow-200 transition-all duration-200 hover:shadow-lg" data-rack="C">
                                    <div class="flex items-center justify-between">
                                        <div class="text-lg font-bold text-yellow-800">Rack C</div>
                                        <div class="text-sm text-gray-600">
                                            <span class="rack-occupied">0</span>/<span class="rack-capacity">100</span>
                                        </div>
                                    </div>
                                    <div class="w-full bg-gray-300 rounded-full h-2 mt-2">
                                        <div class="rack-occupancy-bar bg-yellow-500 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                                    </div>
                                </div>
                                
                                <!-- Rack D -->
                                <div class="rack-slot bg-red-100 border-2 border-red-300 rounded-lg p-3 cursor-pointer hover:bg-red-200 transition-all duration-200 hover:shadow-lg" data-rack="D">
                                    <div class="flex items-center justify-between">
                                        <div class="text-lg font-bold text-red-800">Rack D</div>
                                        <div class="text-sm text-gray-600">
                                            <span class="rack-occupied">0</span>/<span class="rack-capacity">100</span>
                                        </div>
                                    </div>
                                    <div class="w-full bg-gray-300 rounded-full h-2 mt-2">
                                        <div class="rack-occupancy-bar bg-red-500 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Folders Section (Right) -->
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <h5 class="font-bold text-gray-800">Folders</h5>
                                <button id="closeRackDetails" class="text-gray-600 hover:text-gray-800">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                            <div id="rackFoldersGrid" class="space-y-2 max-h-96 overflow-y-auto">
                                <div class="text-center text-gray-500 py-8">
                                    <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                    </svg>
                                    <p class="text-sm">Select a rack to view folders</p>
                                </div>
                            </div>
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
    
    @keyframes bounce-in {
        0% { opacity: 0; transform: translate(-50%, -20px) scale(0.3); }
        50% { transform: translate(-50%, -20px) scale(1.05); }
        100% { opacity: 1; transform: translate(-50%, -20px) scale(1); }
    }
    
    .animate-bounce-in {
        animation: bounce-in 0.5s ease-out;
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
        
        // Will be populated from API
        this.areas = [];
        this.shelves = [];
        this.locations = [];
        
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.updateSidebarState();
        this.loadShelfData(); // Fetch data from API
    }
    
    /**
     * Fetch shelf data from the API
     */
    async loadShelfData() {
        try {
            const response = await fetch('<?= base_url('api/shelfmap/data') ?>', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            });

            if (!response.ok) {
                const errorText = await response.text();
                console.error('API Error Response:', {
                    status: response.status,
                    statusText: response.statusText,
                    body: errorText.substring(0, 500)
                });
                throw new Error(`API Error ${response.status}: ${response.statusText}`);
            }

            const result = await response.json();

            if (result.status === 'success' && result.data) {
                this.areas = result.data.areas || [];
                this.shelves = result.data.shelves || [];
                this.locations = result.data.locations || [];
                
                console.log('✓ Shelf data loaded successfully', {
                    areas: this.areas.length,
                    shelves: this.shelves.length,
                    locations: this.locations.length,
                    totalFolders: result.data.totalFolders,
                    totalLocations: result.data.totalLocations
                });
            } else {
                console.warn('API returned unexpected data:', result);
                throw new Error(result.message || 'No data returned from API');
            }
        } catch (error) {
            console.error('Error loading shelf data from API:', error);
            alert(`Failed to load shelf data: ${error.message}. Check browser console and server logs.`);
        }
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
        
                
        // Cabinet controls
        document.getElementById('closeCabinet').addEventListener('click', () => {
            this.clickedArea = null;
            document.getElementById('cabinetVisualization').classList.add('hidden');
        });
        
        document.getElementById('closeRackDetails').addEventListener('click', () => {
            this.selectedRack = null;
            document.getElementById('selectedRackDetails').classList.add('hidden');
        });
        
        // Rack slot interactions
        document.getElementById('cabinetContainer').addEventListener('click', (e) => {
            const rackSlot = e.target.closest('.rack-slot');
            if (rackSlot) {
                const rackLetter = rackSlot.dataset.rack;
                this.animateRackClick(rackSlot);
                this.showRackDetails(rackLetter);
            }
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
        
        const query = this.searchQuery.toLowerCase();
        return this.shelves.filter(shelf => 
            shelf.id?.toLowerCase().includes(query) ||
            shelf.name?.toLowerCase().includes(query) ||
            shelf.documents?.some(doc => doc.toLowerCase().includes(query))
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
        
        // Show click animation at click position
        this.showClickAnimation(event.clientX - rect.left, event.clientY - rect.top);
        
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
        
        // Check if hovering over Brenda's location area
        this.checkBrendaLocation(x, y);
    }
    
    checkBrendaLocation(x, y) {
        const brendaLocation = document.getElementById('brendaLocation');
        const arleneLocation = document.getElementById('arleneLocation');
        const lizaLocation = document.getElementById('lizaLocation');
        
        // Check Brenda's coordinates (x=39-41%, y=35-40%)
        if ((x >= 39 && x <= 41) && (y >= 35 && y <= 40)) {
            brendaLocation.classList.remove('hidden');
        } else {
            brendaLocation.classList.add('hidden');
        }
        
        // Check Arlene's coordinates (x=40-42%, y=9-13%)
        if ((x >= 40 && x <= 42) && (y >= 9 && y <= 13)) {
            arleneLocation.classList.remove('hidden');
        } else {
            arleneLocation.classList.add('hidden');
        }
        
        // Check Liza's coordinates (x=15-17%, y=79-82%)
        if ((x >= 15 && x <= 17) && (y >= 79 && y <= 82)) {
            lizaLocation.classList.remove('hidden');
        } else {
            lizaLocation.classList.add('hidden');
        }
    }
    
    showClickAnimation(x, y) {
        const clickAnimation = document.getElementById('clickAnimation');
        const img = document.getElementById('rmsLayoutImage');
        const rect = img.getBoundingClientRect();
        const containerRect = img.parentElement.getBoundingClientRect();
        
        // Calculate position relative to the container
        const relativeX = x + (rect.left - containerRect.left);
        const relativeY = y + (rect.top - containerRect.top);
        
        // Position animation at click coordinates
        clickAnimation.style.left = `${relativeX - 16}px`; // Center the 32px animation
        clickAnimation.style.top = `${relativeY - 16}px`;
        clickAnimation.classList.remove('hidden');
        
        // Remove animation after 1 second
        setTimeout(() => {
            clickAnimation.classList.add('hidden');
        }, 1000);
        
        // Add a ripple effect
        this.createRippleEffect(x, y);
    }
    
    createRippleEffect(x, y) {
        const img = document.getElementById('rmsLayoutImage');
        const rect = img.getBoundingClientRect();
        const containerRect = img.parentElement.getBoundingClientRect();
        
        // Calculate position relative to the container
        const relativeX = x + (rect.left - containerRect.left);
        const relativeY = y + (rect.top - containerRect.top);
        
        // Create ripple element
        const ripple = document.createElement('div');
        ripple.className = 'absolute pointer-events-none';
        ripple.style.left = `${relativeX - 20}px`;
        ripple.style.top = `${relativeY - 20}px`;
        ripple.innerHTML = `
            <div class="w-10 h-10 border-2 border-blue-400 rounded-full animate-ping"></div>
        `;
        
        // Add ripple to image container
        img.parentElement.appendChild(ripple);
        
        // Remove ripple after animation
        setTimeout(() => {
            ripple.remove();
        }, 1000);
    }
    
    animateRackClick(rackSlot) {
        // Add scale animation to the rack slot
        rackSlot.style.transform = 'scale(0.95)';
        rackSlot.style.transition = 'transform 0.1s ease';
        
        // Create ripple effect on the rack slot
        const rect = rackSlot.getBoundingClientRect();
        const rackRipple = document.createElement('div');
        rackRipple.className = 'absolute pointer-events-none';
        rackRipple.style.left = `${rect.width / 2 - 15}px`;
        rackRipple.style.top = `${rect.height / 2 - 15}px`;
        rackRipple.style.width = '30px';
        rackRipple.style.height = '30px';
        rackRipple.innerHTML = `
            <div class="w-full h-full border-2 border-white rounded-full animate-ping"></div>
        `;
        
        rackSlot.style.position = 'relative';
        rackSlot.appendChild(rackRipple);
        
        // Restore scale and remove ripple
        setTimeout(() => {
            rackSlot.style.transform = 'scale(1)';
            rackRipple.remove();
        }, 300);
        
        // Add glow effect
        rackSlot.classList.add('ring-4', 'ring-blue-300', 'ring-opacity-50');
        setTimeout(() => {
            rackSlot.classList.remove('ring-4', 'ring-blue-300', 'ring-opacity-50');
        }, 600);
    }
    
    animateCabinetOpen(cabinet) {
        // Set initial state
        cabinet.style.opacity = '0';
        cabinet.style.transform = 'scale(0.9) translateY(-20px)';
        cabinet.style.transition = 'all 0.3s ease-out';
        
        // Show the cabinet
        cabinet.classList.remove('hidden');
        
        // Animate to final state
        setTimeout(() => {
            cabinet.style.opacity = '1';
            cabinet.style.transform = 'scale(1) translateY(0)';
        }, 50);
        
        // Animate rack slots appearing
        setTimeout(() => {
            const rackSlots = cabinet.querySelectorAll('.rack-slot');
            rackSlots.forEach((slot, index) => {
                slot.style.opacity = '0';
                slot.style.transform = 'translateX(-20px)';
                slot.style.transition = 'all 0.3s ease-out';
                
                setTimeout(() => {
                    slot.style.opacity = '1';
                    slot.style.transform = 'translateX(0)';
                }, 200 + (index * 100));
            });
        }, 200);
    }
    
    getClickedArea(x, y) {
        // Find all areas that contain the click point
        const matchingAreas = this.areas.filter(area => 
            x >= area.bounds.x1 && 
            x <= area.bounds.x2 && 
            y >= area.bounds.y1 && 
            y <= area.bounds.y2
        );
        
        // If multiple areas match, return the one with the smallest area (most specific)
        if (matchingAreas.length > 1) {
            return matchingAreas.reduce((smallest, current) => {
                const currentSize = (current.bounds.x2 - current.bounds.x1) * (current.bounds.y2 - current.bounds.y1);
                const smallestSize = (smallest.bounds.x2 - smallest.bounds.x1) * (smallest.bounds.y2 - smallest.bounds.y1);
                return currentSize < smallestSize ? current : smallest;
            });
        }
        
        return matchingAreas[0];
    }
    
    showAreaDetails() {
        const cabinet = document.getElementById('cabinetVisualization');
        const cabinetName = document.getElementById('cabinetName');
        const cabinetDescription = document.getElementById('cabinetDescription');
        
        cabinetName.textContent = this.clickedArea.name || 'Storage Cabinet';
        cabinetDescription.textContent = this.clickedArea.description || 'Click on a rack to view details';
        
        // Update rack data with actual data from API if available
        this.updateRackData();
        
        // Animate cabinet appearance
        this.animateCabinetOpen(cabinet);
    }
    
    updateRackData() {
        // Use real data from the areas array loaded from API
        const rackData = {};
        
        // Group locations by rack letter
        this.areas.forEach(area => {
            const rackLetter = area.data.rack;
            if (rackLetter && ['A', 'B', 'C', 'D'].includes(rackLetter)) {
                if (!rackData[rackLetter]) {
                    rackData[rackLetter] = {
                        capacity: 0,
                        occupied: 0,
                        folders: []
                    };
                }
                
                rackData[rackLetter].capacity += area.data.capacity;
                rackData[rackLetter].occupied += area.data.occupied;
                rackData[rackLetter].folders.push(...area.data.documents);
            }
        });
        
        // Update each rack slot with real data
        ['A', 'B', 'C', 'D'].forEach(rackLetter => {
            const rackSlot = document.querySelector(`[data-rack="${rackLetter}"]`);
            if (rackSlot) {
                const data = rackData[rackLetter] || { capacity: 0, occupied: 0, folders: [] };
                const capacityEl = rackSlot.querySelector('.rack-capacity');
                const occupiedEl = rackSlot.querySelector('.rack-occupied');
                const occupancyBar = rackSlot.querySelector('.rack-occupancy-bar');
                
                capacityEl.textContent = data.capacity;
                occupiedEl.textContent = data.occupied;
                
                const percentage = data.capacity > 0 ? (data.occupied / data.capacity) * 100 : 0;
                occupancyBar.style.width = `${percentage}%`;
                
                // Update color based on occupancy
                if (percentage >= 90) {
                    occupancyBar.className = occupancyBar.className.replace(/bg-\w+-500/, 'bg-red-500');
                } else if (percentage >= 75) {
                    occupancyBar.className = occupancyBar.className.replace(/bg-\w+-500/, 'bg-yellow-500');
                } else {
                    occupancyBar.className = occupancyBar.className.replace(/bg-\w+-500/, 'bg-green-500');
                }
                
                // Store real folder data for later use
                rackSlot.dataset.folders = JSON.stringify(data.folders);
            }
        });
    }
    
    showRackDetails(rackLetter) {
        const rackSlot = document.querySelector(`[data-rack="${rackLetter}"]`);
        if (!rackSlot) return;
        
        const folders = JSON.parse(rackSlot.dataset.folders || '[]');
        this.selectedRack = rackLetter;
        
        // Clear and populate folders grid with animation
        const foldersGrid = document.getElementById('rackFoldersGrid');
        foldersGrid.innerHTML = '';
        
        if (folders.length === 0) {
            const emptyElement = document.createElement('div');
            emptyElement.className = 'text-center text-gray-500 py-8 opacity-0 transform translate-y-4';
            emptyElement.innerHTML = `
                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                </svg>
                <p class="text-sm">No folders in Rack ${rackLetter}</p>
            `;
            foldersGrid.appendChild(emptyElement);
            
            // Animate empty state appearance
            setTimeout(() => {
                emptyElement.classList.remove('opacity-0', 'translate-y-4');
                emptyElement.classList.add('opacity-100', 'translate-y-0', 'transition-all', 'duration-300');
            }, 50);
        } else {
            folders.forEach((folderCode, index) => {
                const folderElement = document.createElement('div');
                folderElement.className = 'bg-white p-3 rounded border border-gray-200 hover:shadow-md transition-shadow cursor-pointer opacity-0 transform translate-y-4';
                folderElement.innerHTML = `
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <svg class="w-4 h-4 text-blue-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <div>
                                <div class="font-semibold text-sm text-gray-900">${folderCode}</div>
                                <div class="text-xs text-gray-500">Rack ${rackLetter}</div>
                            </div>
                        </div>
                        <button class="text-xs text-blue-600 hover:text-blue-800 font-medium">View</button>
                    </div>
                `;
                foldersGrid.appendChild(folderElement);
                
                // Stagger animation for each folder
                setTimeout(() => {
                    folderElement.classList.remove('opacity-0', 'translate-y-4');
                    folderElement.classList.add('opacity-100', 'translate-y-0', 'transition-all', 'duration-300');
                }, 100 + (index * 100));
            });
        }
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
