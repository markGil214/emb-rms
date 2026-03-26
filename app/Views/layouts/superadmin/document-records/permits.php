<?= $this->extend('layouts/main') ?>



<?= $this->section('content') ?>

<div x-data="permitsManager()">

    <!-- Page Header -->

    <div class="mb-8">

        <h1 class="text-3xl font-bold text-gray-900 mb-2">Document Records - Permits</h1>

        <p class="text-gray-600">Manage and track all permit documents in the system</p>

    </div>



    <!-- Action Bar -->

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-3">

        <div class="flex items-center justify-between">

            <div class="flex items-center space-x-4">

                <!-- Search Bar -->

                <div class="relative">

                    <input type="text" 

                           x-model="searchQuery"

                           @input="filterFolders()"

                           placeholder="Search folders..." 

                           class="w-64 pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">

                    <svg class="absolute left-3 top-2.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>

                    </svg>

                </div>

                

                <!-- Filter Dropdown -->

                <select x-model="statusFilter" @change="filterFolders()" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">

                    <option value="">All Status</option>

                    <option value="available">Available</option>

                    <option value="borrowed">Borrowed</option>

                    <option value="archived">Archived</option>

                </select>

                

                <!-- Folder Type Filter -->

                <select x-model="folderTypeFilter" @change="filterFolders()" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">

                    <option value="">All Types</option>

                    <option value="Commercial sand and gravel">Commercial sand and gravel</option>

                    <option value="Telecommunication">Telecommunication</option>

                    <option value="Local Government Unit">Local Government Unit</option>

                    <option value="Mining Company">Mining Company</option>

                    <option value="Hydro Power Plants">Hydro Power Plants</option>

                </select>

                

                <!-- Entries Per Page -->

                <select x-model="entriesPerPage" @change="updatePagination()" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">

                    <option value="25">25</option>

                    <option value="50">50</option>

                    <option value="100">100</option>

                    <option value="">All</option>

                </select>

            </div>

            

            <!-- Create Button -->

            <a href="<?= route_to('records.create') ?>" 

               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg inline-flex items-center space-x-2 transition-colors duration-200">

                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>

                </svg>

                <span>Create Folder</span>

            </a>

        </div>

    </div>



    <!-- Data Table -->

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">

        <template x-if="filteredFolders.length === 0">

            <div class="p-12 text-center">

                <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>

                </svg>

                <h3 class="text-lg font-medium text-gray-900 mb-2">

                    <span x-show="searchQuery || statusFilter">No matching folders found</span>

                    <span x-show="!searchQuery && !statusFilter">No folders found</span>

                </h3>

                <p class="text-gray-600 mb-4">

                    <span x-show="searchQuery || statusFilter">Try adjusting your search or filter criteria</span>

                    <span x-show="!searchQuery && !statusFilter">Get started by creating your first folder</span>

                </p>

                <a x-show="!searchQuery && !statusFilter" href="<?= route_to('records.create') ?>" 

                   class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg inline-flex items-center space-x-2 transition-colors duration-200">

                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>

                    </svg>

                    <span>Create Folder</span>

                </a>

            </div>

        </template>

        

        <template x-if="filteredFolders.length > 0">

            <div class="overflow-x-auto">

                <table class="min-w-full divide-y divide-gray-200">

                    <thead class="bg-gray-50">

                        <tr>

                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">

                                File Code

                            </th>

                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">

                                Location Code

                            </th>

                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">

                                Company

                            </th>

                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">

                                Folder Type

                            </th>

                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">

                                Date

                            </th>

                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">

                                Status

                            </th>

                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">

                                Actions

                            </th>

                        </tr>

                    </thead>

                    <tbody class="bg-white divide-y divide-gray-200">

                        <template x-for="(folder, index) in filteredFolders" :key="folder.folder_id">

                            <tr class="hover:bg-gray-200 transition-colors duration-150" 
                                :class="index % 2 === 0 ? 'bg-white' : 'bg-gray-200'">

                                <td class="px-6 py-4 whitespace-nowrap">

                                    <div class="flex items-center">

                                        <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>

                                        </svg>

                                        <span class="text-sm font-medium text-gray-900" x-text="folder.file_code"></span>

                                    </div>

                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">

                                    <div class="flex items-center">

                                        <svg class="w-4 h-4 text-gray-400 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>

                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>

                                        </svg>

                                        <span class="text-sm text-gray-900" x-text="folder.location_code"></span>

                                    </div>

                                </td>

                                <td class="px-6 py-4">

                                    <div class="text-sm text-gray-900" x-text="folder.company_name"></div>

                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">

                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800"

                                          x-text="folder.folder_type || 'Not Set'"></span>

                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">

                                    <div class="text-sm text-gray-500">

                                        <div>Issued: <span x-text="formatDate(folder.issuance_date)"></span></div>

                                        <div>Expires: <span x-text="formatDate(folder.expiry_date)"></span></div>

                                    </div>

                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">

                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"

                                          :class="getStatusClass(folder.status)">

                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">

                                            <path x-show="folder.status === 'Available'" fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>

                                            <path x-show="folder.status === 'Archived'" d="M4 3a2 2 0 100 4h12a2 2 0 100-4H4z"></path>

                                            <path x-show="folder.status === 'Archived'" fill-rule="evenodd" d="M3 8h14v7a2 2 0 01-2 2H5a2 2 0 01-2-2V8zm5 3a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z" clip-rule="evenodd"></path>

                                            <path x-show="folder.status !== 'Available' && folder.status !== 'Archived'" fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>

                                        </svg>

                                        <span x-text="folder.status"></span>

                                    </span>

                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">

                                    <div class="flex items-center justify-end space-x-2">

                                        <a :href="`/permits/${folder.folder_id}`" 

                                           class="text-blue-600 hover:text-blue-900 inline-flex items-center space-x-1"

                                           title="View Details">

                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>

                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>

                                            </svg>

                                            <span>View</span>

                                        </a>

                                        <a :href="`/permits/${folder.folder_id}/edit`" 

                                           class="text-green-600 hover:text-green-900 inline-flex items-center space-x-1"

                                           title="Edit Permit">

                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>

                                            </svg>

                                            <span>Edit</span>

                                        </a>

                                        <div class="flex items-center space-x-4">

                                            <a href="#" 

                                               class="text-green-600 hover:text-green-900 inline-flex items-center space-x-2">

                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>

                                                </svg>

                                                <span>Archive</span>

                                            </a>

                                        </div>

                                    </div>

                                </td>

                            </tr>

                        </template>

                    </tbody>

                </table>

            </div>

        </template>

    </div>

    

    <!-- Pagination Controls -->

    <div x-show="entriesPerPage !== '' && filteredFolders.length > 0" class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mt-4">

        <div class="flex items-center justify-between">

            <div class="text-sm text-gray-700">

                Showing 

                <span x-show="entriesPerPage === ''" x-text="filteredFolders.length"></span>

                <span x-show="entriesPerPage !== ''" x-text="Math.min(filteredFolders.length, entriesPerPage)"></span>

                of 

                <span x-text="filteredFolders.length"></span>

                entries

            </div>

            

            <div class="flex items-center space-x-2">

                <!-- Previous Button -->

                <button @click="previousPage()" 

                        x-show="currentPage > 1"

                        class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">

                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7 7m0 14l5-5 5-5z"></path>

                    </svg>

                    Previous

                </button>

                

                <!-- Page Numbers -->

                <div class="flex items-center space-x-1">

                    <template x-for="page in totalPages" :key="page">

                        <button @click="goToPage(page)"

                                :class="page === currentPage ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50'"

                                class="px-3 py-2 text-sm font-medium rounded-md">

                            <span x-text="page"></span>

                        </button>

                    </template>

                </div>

                

                <!-- Next Button -->

                <button @click="nextPage()" 

                        x-show="currentPage < totalPages"

                        class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">

                    Next

                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7m0 14l5-5 5-5z"></path>

                    </svg>

                </button>

            </div>

        </div>

    </div>

