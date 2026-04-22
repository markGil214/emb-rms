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
                <img src="/images/EMB-Logo.png" alt="EMB Records Logo" class="w-10 h-10 ml-9">
                <h2 x-show="sidebarOpen" x-transition class="text-11x1 font-bold ml-2">RMS</h2>
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
            <?php if (can('view_shelf_map')): ?>
            <a href="<?= route_to('shelfmap') ?>" class="flex items-center p-3 rounded hover:bg-green-900 transition-colors group"
               :class="window.location.pathname === '<?= route_to('shelfmap') ?>' ? 'bg-green-900' : ''">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
                <span x-show="sidebarOpen" x-transition class="ml-3">Shelf Map And Search</span>
            </a>
            <?php endif; ?>
        </li>
        
        <li>
                <?php if (can('view_documents') || can('create_document_record') || can('edit_document_metadata') || can('search_documents') || can('manage_racks') || can('manage_categories') || can('approve_folder_creation') || can('approve_folder_archival')): ?>
            <a href="<?= route_to('records') ?>" class="flex items-center p-3 rounded hover:bg-green-900 transition-colors group"
               :class="window.location.pathname === '<?= route_to('records') ?>' ? 'bg-green-900' : ''">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <span x-show="sidebarOpen" x-transition class="ml-3">Document Records</span>
            </a>
            <?php endif; ?>
        </li>
        
        <li>
            <?php if (can('request_borrow') || can('approve_borrow_requests') || can('process_borrow_release') || can('process_return')): ?>
            <a href="<?= route_to('borrows.index') ?>" class="flex items-center p-3 rounded hover:bg-green-900 transition-colors group"
               :class="window.location.pathname === '<?= route_to('borrows.index') ?>' ? 'bg-green-900' : ''">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                </svg>
                <span x-show="sidebarOpen" x-transition class="ml-3">Borrow Management</span>
            </a>
            <?php endif; ?>
        </li>

        <li>
            <?php if (can('request_relocation') || can('approve_relocation')): ?>
            <a href="<?= route_to('relocations.index') ?>" class="flex items-center p-3 rounded hover:bg-green-900 transition-colors group"
               :class="window.location.pathname === '<?= route_to('relocations.index') ?>' ? 'bg-green-900' : ''">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                </svg>
                <span x-show="sidebarOpen" x-transition class="ml-3">Relocation</span>
            </a>
            <?php endif; ?>
        </li>

         <li>
            <?php if (can('archive_document') || can('view_archive_module') || can('manage_archive_policies') || can('view_disposal_workflow') || can('approve_disposal')): ?>
            <a href="<?= route_to('archive.index') ?>" class="flex items-center p-3 rounded hover:bg-green-900 transition-colors group"
               :class="window.location.pathname === '<?= route_to('archive.index') ?>' ? 'bg-green-900' : ''">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                </svg>
                <span x-show="sidebarOpen" x-transition class="ml-3">Archive and Disposal</span>
            </a>
            <?php endif; ?>
        </li>

        <li>
            <?php if (can('manage_users')): ?>
            <a href="<?= route_to('admin.permissions') ?>" class="flex items-center p-3 rounded hover:bg-green-900 transition-colors group">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                <span x-show="sidebarOpen" x-transition class="ml-3">User Permission</span>
            </a>
            <?php endif; ?>
        </li>
    </ul>
</div>
</nav>