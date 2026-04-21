    <?= $this->extend('layouts/main') ?>

<style>
#rmsLayoutImage {
    cursor: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M12 2C10 2 8 4 8 6S10 10 12 10 16 8 16 6 14 4 12 2zm0 8c-1 0-2-1-2-2s1-2 2-2 2 1 2 2-1 2-2 2z" fill="%234ade80"/><path d="M12 10c-2 0-4 2-4 4s2 4 4 4 4-2 4-4-2-4-4-4z" fill="%2322c55e"/><path d="M8 14c-1 0-2 1-2 2s1 2 2 2 2-1 2-2-1-2-2-2z" fill="%2316a34a"/><path d="M16 14c-1 0-2 1-2 2s1 2 2 2 2-1 2-2-1-2-2-2z" fill="%2316a34a"/><circle cx="12" cy="12" r="1" fill="%23fbbf24"/></svg>') 12 12, auto;
}
</style>



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



        

        <!-- RMS Layout Image -->

        <div class="p-4 bg-gray-100">

            <div class="bg-white rounded-lg shadow p-4">

                <div class="mb-3 text-center">

                    <h3 class="text-base font-semibold text-gray-900">Records Layout Map</h3>

                    <p class="text-xs text-gray-600">Document storage cabinet visualization</p>

                </div>

                

                <!-- Interactive Layout Image -->

                <div class="relative bg-gray-50 rounded p-4">

                    <div class="flex justify-center relative" style="max-width: 100%; margin: 0 auto;">

                        <div class="relative" style="width: 100%; max-width: 800px;">

                            <img 

                                id="rmsLayoutImage"

                                src="<?= base_url('images/RECORDS-LAYOUT-MAP.png') ?>" 

                                alt="Records Layout Map" 

                                class="w-full h-auto rounded-lg shadow-md"

                                style="max-height: 500px; object-fit: contain;"

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

                        <!-- Brenda Profile Chatbox (x=32, y=35) -->
                        <div id="brendaChatbox" class="absolute hidden z-50 cursor-pointer" style="left: 32%; top: 35%; transform: translate(-50%, -50%);" onclick="showBrendaProfile()">
                            <div class="bg-white rounded-lg shadow-lg border border-gray-200 p-1 w-16 h-16">
                                <!-- Profile Header -->
                                <div class="flex flex-col items-center">
                                    <div class="w-6 h-6 bg-gradient-to-br from-purple-500 to-pink-500 rounded-full flex items-center justify-center text-white font-bold text-xs shadow mb-1">
                                        B
                                    </div>
                                    <h4 class="font-bold text-gray-900 text-[10px] text-center mx-1 mb-1">Ma'am Brenda</h4>
                                    
                                    <!-- Status Indicator -->
                                    <div class="flex items-center justify-center">
                                        <div class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Arlene Profile Chatbox (x=33, y=14) -->
                        <div id="arleneChatbox" class="absolute hidden z-50 cursor-pointer" style="left: 33%; top: 14%; transform: translate(-50%, -50%);" onclick="showArleneProfile()">
                            <div class="bg-white rounded-lg shadow-lg border border-gray-200 p-1 w-16 h-16">
                                <!-- Profile Header -->
                                <div class="flex flex-col items-center">
                                    <div class="w-6 h-6 bg-gradient-to-br from-orange-500 to-red-500 rounded-full flex items-center justify-center text-white font-bold text-xs shadow mb-1">
                                        A
                                    </div>
                                    <h4 class="font-bold text-gray-900 text-[10px] text-center mx-1 mb-1">Ma'am Arlene</h4>
                                    
                                    <!-- Status Indicator -->
                                    <div class="flex items-center justify-center">
                                        <div class="w-1.5 h-1.5 bg-yellow-500 rounded-full animate-pulse"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Liza Profile Chatbox (x=12, y=70) -->
                        <div id="lizaChatbox" class="absolute hidden z-50 cursor-pointer" style="left: 12%; top: 70%; transform: translate(-50%, -50%);" onclick="showLizaProfile()">
                            <div class="bg-white rounded-lg shadow-lg border border-gray-200 p-1 w-16 h-16">
                                <!-- Profile Header -->
                                <div class="flex flex-col items-center">
                                    <div class="w-6 h-6 bg-gradient-to-br from-teal-500 to-cyan-500 rounded-full flex items-center justify-center text-white font-bold text-xs shadow mb-1">
                                        L
                                    </div>
                                    <h4 class="font-bold text-gray-900 text-[10px] text-center mx-1 mb-1">Ma'am Liza</h4>
                                    
                                    <!-- Status Indicator -->
                                    <div class="flex items-center justify-center">
                                        <div class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Target Coordinate Marker (39, 68) - Cabinet 1 -->
                        <div id="targetMarker1" class="absolute pointer-events-none" style="left: 39%; top: 68%; transform: translate(-50%, -50%);">
                            <div class="relative">
                                <div class="w-6 h-6 bg-white rounded-full animate-pulse opacity-70"></div>
                                <div class="absolute inset-0 w-6 h-6 bg-yellow-400 rounded-full animate-ping opacity-30"></div>
                                <div class="absolute inset-0 flex items-center justify-center text-xs font-bold text-gray-800">1</div>
                            </div>
                        </div>

                        <!-- Target Coordinate Marker (43, 68) - Cabinet 2 -->
                        <div id="targetMarker2" class="absolute pointer-events-none" style="left: 43%; top: 68%; transform: translate(-50%, -50%);">
                            <div class="relative">
                                <div class="w-6 h-6 bg-white rounded-full animate-pulse opacity-70"></div>
                                <div class="absolute inset-0 w-6 h-6 bg-yellow-400 rounded-full animate-ping opacity-30"></div>
                                <div class="absolute inset-0 flex items-center justify-center text-xs font-bold text-gray-800">2</div>
                            </div>
                        </div>

                        <!-- Target Coordinate Marker (49, 68) - Cabinet 3 -->
                        <div id="targetMarker3" class="absolute pointer-events-none" style="left: 49%; top: 68%; transform: translate(-50%, -50%);">
                            <div class="relative">
                                <div class="w-6 h-6 bg-white rounded-full animate-pulse opacity-70"></div>
                                <div class="absolute inset-0 w-6 h-6 bg-yellow-400 rounded-full animate-ping opacity-30"></div>
                                <div class="absolute inset-0 flex items-center justify-center text-xs font-bold text-gray-800">3</div>
                            </div>
                        </div>

                        <!-- Target Coordinate Marker (53, 68) - Cabinet 4 -->
                        <div id="targetMarker4" class="absolute pointer-events-none" style="left: 53%; top: 68%; transform: translate(-50%, -50%);">
                            <div class="relative">
                                <div class="w-6 h-6 bg-white rounded-full animate-pulse opacity-70"></div>
                                <div class="absolute inset-0 w-6 h-6 bg-yellow-400 rounded-full animate-ping opacity-30"></div>
                                <div class="absolute inset-0 flex items-center justify-center text-xs font-bold text-gray-800">4</div>
                            </div>
                        </div>

                        <!-- Target Coordinate Marker (59, 68) - Cabinet 5 -->
                        <div id="targetMarker5" class="absolute pointer-events-none" style="left: 59%; top: 68%; transform: translate(-50%, -50%);">
                            <div class="relative">
                                <div class="w-6 h-6 bg-white rounded-full animate-pulse opacity-70"></div>
                                <div class="absolute inset-0 w-6 h-6 bg-yellow-400 rounded-full animate-ping opacity-30"></div>
                                <div class="absolute inset-0 flex items-center justify-center text-xs font-bold text-gray-800">5</div>
                            </div>
                        </div>

                        <!-- Target Coordinate Marker (63, 68) - Cabinet 6 -->
                        <div id="targetMarker6" class="absolute pointer-events-none" style="left: 63%; top: 68%; transform: translate(-50%, -50%);">
                            <div class="relative">
                                <div class="w-6 h-6 bg-white rounded-full animate-pulse opacity-70"></div>
                                <div class="absolute inset-0 w-6 h-6 bg-yellow-400 rounded-full animate-ping opacity-30"></div>
                                <div class="absolute inset-0 flex items-center justify-center text-xs font-bold text-gray-800">6</div>
                            </div>
                        </div>

                        <!-- Target Coordinate Marker (69, 68) - Cabinet 7 -->
                        <div id="targetMarker7" class="absolute pointer-events-none" style="left: 69%; top: 68%; transform: translate(-50%, -50%);">
                            <div class="relative">
                                <div class="w-6 h-6 bg-white rounded-full animate-pulse opacity-70"></div>
                                <div class="absolute inset-0 w-6 h-6 bg-yellow-400 rounded-full animate-ping opacity-30"></div>
                                <div class="absolute inset-0 flex items-center justify-center text-xs font-bold text-gray-800">7</div>
                            </div>
                        </div>

                        <!-- Target Coordinate Marker (73, 68) - Cabinet 8 -->
                        <div id="targetMarker8" class="absolute pointer-events-none" style="left: 73%; top: 68%; transform: translate(-50%, -50%);">
                            <div class="relative">
                                <div class="w-6 h-6 bg-white rounded-full animate-pulse opacity-70"></div>
                                <div class="absolute inset-0 w-6 h-6 bg-yellow-400 rounded-full animate-ping opacity-30"></div>
                                <div class="absolute inset-0 flex items-center justify-center text-xs font-bold text-gray-800">8</div>
                            </div>
                        </div>

                        <!-- Target Coordinate Marker (79, 64) - Cabinet 9 -->
                        <div id="targetMarker9" class="absolute pointer-events-none" style="left: 79%; top: 64%; transform: translate(-50%, -50%);">
                            <div class="relative">
                                <div class="w-6 h-6 bg-white rounded-full animate-pulse opacity-70"></div>
                                <div class="absolute inset-0 w-6 h-6 bg-red-400 rounded-full animate-ping opacity-30"></div>
                                <div class="absolute inset-0 flex items-center justify-center text-xs font-bold text-gray-800">9</div>
                            </div>
                        </div>

                        <!-- Target Coordinate Marker (83, 64) - Cabinet 10 -->
                        <div id="targetMarker10" class="absolute pointer-events-none" style="left: 83%; top: 64%; transform: translate(-50%, -50%);">
                            <div class="relative">
                                <div class="w-6 h-6 bg-white rounded-full animate-pulse opacity-70"></div>
                                <div class="absolute inset-0 w-6 h-6 bg-red-400 rounded-full animate-ping opacity-30"></div>
                                <div class="absolute inset-0 flex items-center justify-center text-xs font-bold text-gray-800">10</div>
                            </div>
                        </div>

                        <!-- Target Coordinate Marker (89, 64) - Cabinet 11 -->
                        <div id="targetMarker11" class="absolute pointer-events-none" style="left: 89%; top: 64%; transform: translate(-50%, -50%);">
                            <div class="relative">
                                <div class="w-6 h-6 bg-white rounded-full animate-pulse opacity-70"></div>
                                <div class="absolute inset-0 w-6 h-6 bg-red-400 rounded-full animate-ping opacity-30"></div>
                                <div class="absolute inset-0 flex items-center justify-center text-xs font-bold text-gray-800">11</div>
                            </div>
                        </div>

                        <!-- Target Coordinate Marker (93, 64) - Cabinet 12 -->
                        <div id="targetMarker12" class="absolute pointer-events-none" style="left: 93%; top: 64%; transform: translate(-50%, -50%);">
                            <div class="relative">
                                <div class="w-6 h-6 bg-white rounded-full animate-pulse opacity-70"></div>
                                <div class="absolute inset-0 w-6 h-6 bg-red-400 rounded-full animate-ping opacity-30"></div>
                                <div class="absolute inset-0 flex items-center justify-center text-xs font-bold text-gray-800">12</div>
                            </div>
                        </div>

                        <!-- Target Coordinate Marker (38, 36) - Cabinet 13 -->
                        <div id="targetMarker13" class="absolute pointer-events-none" style="left: 38%; top: 36%; transform: translate(-50%, -50%);">
                            <div class="relative">
                                <div class="w-6 h-6 bg-white rounded-full animate-pulse opacity-70"></div>
                                <div class="absolute inset-0 w-6 h-6 bg-blue-400 rounded-full animate-ping opacity-30"></div>
                                <div class="absolute inset-0 flex items-center justify-center text-xs font-bold text-gray-800">13</div>
                            </div>
                        </div>

                        <!-- Target Coordinate Marker (46, 29) - Cabinet 14 -->
                        <div id="targetMarker14" class="absolute pointer-events-none" style="left: 46%; top: 29%; transform: translate(-50%, -50%);">
                            <div class="relative">
                                <div class="w-6 h-6 bg-white rounded-full animate-pulse opacity-70"></div>
                                <div class="absolute inset-0 w-6 h-6 bg-blue-400 rounded-full animate-ping opacity-30"></div>
                                <div class="absolute inset-0 flex items-center justify-center text-xs font-bold text-gray-800">14</div>
                            </div>
                        </div>

                        <!-- Target Coordinate Marker (56, 29) - Cabinet 15 -->
                        <div id="targetMarker15" class="absolute pointer-events-none" style="left: 56%; top: 29%; transform: translate(-50%, -50%);">
                            <div class="relative">
                                <div class="w-6 h-6 bg-white rounded-full animate-pulse opacity-70"></div>
                                <div class="absolute inset-0 w-6 h-6 bg-blue-400 rounded-full animate-ping opacity-30"></div>
                                <div class="absolute inset-0 flex items-center justify-center text-xs font-bold text-gray-800">15</div>
                            </div>
                        </div>

                        <!-- Target Coordinate Marker (67, 29) - Cabinet 16 -->
                        <div id="targetMarker16" class="absolute pointer-events-none" style="left: 67%; top: 29%; transform: translate(-50%, -50%);">
                            <div class="relative">
                                <div class="w-6 h-6 bg-white rounded-full animate-pulse opacity-70"></div>
                                <div class="absolute inset-0 w-6 h-6 bg-blue-400 rounded-full animate-ping opacity-30"></div>
                                <div class="absolute inset-0 flex items-center justify-center text-xs font-bold text-gray-800">16</div>
                            </div>
                        </div>

                        <!-- Target Coordinate Marker (75, 29) - Cabinet 17 -->
                        <div id="targetMarker17" class="absolute pointer-events-none" style="left: 75%; top: 29%; transform: translate(-50%, -50%);">
                            <div class="relative">
                                <div class="w-6 h-6 bg-white rounded-full animate-pulse opacity-70"></div>
                                <div class="absolute inset-0 w-6 h-6 bg-blue-400 rounded-full animate-ping opacity-30"></div>
                                <div class="absolute inset-0 flex items-center justify-center text-xs font-bold text-gray-800">17</div>
                            </div>
                        </div>

                        <!-- Target Coordinate Marker (81, 29) - Cabinet 18 -->
                        <div id="targetMarker18" class="absolute pointer-events-none" style="left: 81%; top: 29%; transform: translate(-50%, -50%);">
                            <div class="relative">
                                <div class="w-6 h-6 bg-white rounded-full animate-pulse opacity-70"></div>
                                <div class="absolute inset-0 w-6 h-6 bg-blue-400 rounded-full animate-ping opacity-30"></div>
                                <div class="absolute inset-0 flex items-center justify-center text-xs font-bold text-gray-800">18</div>
                            </div>
                        </div>

                        <!-- Target Coordinate Marker (87, 29) - Cabinet 19 -->
                        <div id="targetMarker19" class="absolute pointer-events-none" style="left: 87%; top: 29%; transform: translate(-50%, -50%);">
                            <div class="relative">
                                <div class="w-6 h-6 bg-white rounded-full animate-pulse opacity-70"></div>
                                <div class="absolute inset-0 w-6 h-6 bg-blue-400 rounded-full animate-ping opacity-30"></div>
                                <div class="absolute inset-0 flex items-center justify-center text-xs font-bold text-gray-800">19</div>
                            </div>
                        </div>

                        <!-- Target Coordinate Marker (93, 29) - Cabinet 20 -->
                        <div id="targetMarker20" class="absolute pointer-events-none" style="left: 93%; top: 29%; transform: translate(-50%, -50%);">
                            <div class="relative">
                                <div class="w-6 h-6 bg-white rounded-full animate-pulse opacity-70"></div>
                                <div class="absolute inset-0 w-6 h-6 bg-blue-400 rounded-full animate-ping opacity-30"></div>
                                <div class="absolute inset-0 flex items-center justify-center text-xs font-bold text-gray-800">20</div>
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

                        <h4 id="cabinetName" class="font-bold text-lg text-gray-900 text-center">Cabinet Details</h4>

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

                                <!-- Shelf A -->

                                <div class="rack-slot bg-blue-100 border-2 border-blue-300 rounded-lg p-3 cursor-pointer hover:bg-blue-200 transition-all duration-200 hover:shadow-lg" data-rack="A">

                                    <div class="flex items-center justify-between">

                                        <div class="text-lg font-bold text-blue-800">Shelf A</div>

                                        <div class="text-sm text-gray-600">

                                            <span class="rack-occupied">0</span>/<span class="rack-capacity">100</span>

                                        </div>

                                    </div>

                                    <div class="w-full bg-gray-300 rounded-full h-2 mt-2">

                                        <div class="rack-occupancy-bar bg-blue-500 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>

                                    </div>

                                </div>

                                

                                <!-- Shelf B -->

                                <div class="rack-slot bg-green-100 border-2 border-green-300 rounded-lg p-3 cursor-pointer hover:bg-green-200 transition-all duration-200 hover:shadow-lg" data-rack="B">

                                    <div class="flex items-center justify-between">

                                        <div class="text-lg font-bold text-green-800">Shelf B</div>

                                        <div class="text-sm text-gray-600">

                                            <span class="rack-occupied">0</span>/<span class="rack-capacity">100</span>

                                        </div>

                                    </div>

                                    <div class="w-full bg-gray-300 rounded-full h-2 mt-2">

                                        <div class="rack-occupancy-bar bg-green-500 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>

                                    </div>

                                </div>

                                

                                <!-- Shelf C -->

                                <div class="rack-slot bg-yellow-100 border-2 border-yellow-300 rounded-lg p-3 cursor-pointer hover:bg-yellow-200 transition-all duration-200 hover:shadow-lg" data-rack="C">

                                    <div class="flex items-center justify-between">

                                        <div class="text-lg font-bold text-yellow-800">Shelf C</div>

                                        <div class="text-sm text-gray-600">

                                            <span class="rack-occupied">0</span>/<span class="rack-capacity">100</span>

                                        </div>

                                    </div>

                                    <div class="w-full bg-gray-300 rounded-full h-2 mt-2">

                                        <div class="rack-occupancy-bar bg-yellow-500 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>

                                    </div>

                                </div>

                                

                                <!-- Shelf D -->

                                <div class="rack-slot bg-red-100 border-2 border-red-300 rounded-lg p-3 cursor-pointer hover:bg-red-200 transition-all duration-200 hover:shadow-lg" data-rack="D">

                                    <div class="flex items-center justify-between">

                                        <div class="text-lg font-bold text-red-800">Shelf D</div>

                                        <div class="text-sm text-gray-600">

                                            <span class="rack-occupied">0</span>/<span class="rack-capacity">100</span>

                                        </div>

                                    </div>

                                    <div class="w-full bg-gray-300 rounded-full h-2 mt-2">

                                        <div class="rack-occupancy-bar bg-red-500 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>

                                    </div>

                                </div>

                                

                                <!-- Shelf E -->

                                <div class="rack-slot bg-purple-100 border-2 border-purple-300 rounded-lg p-3 cursor-pointer hover:bg-purple-200 transition-all duration-200 hover:shadow-lg" data-rack="E">

                                    <div class="flex items-center justify-between">

                                        <div class="text-lg font-bold text-purple-800">Shelf E</div>

                                        <div class="text-sm text-gray-600">

                                            <span class="rack-occupied">0</span>/<span class="rack-capacity">100</span>

                                        </div>

                                    </div>

                                    <div class="w-full bg-gray-300 rounded-full h-2 mt-2">

                                        <div class="rack-occupancy-bar bg-purple-500 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>

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

                            

                            <!-- Search Bar in Folders Section -->

                            <div class="mb-4">

                                <div class="relative">

                                    <input 

                                        type="text" 

                                        id="searchInput"

                                        placeholder="Search folders in this rack..."

                                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"

                                    >

                                    <svg class="absolute left-3 top-2.5 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>

                                    </svg>

                                    <div id="clearSearch" class="absolute right-3 top-2.5 hidden">

                                        <button class="text-gray-400 hover:text-gray-600">

                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>

                                            </svg>

                                        </button>

                                    </div>

                                </div>

                                <div id="searchResults" class="mt-1 text-xs text-gray-600 hidden">

                                    <span id="resultsCount">0</span> folders found

                                </div>

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

        this.showCoordinates = false;
        this.hoverX = 0;
        this.hoverY = 0;
        this.showHoverCoordinates = true;
        this.clickX = 0;
        this.clickY = 0;
        this.showCoordinates = false;
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

            

            // If a rack is selected, update the folder display with search results

            if (this.selectedRack) {

                this.showRackDetails(this.selectedRack);

            }

        });

        

        clearSearch.addEventListener('click', () => {

            this.searchQuery = '';

            searchInput.value = '';

            this.updateSearchResults();

            

            // If a rack is selected, refresh the folder display

            if (this.selectedRack) {

                this.showRackDetails(this.selectedRack);

            }

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

    

    get filteredFolders() {

        if (!this.searchQuery || !this.selectedRack) return [];

        

        const query = this.searchQuery.toLowerCase();

        const rackSlot = document.querySelector(`[data-rack="${this.selectedRack}"]`);

        if (!rackSlot) return [];

        

        const folders = JSON.parse(rackSlot.dataset.folders || '[]');

        return folders.filter(folder => 

            folder.toLowerCase().includes(query)

        );

    }

    

    updateSearchResults() {

        const resultsCount = document.getElementById('resultsCount');

        const searchResults = document.getElementById('searchResults');

        const clearButton = document.getElementById('clearSearch');

        

        const filtered = this.filteredFolders;

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

        // Check if click is near coordinates and open appropriate cabinet
        // Use a small range around the target coordinates for better usability
        const tolerance = 2; // Allow 2% tolerance in each direction
        
        // Cabinet 1 at coordinates x=39, y=68
        if (Math.abs(this.clickX - 39) <= tolerance && Math.abs(this.clickY - 68) <= tolerance) {
            console.log(`Opening cabinet 1 at coordinates: X=${this.clickX}, Y=${this.clickY} (target: X=39, Y=68)`);
            this.openCabinetAtCoordinates(1);
        }
        // Cabinet 2 at coordinates x=43, y=68
        else if (Math.abs(this.clickX - 43) <= tolerance && Math.abs(this.clickY - 68) <= tolerance) {
            console.log(`Opening cabinet 2 at coordinates: X=${this.clickX}, Y=${this.clickY} (target: X=43, Y=68)`);
            this.openCabinetAtCoordinates(2);
        }
        // Cabinet 3 at coordinates x=49, y=68
        else if (Math.abs(this.clickX - 49) <= tolerance && Math.abs(this.clickY - 68) <= tolerance) {
            console.log(`Opening cabinet 3 at coordinates: X=${this.clickX}, Y=${this.clickY} (target: X=49, Y=68)`);
            this.openCabinetAtCoordinates(3);
        }
        // Cabinet 4 at coordinates x=53, y=68
        else if (Math.abs(this.clickX - 53) <= tolerance && Math.abs(this.clickY - 68) <= tolerance) {
            console.log(`Opening cabinet 4 at coordinates: X=${this.clickX}, Y=${this.clickY} (target: X=53, Y=68)`);
            this.openCabinetAtCoordinates(4);
        }
        // Cabinet 5 at coordinates x=59, y=68
        else if (Math.abs(this.clickX - 59) <= tolerance && Math.abs(this.clickY - 68) <= tolerance) {
            console.log(`Opening cabinet 5 at coordinates: X=${this.clickX}, Y=${this.clickY} (target: X=59, Y=68)`);
            this.openCabinetAtCoordinates(5);
        }
        // Cabinet 6 at coordinates x=63, y=68
        else if (Math.abs(this.clickX - 63) <= tolerance && Math.abs(this.clickY - 68) <= tolerance) {
            console.log(`Opening cabinet 6 at coordinates: X=${this.clickX}, Y=${this.clickY} (target: X=63, Y=68)`);
            this.openCabinetAtCoordinates(6);
        }
        // Cabinet 7 at coordinates x=69, y=68
        else if (Math.abs(this.clickX - 69) <= tolerance && Math.abs(this.clickY - 68) <= tolerance) {
            console.log(`Opening cabinet 7 at coordinates: X=${this.clickX}, Y=${this.clickY} (target: X=69, Y=68)`);
            this.openCabinetAtCoordinates(7);
        }
        // Cabinet 8 at coordinates x=73, y=68
        else if (Math.abs(this.clickX - 73) <= tolerance && Math.abs(this.clickY - 68) <= tolerance) {
            console.log(`Opening cabinet 8 at coordinates: X=${this.clickX}, Y=${this.clickY} (target: X=73, Y=68)`);
            this.openCabinetAtCoordinates(8);
        }
        // Cabinet 9 at coordinates x=79, y=64
        else if (Math.abs(this.clickX - 79) <= tolerance && Math.abs(this.clickY - 64) <= tolerance) {
            console.log(`Opening cabinet 9 at coordinates: X=${this.clickX}, Y=${this.clickY} (target: X=79, Y=64)`);
            this.openCabinetAtCoordinates(9);
        }
        // Cabinet 10 at coordinates x=83, y=64
        else if (Math.abs(this.clickX - 83) <= tolerance && Math.abs(this.clickY - 64) <= tolerance) {
            console.log(`Opening cabinet 10 at coordinates: X=${this.clickX}, Y=${this.clickY} (target: X=83, Y=64)`);
            this.openCabinetAtCoordinates(10);
        }
        // Cabinet 11 at coordinates x=89, y=64
        else if (Math.abs(this.clickX - 89) <= tolerance && Math.abs(this.clickY - 64) <= tolerance) {
            console.log(`Opening cabinet 11 at coordinates: X=${this.clickX}, Y=${this.clickY} (target: X=89, Y=64)`);
            this.openCabinetAtCoordinates(11);
        }
        // Cabinet 12 at coordinates x=93, y=64
        else if (Math.abs(this.clickX - 93) <= tolerance && Math.abs(this.clickY - 64) <= tolerance) {
            console.log(`Opening cabinet 12 at coordinates: X=${this.clickX}, Y=${this.clickY} (target: X=93, Y=64)`);
            this.openCabinetAtCoordinates(12);
        }
        // Cabinet 13 at coordinates x=38, y=36
        else if (Math.abs(this.clickX - 38) <= tolerance && Math.abs(this.clickY - 36) <= tolerance) {
            console.log(`Opening cabinet 13 at coordinates: X=${this.clickX}, Y=${this.clickY} (target: X=38, Y=36)`);
            this.openCabinetAtCoordinates(13);
        }
        // Cabinet 14 at coordinates x=46, y=29
        else if (Math.abs(this.clickX - 46) <= tolerance && Math.abs(this.clickY - 29) <= tolerance) {
            console.log(`Opening cabinet 14 at coordinates: X=${this.clickX}, Y=${this.clickY} (target: X=46, Y=29)`);
            this.openCabinetAtCoordinates(14);
        }
        // Cabinet 15 at coordinates x=56, y=29
        else if (Math.abs(this.clickX - 56) <= tolerance && Math.abs(this.clickY - 29) <= tolerance) {
            console.log(`Opening cabinet 15 at coordinates: X=${this.clickX}, Y=${this.clickY} (target: X=56, Y=29)`);
            this.openCabinetAtCoordinates(15);
        }
        // Cabinet 16 at coordinates x=67, y=29
        else if (Math.abs(this.clickX - 67) <= tolerance && Math.abs(this.clickY - 29) <= tolerance) {
            console.log(`Opening cabinet 16 at coordinates: X=${this.clickX}, Y=${this.clickY} (target: X=67, Y=29)`);
            this.openCabinetAtCoordinates(16);
        }
        // Cabinet 17 at coordinates x=75, y=29
        else if (Math.abs(this.clickX - 75) <= tolerance && Math.abs(this.clickY - 29) <= tolerance) {
            console.log(`Opening cabinet 17 at coordinates: X=${this.clickX}, Y=${this.clickY} (target: X=75, Y=29)`);
            this.openCabinetAtCoordinates(17);
        }
        // Cabinet 18 at coordinates x=81, y=29
        else if (Math.abs(this.clickX - 81) <= tolerance && Math.abs(this.clickY - 29) <= tolerance) {
            console.log(`Opening cabinet 18 at coordinates: X=${this.clickX}, Y=${this.clickY} (target: X=81, Y=29)`);
            this.openCabinetAtCoordinates(18);
        }
        // Cabinet 19 at coordinates x=87, y=29
        else if (Math.abs(this.clickX - 87) <= tolerance && Math.abs(this.clickY - 29) <= tolerance) {
            console.log(`Opening cabinet 19 at coordinates: X=${this.clickX}, Y=${this.clickY} (target: X=87, Y=29)`);
            this.openCabinetAtCoordinates(19);
        }
        // Cabinet 20 at coordinates x=93, y=29
        else if (Math.abs(this.clickX - 93) <= tolerance && Math.abs(this.clickY - 29) <= tolerance) {
            console.log(`Opening cabinet 20 at coordinates: X=${this.clickX}, Y=${this.clickY} (target: X=93, Y=29)`);
            this.openCabinetAtCoordinates(20);
        }

        // Hide coordinates after 3 seconds

        setTimeout(() => {

            this.showCoordinates = false;

            clickCoords.classList.add('hidden');

        }, 3000);

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

        // Show chatboxes when hovering near specific coordinates
        const brendaChatbox = document.getElementById('brendaChatbox');
        const arleneChatbox = document.getElementById('arleneChatbox');
        const lizaChatbox = document.getElementById('lizaChatbox');
        const tolerance = 3; // Allow 3% tolerance for hover detection
        
        // Brenda at x=32, y=35
        if (Math.abs(this.hoverX - 32) <= tolerance && Math.abs(this.hoverY - 35) <= tolerance) {
            brendaChatbox.classList.remove('hidden');
        } else {
            brendaChatbox.classList.add('hidden');
        }
        
        // Arlene at x=33, y=14
        if (Math.abs(this.hoverX - 33) <= tolerance && Math.abs(this.hoverY - 14) <= tolerance) {
            arleneChatbox.classList.remove('hidden');
        } else {
            arleneChatbox.classList.add('hidden');
        }
        
        // Liza at x=12, y=70
        if (Math.abs(this.hoverX - 12) <= tolerance && Math.abs(this.hoverY - 70) <= tolerance) {
            lizaChatbox.classList.remove('hidden');
        } else {
            lizaChatbox.classList.add('hidden');
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

    

    openCabinetAtCoordinates(cabinetNumber) {
        console.log(`openCabinetAtCoordinates function called for cabinet ${cabinetNumber}`);
        
        // Create mock area data based on cabinet number
        const cabinetData = {
            1: {
                name: 'Rack 1 - Permit',
                data: {
                    shelf: 'Shelf A',
                    capacity: 100,
                    occupied: 25,
                    documents: ['CAB1-DOC-001', 'CAB1-DOC-002', 'CAB1-DOC-003']
                }
            },
            2: {
                name: 'Rack 2 - Permit',
                data: {
                    shelf: 'Shelf B',
                    capacity: 100,
                    occupied: 45,
                    documents: ['CAB2-DOC-001', 'CAB2-DOC-002', 'CAB2-DOC-003', 'CAB2-DOC-004', 'CAB2-DOC-005']
                }
            },
            3: {
                name: 'Rack 3 - Permit',
                data: {
                    shelf: 'Shelf C',
                    capacity: 100,
                    occupied: 67,
                    documents: ['CAB3-DOC-001', 'CAB3-DOC-002', 'CAB3-DOC-003', 'CAB3-DOC-004', 'CAB3-DOC-005', 'CAB3-DOC-006', 'CAB3-DOC-007']
                }
            },
            4: {
                name: 'Rack 4 - Permit',
                data: {
                    shelf: 'Shelf D',
                    capacity: 100,
                    occupied: 78,
                    documents: ['CAB4-DOC-001', 'CAB4-DOC-002', 'CAB4-DOC-003', 'CAB4-DOC-004', 'CAB4-DOC-005', 'CAB4-DOC-006', 'CAB4-DOC-007', 'CAB4-DOC-008']
                }
            },
            5: {
                name: 'Rack 5 - Permit',
                data: {
                    shelf: 'Shelf A',
                    capacity: 100,
                    occupied: 34,
                    documents: ['CAB5-DOC-001', 'CAB5-DOC-002', 'CAB5-DOC-003', 'CAB5-DOC-004']
                }
            },
            6: {
                name: 'Rack 6 - Permit',
                data: {
                    shelf: 'Shelf B',
                    capacity: 100,
                    occupied: 56,
                    documents: ['CAB6-DOC-001', 'CAB6-DOC-002', 'CAB6-DOC-003', 'CAB6-DOC-004', 'CAB6-DOC-005', 'CAB6-DOC-006']
                }
            },
            7: {
                name: 'Rack 7 - Permit',
                data: {
                    shelf: 'Shelf C',
                    capacity: 100,
                    occupied: 89,
                    documents: ['CAB7-DOC-001', 'CAB7-DOC-002', 'CAB7-DOC-003', 'CAB7-DOC-004', 'CAB7-DOC-005', 'CAB7-DOC-006', 'CAB7-DOC-007', 'CAB7-DOC-008', 'CAB7-DOC-009']
                }
            },
            8: {
                name: 'Rack 8 - Permit',
                data: {
                    shelf: 'Shelf D',
                    capacity: 100,
                    occupied: 23,
                    documents: ['CAB8-DOC-001', 'CAB8-DOC-002', 'CAB8-DOC-003']
                }
            },
            9: {
                name: 'Rack 9 - ECC Files',
                data: {
                    shelf: 'Shelf A',
                    capacity: 100,
                    occupied: 45,
                    documents: ['CAB9-DOC-001', 'CAB9-DOC-002', 'CAB9-DOC-003', 'CAB9-DOC-004', 'CAB9-DOC-005']
                }
            },
            10: {
                name: 'Rack 10 - ECC Files',
                data: {
                    shelf: 'Shelf B',
                    capacity: 100,
                    occupied: 72,
                    documents: ['CAB10-DOC-001', 'CAB10-DOC-002', 'CAB10-DOC-003', 'CAB10-DOC-004', 'CAB10-DOC-005', 'CAB10-DOC-006', 'CAB10-DOC-007']
                }
            },
            11: {
                name: 'Rack 11 - ECC Files',
                data: {
                    shelf: 'Shelf C',
                    capacity: 100,
                    occupied: 38,
                    documents: ['CAB11-DOC-001', 'CAB11-DOC-002', 'CAB11-DOC-003', 'CAB11-DOC-004']
                }
            },
            12: {
                name: 'Rack 12 - ECC Files',
                data: {
                    shelf: 'Shelf D',
                    capacity: 100,
                    occupied: 91,
                    documents: ['CAB12-DOC-001', 'CAB12-DOC-002', 'CAB12-DOC-003', 'CAB12-DOC-004', 'CAB12-DOC-005', 'CAB12-DOC-006', 'CAB12-DOC-007', 'CAB12-DOC-008', 'CAB12-DOC-009', 'CAB12-DOC-010']
                }
            },
            13: {
                name: 'Rack 13 - IEE Files',
                data: {
                    shelf: 'Shelf A',
                    capacity: 100,
                    occupied: 52,
                    documents: ['CAB13-DOC-001', 'CAB13-DOC-002', 'CAB13-DOC-003', 'CAB13-DOC-004', 'CAB13-DOC-005', 'CAB13-DOC-006']
                }
            },
            14: {
                name: 'Rack 14 - IEE Files',
                data: {
                    shelf: 'Shelf B',
                    capacity: 100,
                    occupied: 28,
                    documents: ['CAB14-DOC-001', 'CAB14-DOC-002', 'CAB14-DOC-003']
                }
            },
            15: {
                name: 'Rack 15 - IEE Files',
                data: {
                    shelf: 'Shelf C',
                    capacity: 100,
                    occupied: 73,
                    documents: ['CAB15-DOC-001', 'CAB15-DOC-002', 'CAB15-DOC-003', 'CAB15-DOC-004', 'CAB15-DOC-005', 'CAB15-DOC-006', 'CAB15-DOC-007', 'CAB15-DOC-008']
                }
            },
            16: {
                name: 'Rack 16 - IEE Files',
                data: {
                    shelf: 'Shelf D',
                    capacity: 100,
                    occupied: 41,
                    documents: ['CAB16-DOC-001', 'CAB16-DOC-002', 'CAB16-DOC-003', 'CAB16-DOC-004', 'CAB16-DOC-005']
                }
            },
            17: {
                name: 'Rack 17 - IEE Files',
                data: {
                    shelf: 'Shelf A',
                    capacity: 100,
                    occupied: 85,
                    documents: ['CAB17-DOC-001', 'CAB17-DOC-002', 'CAB17-DOC-003', 'CAB17-DOC-004', 'CAB17-DOC-005', 'CAB17-DOC-006', 'CAB17-DOC-007', 'CAB17-DOC-008', 'CAB17-DOC-009']
                }
            },
            18: {
                name: 'Rack 18 - IEE Files',
                data: {
                    shelf: 'Shelf B',
                    capacity: 100,
                    occupied: 19,
                    documents: ['CAB18-DOC-001', 'CAB18-DOC-002']
                }
            },
            19: {
                name: 'Rack 19 - IEE Files',
                data: {
                    shelf: 'Shelf C',
                    capacity: 100,
                    occupied: 64,
                    documents: ['CAB19-DOC-001', 'CAB19-DOC-002', 'CAB19-DOC-003', 'CAB19-DOC-004', 'CAB19-DOC-005', 'CAB19-DOC-006', 'CAB19-DOC-007']
                }
            },
            20: {
                name: 'Rack 20 - IEE Files',
                data: {
                    shelf: 'Shelf E',
                    capacity: 100,
                    occupied: 96,
                    documents: ['CAB20-DOC-001', 'CAB20-DOC-002', 'CAB20-DOC-003', 'CAB20-DOC-004', 'CAB20-DOC-005', 'CAB20-DOC-006', 'CAB20-DOC-007', 'CAB20-DOC-008', 'CAB20-DOC-009', 'CAB20-DOC-010', 'CAB20-DOC-011']
                }
            },
            21: {
                name: 'Rack 21',
                data: {
                    shelf: 'Shelf E',
                    capacity: 100,
                    occupied: 42,
                    documents: ['CAB21-DOC-001', 'CAB21-DOC-002', 'CAB21-DOC-003', 'CAB21-DOC-004', 'CAB21-DOC-005']
                }
            },
            22: {
                name: 'Rack 22',
                data: {
                    shelf: 'Shelf E',
                    capacity: 100,
                    occupied: 78,
                    documents: ['CAB22-DOC-001', 'CAB22-DOC-002', 'CAB22-DOC-003', 'CAB22-DOC-004', 'CAB22-DOC-005', 'CAB22-DOC-006', 'CAB22-DOC-007', 'CAB22-DOC-008']
                }
            },
            23: {
                name: 'Rack 23',
                data: {
                    shelf: 'Shelf E',
                    capacity: 100,
                    occupied: 15,
                    documents: ['CAB23-DOC-001', 'CAB23-DOC-002', 'CAB23-DOC-003']
                }
            },
            24: {
                name: 'Rack 24',
                data: {
                    shelf: 'Shelf E',
                    capacity: 100,
                    occupied: 63,
                    documents: ['CAB24-DOC-001', 'CAB24-DOC-002', 'CAB24-DOC-003', 'CAB24-DOC-004', 'CAB24-DOC-005', 'CAB24-DOC-006', 'CAB24-DOC-007']
                }
            },
            25: {
                name: 'Rack 25',
                data: {
                    shelf: 'Shelf E',
                    capacity: 100,
                    occupied: 87,
                    documents: ['CAB25-DOC-001', 'CAB25-DOC-002', 'CAB25-DOC-003', 'CAB25-DOC-004', 'CAB25-DOC-005', 'CAB25-DOC-006', 'CAB25-DOC-007', 'CAB25-DOC-008', 'CAB25-DOC-009']
                }
            }
        };
        
        this.clickedArea = cabinetData[cabinetNumber];
        console.log('Created mock area:', this.clickedArea);
        
        // Show the cabinet details
        this.showAreaDetails();
        console.log('Called showAreaDetails');
        
        // Show a notification for cabinet opening
        const colorMap = {
            1: 'bg-yellow-500',
            2: 'bg-yellow-500',
            3: 'bg-yellow-500',
            4: 'bg-yellow-500',
            5: 'bg-yellow-500',
            6: 'bg-yellow-500',
            7: 'bg-yellow-500',
            8: 'bg-yellow-500',
            9: 'bg-red-500',
            10: 'bg-red-500',
            11: 'bg-red-500',
            12: 'bg-red-500',
            13: 'bg-blue-500',
            14: 'bg-blue-500',
            15: 'bg-blue-500',
            16: 'bg-blue-500',
            17: 'bg-blue-500',
            18: 'bg-blue-500',
            19: 'bg-blue-500',
            20: 'bg-blue-500'
        };
        
        const notificationColor = colorMap[cabinetNumber];
        
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 ${notificationColor} text-white px-4 py-2 rounded-lg shadow-lg z-50 fade-in`;
        notification.innerHTML = `
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span>Cabinet ${cabinetNumber} opened</span>
            </div>
        `;
        document.body.appendChild(notification);
        
        // Remove notification after 3 seconds
        setTimeout(() => {
            notification.remove();
        }, 3000);
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

            if (rackLetter && ['A', 'B', 'C', 'D', 'E'].includes(rackLetter)) {

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

        ['A', 'B', 'C', 'D', 'E'].forEach(rackLetter => {

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

        

        this.selectedRack = rackLetter;

        

        // Use filtered folders if search is active, otherwise use all folders

        let folders;

        if (this.searchQuery) {

            folders = this.filteredFolders;

        } else {

            folders = JSON.parse(rackSlot.dataset.folders || '[]');

        }

        

        // Clear and populate folders grid with animation

        const foldersGrid = document.getElementById('rackFoldersGrid');

        foldersGrid.innerHTML = '';

        

        if (folders.length === 0) {

            const emptyElement = document.createElement('div');

            emptyElement.className = 'text-center text-gray-500 py-8 opacity-0 transform translate-y-4';

            const emptyMessage = this.searchQuery ? 

                `No folders found matching "${this.searchQuery}"` : 

                `No folders in Rack ${rackLetter}`;

            emptyElement.innerHTML = `

                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>

                </svg>

                <p class="text-sm">${emptyMessage}</p>

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

    showBrendaProfile() {
        // Create and show detailed profile modal for Brenda
        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
        modal.innerHTML = `
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-900">Ma'am Brenda - Profile</h3>
                    <button onclick="this.closest('.fixed').remove()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <div class="flex items-center space-x-4 mb-4">
                    <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-pink-500 rounded-full flex items-center justify-center text-white font-bold text-xl shadow-lg">
                        B
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-900">Ma'am Brenda</h4>
                        <p class="text-sm text-gray-500">Senior Records Manager</p>
                        <div class="flex items-center mt-1">
                            <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                            <span class="text-xs text-gray-600 ml-2">Active Now</span>
                        </div>
                    </div>
                </div>
                
                <div class="space-y-3">
                    <div class="border-t pt-3">
                        <h5 class="text-sm font-medium text-gray-900 mb-2">Contact Information</h5>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Email:</span>
                                <span class="text-gray-900">brenda@records.gov</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Extension:</span>
                                <span class="text-gray-900">x235</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Department:</span>
                                <span class="text-gray-900">Records Management</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="border-t pt-3">
                        <h5 class="text-sm font-medium text-gray-900 mb-2">Responsibilities</h5>
                        <ul class="text-sm text-gray-700 space-y-1">
                            <li>• Shelf Map Administration</li>
                            <li>• Document Records Management</li>
                            <li>• User Support & Training</li>
                            <li>• System Maintenance</li>
                        </ul>
                    </div>
                </div>
                
                <div class="flex space-x-3 mt-6">
                    <button onclick="this.closest('.fixed').remove()" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                        Close
                    </button>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
    }

    showArleneProfile() {
        // Create and show detailed profile modal for Arlene
        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
        modal.innerHTML = `
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-900">Ma'am Arlene - Profile</h3>
                    <button onclick="this.closest('.fixed').remove()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <div class="flex items-center space-x-4 mb-4">
                    <div class="w-16 h-16 bg-gradient-to-br from-orange-500 to-red-500 rounded-full flex items-center justify-center text-white font-bold text-xl shadow-lg">
                        A
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-900">Ma'am Arlene</h4>
                        <p class="text-sm text-gray-500">Senior Data Analyst</p>
                        <div class="flex items-center mt-1">
                            <div class="w-2 h-2 bg-yellow-500 rounded-full animate-pulse"></div>
                            <span class="text-xs text-gray-600 ml-2">Busy</span>
                        </div>
                    </div>
                </div>
                
                <div class="space-y-3">
                    <div class="border-t pt-3">
                        <h5 class="text-sm font-medium text-gray-900 mb-2">Contact Information</h5>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Email:</span>
                                <span class="text-gray-900">arlene@records.gov</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Extension:</span>
                                <span class="text-gray-900">x242</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Department:</span>
                                <span class="text-gray-900">Data Analytics</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="border-t pt-3">
                        <h5 class="text-sm font-medium text-gray-900 mb-2">Responsibilities</h5>
                        <ul class="text-sm text-gray-700 space-y-1">
                            <li>• Data Analysis & Reporting</li>
                            <li>• Performance Metrics</li>
                            <li>• Quality Assurance</li>
                            <li>• Statistical Insights</li>
                        </ul>
                    </div>
                </div>
                
                <div class="flex space-x-3 mt-6">
                    <button onclick="this.closest('.fixed').remove()" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                        Close
                    </button>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
    }

    showLizaProfile() {
        // Create and show detailed profile modal for Liza
        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
        modal.innerHTML = `
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-900">Ma'am Liza - Profile</h3>
                    <button onclick="this.closest('.fixed').remove()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <div class="flex items-center space-x-4 mb-4">
                    <div class="w-16 h-16 bg-gradient-to-br from-teal-500 to-cyan-500 rounded-full flex items-center justify-center text-white font-bold text-xl shadow-lg">
                        L
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-900">Ma'am Liza</h4>
                        <p class="text-sm text-gray-500">System Administrator</p>
                        <div class="flex items-center mt-1">
                            <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                            <span class="text-xs text-gray-600 ml-2">Available</span>
                        </div>
                    </div>
                </div>
                
                <div class="space-y-3">
                    <div class="border-t pt-3">
                        <h5 class="text-sm font-medium text-gray-900 mb-2">Contact Information</h5>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Email:</span>
                                <span class="text-gray-900">liza@records.gov</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Extension:</span>
                                <span class="text-gray-900">x198</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Department:</span>
                                <span class="text-gray-900">System Administration</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="border-t pt-3">
                        <h5 class="text-sm font-medium text-gray-900 mb-2">Responsibilities</h5>
                        <ul class="text-sm text-gray-700 space-y-1">
                            <li>• System Administration</li>
                            <li>• User Management</li>
                            <li>• Security Oversight</li>
                            <li>• Technical Support</li>
                        </ul>
                    </div>
                </div>
                
                <div class="flex space-x-3 mt-6">
                    <button onclick="this.closest('.fixed').remove()" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                        Close
                    </button>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
    }

}


// Initialize the app when DOM is ready

document.addEventListener('DOMContentLoaded', () => {

    new ShelfMapApp();

});

</script>

<?= $this->endSection() ?>

