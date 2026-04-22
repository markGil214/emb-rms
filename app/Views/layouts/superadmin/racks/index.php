<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div x-data="racksManager()">

    <!-- Action Bar -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-3">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <!-- Search Bar -->
                <div class="relative">
                    <input type="text" 
                           x-model="searchQuery"
                           @input="filterLocations()"
                           placeholder="Search racks or shelves..." 
                           class="w-64 pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <svg class="absolute left-3 top-2.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>

                <!-- Rack Filter -->
                <select x-model="rackFilter" @change="filterLocations()" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">All Racks</option>
                    <?php foreach ($racks as $rack): ?>
                        <option value="<?= esc($rack) ?>">Rack <?= esc($rack) ?></option>
                    <?php endforeach; ?>
                </select>

                <!-- Entries Per Page -->
                <select x-model="entriesPerPage" @change="updatePagination()" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="5">5</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                    <option value="">All</option>
                </select>
            </div>

            <div class="flex items-center space-x-3">
                <!-- Add Shelf Button -->
                <button type="button" onclick="openModal('addRackModal')" 
                        class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Add Shelf
                </button>
            </div>
        </div>
    </div>

<!-- Add Rack Modal -->
<div id="addRackModal" class="fixed inset-0 hidden items-center justify-center bg-gray-500 bg-opacity-50 p-4 z-50" x-data="{ open: false }">
    <div class="relative w-full max-w-md border border-gray-200 p-6 shadow-lg rounded-lg bg-white">
        <div>
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Add New Shelf</h3>
            <form method="post" action="<?= route_to('racks.store') ?>" data-confirm-message="Add this shelf to the selected rack?">
                <?= csrf_field() ?>
                <div class="mb-6">
                    <label for="rack" class="block text-sm font-medium text-gray-700 mb-2">Rack</label>
                    <select id="rack" name="rack" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white text-gray-900">
                        <option value="">Choose a rack</option>
                        <?php for ($i = 1; $i <= 20; $i++): ?>
                            <option value="<?= $i ?>" <?= old('rack') === (string) $i ? 'selected' : '' ?>>Rack <?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                    <?php if (! empty($errors['rack'])): ?>
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400"><?= esc($errors['rack']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="mb-6">
                    <label for="shelf" class="block text-sm font-medium text-gray-700 mb-2">Shelf</label>
                    <input id="shelf" type="text" name="shelf" value="<?= esc(old('shelf')) ?>" required maxlength="50" 
                           pattern="[A-Za-z0-9 ]+" title="Use letters, numbers, and spaces only."
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white text-gray-900">
                    <?php if (! empty($errors['shelf'])): ?>
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400"><?= esc($errors['shelf']) ?></p>
                    <?php endif; ?>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeModal('addRackModal')" 
                            class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors duration-200">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors duration-200">
                        Add Shelf
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Data Table -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
    <template x-if="filteredLocations.length === 0">
        <div class="p-12 text-center">
            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2">
                <span x-show="searchQuery || rackFilter">No matching locations found</span>
                <span x-show="!searchQuery && !rackFilter">No rack shelves found</span>
            </h3>
            <p class="text-gray-600 mb-4">
                <span x-show="searchQuery || rackFilter">Try adjusting your search or filter criteria</span>
                <span x-show="!searchQuery && !rackFilter">Get started by adding your first shelf</span>
            </p>
        </div>
    </template>

    <template x-if="filteredLocations.length > 0">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Rack</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Shelf</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Used</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <template x-for="(location, index) in paginatedLocations" :key="location.rack + '-' + location.shelf">
                        <tr class="hover:bg-gray-200 transition-colors duration-150" 
                            :class="index % 2 === 0 ? 'bg-white' : 'bg-gray-200'">
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center">
                                    <svg class="w-4 h-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                    </svg>
                                    <span class="text-sm font-medium text-gray-900" x-text="'Rack ' + location.rack"></span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="text-sm text-gray-900" x-text="'Shelf ' + location.shelf"></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800" x-text="location.current_count"></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <button class="text-blue-600 hover:text-blue-900 text-sm font-medium">Edit</button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </template>
