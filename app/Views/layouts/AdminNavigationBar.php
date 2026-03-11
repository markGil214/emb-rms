<!-- Vertical Navigation Bar for Admin Dashboard -->
<nav x-data="{ 
    sidebarOpen: true,
    userDropdownOpen: false,
    notificationsOpen: false,
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
        });
    }
}" 
class="bg-gray-800 text-white h-screen fixed left-0 top-0 shadow-lg transition-all duration-300 z-30" 
:class="sidebarOpen ? 'w-64' : 'w-16'">
    
    <!-- Header Section -->
    <div class="p-4 border-b border-gray-200 bg-gray-50 text-black">
        <div class="flex items-center justify-between">
            <div x-show="sidebarOpen" x-transition class="flex items-center">
                <img src="/images/EMB-Logo.png" alt="EMB Records Logo" class="w-8 h-8 mr-3">
                <h2 class="text-xl font-bold">EMBRMS</h2>
            </div>
        </div>
    </div>
    
    <!-- Navigation Menu -->
    <ul class="p-4 space-y-2 h-screen overflow-y-hidden">
        <li>
            <a href="<?= url_to('dashboard') ?>" 
               class="flex items-center p-3 rounded hover:bg-gray-700 transition-colors group"
               :class="window.location.pathname === '<?= url_to('dashboard') ?>' ? 'bg-gray-700' : ''">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
                <span x-show="sidebarOpen" x-transition class="ml-3">Dashboard</span>
            </a>
        </li>
           <li>
            <a href="<?= route_to('shelfmap') ?>" class="flex items-center p-3 rounded hover:bg-gray-700 transition-colors group"
               :class="window.location.pathname === '<?= route_to('shelfmap') ?>' ? 'bg-gray-700' : ''">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <span x-show="sidebarOpen" x-transition class="ml-3">Shelf Map And Search</span>
            </a>
        </li>
        
        <li>
            <a href="#" class="flex items-center p-3 rounded hover:bg-gray-700 transition-colors group">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <span x-show="sidebarOpen" x-transition class="ml-3">Document Records</span>
            </a>
        </li>
        
        <li>
            <a href="#" class="flex items-center p-3 rounded hover:bg-gray-700 transition-colors group">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                </svg>
                <span x-show="sidebarOpen" x-transition class="ml-3">Borrow Queue</span>
            </a>
        </li>

        <li>
            <a href="#" class="flex items-center p-3 rounded hover:bg-gray-700 transition-colors group">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                </svg>
                <span x-show="sidebarOpen" x-transition class="ml-3">Relocation</span>
            </a>
        </li>

         <li>
            <a href="#" class="flex items-center p-3 rounded hover:bg-gray-700 transition-colors group">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                </svg>
                <span x-show="sidebarOpen" x-transition class="ml-3">Archive Modules</span>
            </a>
        </li>

              <li>
            <a href="#" class="flex items-center p-3 rounded hover:bg-gray-700 transition-colors group">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6a4 4 0 004 4h4a4 4 0 004-4v-6"></path>
                </svg>
                <span x-show="sidebarOpen" x-transition class="ml-3">Disposal Workflow</span>
            </a>
        </li>

            <li>
            <a href="#" class="flex items-center p-3 rounded hover:bg-gray-700 transition-colors group">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                </svg>
                <span x-show="sidebarOpen" x-transition class="ml-3">Alert Module</span>
            </a>
        </li>
    </ul>
    
    <!-- User Section -->
    <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-gray-700">
        <div class="flex items-center justify-between">
            <div x-show="sidebarOpen" x-transition class="flex items-center flex-1">
                <div class="w-8 h-8 bg-gray-600 rounded-full flex items-center justify-center mr-3">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium truncate"><?= auth_user()['username'] ?? 'Admin' ?></p>
                    <p class="text-xs text-gray-400 truncate">Administrator</p>
                </div>
            </div>
            
            <div class="relative user-dropdown">
                <button @click="userDropdownOpen = !userDropdownOpen" 
                        @click.away="userDropdownOpen = false"
                        class="p-2 hover:bg-gray-700 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                
                <div x-show="userDropdownOpen" 
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="transform opacity-0 scale-95"
                     x-transition:enter-end="transform opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="transform opacity-100 scale-100"
                     x-transition:leave-end="transform opacity-0 scale-95"
                     :class="sidebarOpen ? 'absolute bottom-full right-0 mb-2' : 'fixed bottom-4 left-20'"
                     class="w-48 bg-white rounded-lg shadow-lg py-1 z-50">
                    <a href="#" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        Profile
                    </a>
                    <a href="#" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        Settings
                    </a>
                    <hr class="my-1">
                    <a href="<?= url_to('logout') ?>" class="flex items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </div>
</nav>