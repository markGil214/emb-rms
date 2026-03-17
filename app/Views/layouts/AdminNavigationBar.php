<!-- Vertical Navigation Bar for Admin Dashboard -->
<nav x-data="{ 
    sidebarOpen: true,
    userDropdownOpen: false,
    notificationsOpen: false,
    documentDropdownOpen: false,
    archiveDropdownOpen: false,
    init() {
        // Initialize sidebar state from localStorage
        this.sidebarOpen = localStorage.getItem('sidebarOpen') !== 'false';
        // Close dropdowns when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.user-dropdown')) {
                this.userDropdownOpen = false;
            }
            if (!e.target.closest('.notifications-dropdown')) {
                this.notificationsOpen = false;
            }
            if (!e.target.closest('.document-dropdown')) {
                this.documentDropdownOpen = false;
            }
            if (!e.target.closest('.archive-dropdown')) {
                this.archiveDropdownOpen = false;
            }
        });
    }
}" 
class="bg-green-700 text-white min-h-screen fixed left-0 top-0 shadow-lg transition-all duration-300 z-30" 
:class="sidebarOpen ? 'w-64' : 'w-26'">
    
    <!-- Navigation Menu -->
    <ul class="p-4 space-y-2">
        <!-- Header Item -->
        <li class="bg-green-700 text-white mb-4">
            <div class="flex items-center ml-7">
                <img src="/images/EMB-Logo.png" alt="EMB Records Logo" class="w-10 h-10 ml-4">
                <h2 x-show="sidebarOpen" x-transition class="text-10x1 font-bold ml-2">EMBRMS</h2>
            </div>
            <hr class ="mt-4">
        </li>
        <li>
            <a href="<?= route_to('dashboard') ?>" 
               class="flex items-center p-3 rounded hover:bg-green-900 transition-colors group"
               :class="window.location.pathname === '<?= route_to('dashboard') ?>' ? 'bg-green-900' : ''">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
                <span x-show="sidebarOpen" x-transition class="ml-3">Dashboard</span>
            </a>
        </li>
        <li>
            <a href="<?= route_to('shelfmap') ?>" class="flex items-center p-3 rounded hover:bg-green-900 transition-colors group"
               :class="window.location.pathname === '<?= route_to('shelfmap') ?>' ? 'bg-green-900' : ''">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
                <span x-show="sidebarOpen" x-transition class="ml-3">Shelf Map And Search</span>
            </a>
        </li>
        
        <li class="document-dropdown">
            <button @click="documentDropdownOpen = !documentDropdownOpen" 
                    class="w-full flex items-center p-3 rounded hover:bg-green-900 transition-colors group text-left">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <span x-show="sidebarOpen" x-transition class="ml-3 flex-1">Document Records</span>
                <svg x-show="sidebarOpen" x-transition class="w-4 h-4 transition-transform" 
                     :class="documentDropdownOpen ? 'rotate-90' : ''" 
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </button>
            
            <div x-show="documentDropdownOpen" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="transform opacity-0 scale-95"
                 x-transition:enter-end="transform opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-75"
                 x-transition:leave-start="transform opacity-100 scale-100"
                 x-transition:leave-end="transform opacity-0 scale-95"
                 class="mt-1 ml-3 space-y-1 bg-green-900 rounded-lg">
                <a href="<?= route_to('records') ?>" class="flex items-center p-2 rounded hover:bg-green-600 transition-colors group">
                    
                    <span x-show="sidebarOpen" x-transition>Permits</span>
                </a>
                <a href="<?= route_to('records') ?>" class="flex items-center p-2 rounded hover:bg-green-600 transition-colors group">
                    
                    <span x-show="sidebarOpen" x-transition>ECC & IEC</span>
                </a>
                <a href="<?= route_to('records') ?>" class="flex items-center p-2 rounded hover:bg-green-600 transition-colors group">
                    
                    <span x-show="sidebarOpen" x-transition>CNC</span>
                </a>
            </div>
        </li>
        
        <li>
            <a href="#" class="flex items-center p-3 rounded hover:bg-green-900 transition-colors group">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                </svg>
                <span x-show="sidebarOpen" x-transition class="ml-3">Borrow Queue</span>
            </a>
        </li>

        <li>
            <a href="#" class="flex items-center p-3 rounded hover:bg-green-900 transition-colors group">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                </svg>
                <span x-show="sidebarOpen" x-transition class="ml-3">Relocation</span>
            </a>
        </li>

         <li class="archive-dropdown">
            <button @click="archiveDropdownOpen = !archiveDropdownOpen" 
                    class="w-full flex items-center p-3 rounded hover:bg-green-900 transition-colors group text-left">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                </svg>
                <span x-show="sidebarOpen" x-transition class="ml-3 flex-1">Archive Modules</span>
                <svg x-show="sidebarOpen" x-transition class="w-4 h-4 transition-transform" 
                     :class="archiveDropdownOpen ? 'rotate-90' : ''" 
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </button>
            
            <div x-show="archiveDropdownOpen" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="transform opacity-0 scale-95"
                 x-transition:enter-end="transform opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-75"
                 x-transition:leave-start="transform opacity-100 scale-100"
                 x-transition:leave-end="transform opacity-0 scale-95"
                 class="mt-1 ml-3 space-y-1  bg-green-900 rounded-lg">
                <a href="<?= route_to('#') ?>" class="flex items-center p-2 rounded hover:bg-green-600 transition-colors group">
                    
                    <span x-show="sidebarOpen" x-transition>Permits</span>
                </a>
                <a href="#" class="flex items-center p-2 rounded hover:bg-green-600 transition-colors group">
                   
                    <span x-show="sidebarOpen" x-transition>ECC & IEC</span>
                </a>
                <a href="#" class="flex items-center p-2 rounded hover:bg-green-600 transition-colors group">
                    
                    <span x-show="sidebarOpen" x-transition>CNC Archive</span>
                </a>
            </div>
        </li>

              <li>
            <a href="#" class="flex items-center p-3 rounded hover:bg-green-900 transition-colors group">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6a4 4 0 004 4h4a4 4 0 004-4v-6"></path>
                </svg>
                <span x-show="sidebarOpen" x-transition class="ml-3">Disposal Workflow</span>
            </a>
        </li>

            <li>
            <a href="#" class="flex items-center p-3 rounded hover:bg-green-900 transition-colors group">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                </svg>
                <span x-show="sidebarOpen" x-transition class="ml-3">Alert Module</span>
            </a>
        </li>
    </ul>
</div>
</nav>