</div>

<!-- Pagination -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mt-3" x-show="filteredLocations.length > 0 && entriesPerPage !== ''">
    <div class="flex items-center justify-between">
        <div class="text-sm text-gray-700">
            Showing <span x-text="startIndex + 1"></span> to <span x-text="endIndex"></span> of <span x-text="filteredLocations.length"></span> results
        </div>
        <div class="flex items-center space-x-2">
            <button @click="previousPage()" 
                    :disabled="currentPage === 1"
                    :class="currentPage === 1 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-100'"
                    class="px-3 py-1 border border-gray-300 rounded-md text-sm">
                Previous
            </button>
            <span class="px-3 py-1 text-sm text-gray-700">
                Page <span x-text="currentPage"></span> of <span x-text="totalPages"></span>
            </span>
            <button @click="nextPage()" 
                    :disabled="currentPage === totalPages"
                    :class="currentPage === totalPages ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-100'"
                    class="px-3 py-1 border border-gray-300 rounded-md text-sm">
                Next
            </button>
        </div>
    </div>
</div>

</div>

<script>
    function openModal(id) {
        var modal = document.getElementById(id);
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
    }

    function closeModal(id) {
        var modal = document.getElementById(id);
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    }

    document.addEventListener('click', function (event) {
        var modal = document.getElementById('addRackModal');
        if (modal && event.target === modal) {
            closeModal('addRackModal');
        }
    });

    document.addEventListener('click', function (event) {
        if (event.target.classList && event.target.classList.contains('modal-close')) {
            closeModal(event.target.getAttribute('data-modal'));
        }
    });

    <?php if (session()->getFlashdata('modal') === 'add-rack'): ?>
    openModal('addRackModal');
    <?php endif; ?>
</script>

<script>
function racksManager() {
    return {
        locations: <?= json_encode($locations ?? []) ?>,
        filteredLocations: [],
        searchQuery: '',
        rackFilter: '',
        entriesPerPage: '25',
        currentPage: 1,
        
        init() {
            this.filteredLocations = [...this.locations];
            this.updatePagination();
        },
        
        filterLocations() {
            this.filteredLocations = this.locations.filter(location => {
                const searchTerm = (this.searchQuery || '').toLowerCase().trim();
                const rackValue = String(location.rack || '').toLowerCase();
                const shelfValue = String(location.shelf || '').toLowerCase();
                const searchableText = `${rackValue} shelf ${shelfValue} rack ${rackValue} shelf ${shelfValue}`;
                const matchesSearch = !searchTerm || searchableText.includes(searchTerm);
                
                const matchesRack = !this.rackFilter || location.rack === this.rackFilter;
                
                return matchesSearch && matchesRack;
            });
            
            this.currentPage = 1;
            this.updatePagination();
        },
        
        get paginatedLocations() {
            if (this.entriesPerPage === '') {
                return this.filteredLocations;
            }
            
            const start = (this.currentPage - 1) * parseInt(this.entriesPerPage);
            const end = start + parseInt(this.entriesPerPage);
            return this.filteredLocations.slice(start, end);
        },
        
        updatePagination() {
            // Trigger reactivity
            this.$nextTick(() => {
                // Pagination will be recalculated automatically
            });
        },
        
        get totalPages() {
            if (this.entriesPerPage === '') {
                return 1;
            }
            return Math.ceil(this.filteredLocations.length / parseInt(this.entriesPerPage));
        },
        
        get startIndex() {
            if (this.entriesPerPage === '') {
                return 0;
            }
            return (this.currentPage - 1) * parseInt(this.entriesPerPage);
        },
        
        get endIndex() {
            if (this.entriesPerPage === '') {
                return this.filteredLocations.length;
            }
            const end = this.startIndex + parseInt(this.entriesPerPage);
            return Math.min(end, this.filteredLocations.length);
        },
        
        previousPage() {
            if (this.currentPage > 1) {
                this.currentPage--;
            }
        },
        
        nextPage() {
            if (this.currentPage < this.totalPages) {
                this.currentPage++;
            }
        }
    }
}
</script>

<?= $this->endSection() ?>