</div>



<script>

function permitsManager() {

    return {

        searchQuery: '',

        statusFilter: '',

        folderTypeFilter: '',

        entriesPerPage: '25',

        currentPage: 1,

        allFolders: <?= json_encode($folders ?? []) ?>,

        filteredFolders: <?= json_encode($folders ?? []) ?>,

        

        get totalPages() {

            if (this.entriesPerPage === '') {

                return 1;

            }

            return Math.ceil(this.filteredFolders.length / parseInt(this.entriesPerPage));

        },

        

        get paginatedFolders() {

            if (this.entriesPerPage === '') {

                return this.filteredFolders;

            }

            const start = (this.currentPage - 1) * parseInt(this.entriesPerPage);

            const end = start + parseInt(this.entriesPerPage);

            return this.filteredFolders.slice(start, end);

        },

        

        filterFolders() {

            this.currentPage = 1;

            this.filteredFolders = this.allFolders.filter(folder => {

                const matchesSearch = !this.searchQuery || 

                    folder.file_code.toLowerCase().includes(this.searchQuery.toLowerCase()) ||

                    folder.location_code.toLowerCase().includes(this.searchQuery.toLowerCase()) ||

                    folder.company_name.toLowerCase().includes(this.searchQuery.toLowerCase());

                

                const matchesStatus = !this.statusFilter || 

                    folder.status.toLowerCase() === this.statusFilter.toLowerCase();

                

                const matchesFolderType = !this.folderTypeFilter || 

                    folder.folder_type === this.folderTypeFilter;

                

                return matchesSearch && matchesStatus && matchesFolderType;

            }).sort((a, b) => a.file_code.localeCompare(b.file_code));

        },

        

        updatePagination() {

            this.currentPage = 1;

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

        },

        

        goToPage(page) {

            this.currentPage = page;

        },

        

        getStatusClass(status) {

            switch(status) {

                case 'Available':

                    return 'bg-green-100 text-green-800';

                case 'Archived':

                    return 'bg-gray-100 text-gray-800';

                default:

                    return 'bg-yellow-100 text-yellow-800';

            }

        },

        

        formatDate(dateString) {

            const date = new Date(dateString);

            return date.toLocaleDateString('en-US', { 

                year: 'numeric', 

                month: 'short', 

                day: 'numeric' 

            });

        }

    }

}

</script>



<?= $this->endSection() ?>