<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BARI-TICKETS - Nunua Tiketi Kirahisi</title>
    <script src="https://cdn.tailwindcss.com"></script>
   <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #2d4563 0%, #3d5573 50%, #d4af37 100%);
            min-height: 100vh;
        }
        .event-card {
            transition: all 0.3s ease;
        }
        .event-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0,0,0,0.15);
        }
        
        /* Masonry Layout */
        .masonry-grid {
            display: grid;
            gap: 8px;
        }
        
        /* Mobile: 2 columns with fixed uniform heights */
        @media (max-width: 640px) {
            .masonry-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 8px;
            }
            
            .masonry-item-small,
            .masonry-item-medium,
            .masonry-item-large,
            .masonry-item-xlarge {
                height: 280px;
            }
        }
        
        /* Tablet: 2 columns with reduced row gap */
        @media (min-width: 641px) and (max-width: 1023px) {
            .masonry-grid {
                grid-template-columns: repeat(2, 1fr);
                grid-auto-rows: 8px;
                gap: 12px;
            }
            
            .masonry-item-small {
                grid-row: span 30;
            }
            
            .masonry-item-medium {
                grid-row: span 40;
            }
            
            .masonry-item-large {
                grid-row: span 50;
            }
            
            .masonry-item-xlarge {
                grid-row: span 60;
            }
        }
        
        /* Desktop: 3 columns true masonry with natural image heights */
        @media (min-width: 1024px) {
            .masonry-grid {
                grid-template-columns: repeat(3, 1fr);
                grid-auto-rows: 4px;
                gap: 16px;
            }
            
            .masonry-item-small img,
            .masonry-item-medium img,
            .masonry-item-large img,
            .masonry-item-xlarge img {
                height: auto;
                width: 100%;
                object-fit: cover;
            }
            
            .masonry-item-small {
                grid-row: span 60;
            }
            
            .masonry-item-medium {
                grid-row: span 80;
            }
            
            .masonry-item-large {
                grid-row: span 100;
            }
            
            .masonry-item-xlarge {
                grid-row: span 120;
            }
        }
        
        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
            z-index: 1000;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .modal.active {
            display: flex;
            opacity: 1;
        }
        
        .modal-content {
            background: white;
            border-radius: 24px;
            max-width: 900px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            margin: auto;
            transform: scale(0.9);
            transition: transform 0.3s ease;
        }
        
        .modal.active .modal-content {
            transform: scale(1);
        }
        
        .logo-container img {
            width: 150px;
            height: auto;
        }

        /* Bottom Navigation for Mobile */
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #2d4563;
            border-top: 1px solid rgba(212, 175, 55, 0.2);
            z-index: 50;
            box-shadow: 0 -4px 6px -1px rgba(0, 0, 0, 0.2);
        }

        /* Sidebar for Desktop */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            width: 280px;
            background: #2d4563;
            border-right: 1px solid rgba(212, 175, 55, 0.2);
            z-index: 50;
            box-shadow: 4px 0 6px -1px rgba(0, 0, 0, 0.2);
            display: none;
        }

        @media (min-width: 1024px) {
            .sidebar {
                display: block;
            }
            .bottom-nav {
                display: none;
            }
            body {
                padding-left: 280px;
            }
        }

        @media (max-width: 1023px) {
            .bottom-nav {
                display: block;
            }
            body {
                padding-bottom: 80px;
            }
        }

        /* Sidebar menu item hover effect */
        .sidebar-item {
            transition: all 0.3s ease;
        }
        
        .sidebar-item:hover {
            background: rgba(212, 175, 55, 0.1);
            border-left: 4px solid #d4af37;
        }

        .sidebar-item.active {
            background: rgba(212, 175, 55, 0.2);
            border-left: 4px solid #d4af37;
        }

        /* Main content background */
        main {
            background: linear-gradient(135deg, #f5f7fa 0%, #e8ecf1 50%, #f0f3f7 100%);
            
        }
    </style>

</head>
<body class="text-gray-800 ">
    
    <!-- Top Navigation Bar -->
    <nav class="bg-[#2d4563] border-b border-[#d4af37]/20 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <!-- Left: Menu Icon (Mobile Only) -->
                <button id="menuBtn" class="p-2 text-white hover:text-[#d4af37] transition-colors lg:hidden">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>

                <!-- Center: Logo -->
                <a href="#nyu
                mbani" class="logo-container absolute left-1/2 transform -translate-x-1/2 lg:relative lg:left-0 lg:transform-none">
                    <img class="logo" alt="BARI-TICKETS" src="{{asset('assets/images/logo-light.png')}}"/>
                </a>

                <!-- Right: Search Icon -->
                <button id="searchBtn" class="p-2 text-white hover:text-[#d4af37] transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </button>
            </div>
        </div>
    </nav>

    <!-- Sidebar for Desktop -->
    <aside class="sidebar">
        <div class="flex flex-col h-full p-6">
            <!-- Logo -->
            <div class="mb-8">
                <img src="{{asset('assets/images/logo-light.png')}}" alt="BARI-TICKETS" class="w-48"/>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 space-y-2">
                <a href="#nyumbani" class="sidebar-item flex items-center gap-3 px-4 py-3 text-white rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    <span class="font-medium">Nyumbani</span>
                </a>

                <a href="#matukio" class="sidebar-item active flex items-center gap-3 px-4 py-3 text-white rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span class="font-medium">Matukio</span>
                </a>

                <button id="sidebarHowItWorksBtn" class="sidebar-item flex items-center gap-3 px-4 py-3 text-white rounded-lg w-full text-left">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="font-medium">Jinsi Inavyofanya Kazi</span>
                </button>

                <button id="sidebarContactBtn" class="sidebar-item flex items-center gap-3 px-4 py-3 text-white rounded-lg w-full text-left">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    <span class="font-medium">Wasiliana Nasi</span>
                </button>
            </nav>

            <!-- Login Button -->
            <a href="{{ route('login') }}" class="mt-auto flex items-center justify-center gap-2 px-6 py-4 bg-[#d4af37] text-[#2d4563] rounded-xl hover:bg-[#b8942d] transition-colors font-semibold shadow-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                <span>Ingia</span>
            </a>
        </div>
    </aside>

    <!-- Featured Events Section -->
    <main id="matukio" class="py-4 lg:py-8 bg-white">
        <div class="max-w-7xl mx-auto px-3 lg:px-6">
        
            @if($events->count() > 0)
            <div class="masonry-grid">
                @foreach($events as $event)
                @php
                    // Create a pattern for varied sizes
                    $sizeClasses = ['masonry-item-small', 'masonry-item-medium', 'masonry-item-large', 'masonry-item-xlarge'];
                    $sizeClass = $sizeClasses[$loop->index % 4];
                @endphp
                
                <!-- Event Card -->
                <a href="{{ route('showEventPage', ['event_id' => $event->id, 'event_slug' => Str::slug($event->title)]) }}" 
                   class="event-card {{ $sizeClass }} relative md:rounded-3xl overflow-hidden cursor-pointer bg-white shadow-md border border-gray-100">
                    
                    @if($event->images->count() > 0)
                        <img src="{{ asset(config('attendize.cdn_url_user_assets').'/'.$event->images->first()->image_path) }}" 
                             alt="{{ $event->title }}" class="w-full h-full object-cover">
                    @else
                        <!-- Fallback gradient -->
                        <div class="w-full h-full bg-gradient-to-br from-[#2d4563] via-[#3d5573] to-[#d4af37] flex items-center justify-center">
                            <svg class="w-20 h-20 text-white/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    @endif
                </a>
                @endforeach
            </div>
            @else
            <!-- No Events Message -->
            <div class="text-center py-16 bg-gray-50 rounded-3xl">
                <svg class="w-20 h-20 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <h3 class="text-2xl font-bold text-[#2d4563] mb-2">Hakuna Matukio Kwa Sasa</h3>
                <p class="text-gray-500">Rudi hivi karibuni kuona matukio yanayokuja!</p>
            </div>
            @endif

        </div>
    </main>

    <!-- Bottom Navigation for Mobile -->
    <div class="bottom-nav lg:hidden">
        <div class="flex items-center justify-between px-6 py-4">
            <!-- How It Works Button -->
            <button id="howItWorksBtn" class="flex flex-col items-center gap-1 text-white hover:text-[#d4af37] transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="text-xs font-medium">Jinsi Inavyofanya</span>
            </button>

            <!-- Center Login Button -->
            <a href="{{ route('login') }}" class="flex flex-col items-center gap-1 -mt-8">
                <div class="w-16 h-16 bg-gradient-to-br from-[#d4af37] to-[#b8942d] rounded-full flex items-center justify-center shadow-lg">
                    <svg class="w-8 h-8 text-[#2d4563]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <span class="text-xs font-medium text-white">Ingia</span>
            </a>

            <!-- Contact Button -->
            <button id="contactBtn" class="flex flex-col items-center gap-1 text-white hover:text-[#d4af37] transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
                <span class="text-xs font-medium">Wasiliana</span>
            </button>
        </div>
    </div>

    <!-- Menu Modal -->
    <div id="menuModal" class="modal">
        <div class="modal-content p-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-[#2d4563]">Menu</h2>
                <button id="closeMenuModal" class="p-2 text-gray-600 hover:text-[#d4af37]">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="space-y-4">

            <a href="#" class="block px-4 py-3 text-lg font-medium text-[#2d4563] hover:bg-gray-50 rounded-lg">Nyumbani</a>
                <a href="#matukio" class="block px-4 py-3 text-lg font-medium text-[#2d4563] hover:bg-gray-50 rounded-lg">Matukio</a>
                <button id="menuHowItWorksBtn" class="block w-full text-left px-4 py-3 text-lg font-medium text-[#2d4563] hover:bg-gray-50 rounded-lg">Jinsi Inavyofanya Kazi</button>
                <button id="menuContactBtn" class="block w-full text-left px-4 py-3 text-lg font-medium text-[#2d4563] hover:bg-gray-50 rounded-lg">Wasiliana Nasi</button>
                <a href="{{ route('login') }}" class="block px-4 py-3 bg-[#d4af37] text-[#2d4563] rounded-lg hover:bg-[#b8942d] transition-colors font-semibold text-center text-lg">
                    Ingia
                </a>
            </div>
        </div>
    </div>

    <!-- How It Works Modal -->
    <div id="howItWorksModal" class="modal">
        <div class="modal-content p-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl lg:text-3xl font-bold text-[#2d4563]">Jinsi Inavyofanya Kazi</h2>
                <button id="closeHowItWorksModal" class="p-2 text-gray-600 hover:text-[#d4af37]">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <p class="text-gray-600 mb-8 text-center">
                Nunua tiketi kwa hatua tatu tu - haraka, salama, na rahisi
            </p>

            <div class="space-y-8">
                <!-- Step 1 -->
                <div class="flex gap-6">
                    <div class="flex-shrink-0">
                        <div class="w-16 h-16 bg-gradient-to-br from-[#2d4563] to-[#1a2942] rounded-full flex items-center justify-center">
                            <span class="text-2xl font-bold text-[#d4af37]">01</span>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-[#2d4563] mb-2">Tafuta na Chagua</h3>
                        <p class="text-gray-600">
                            Chunguza matukio mbalimbali yanayopatikana kwenye jukwaa la BARI-TICKETS. Chagua matukio ambayo unapendezwa nayo na angalia maelezo yote.
                        </p>
                    </div>
                </div>

                <!-- Step 2 -->
                <div class="flex gap-6">
                    <div class="flex-shrink-0">
                        <div class="w-16 h-16 bg-gradient-to-br from-[#2d4563] to-[#1a2942] rounded-full flex items-center justify-center">
                            <span class="text-2xl font-bold text-[#d4af37]">02</span>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-[#2d4563] mb-2">Lipa Salama</h3>
                        <p class="text-gray-600">
                            Lipa kwa njia salama kwa kutumia M-Pesa, kadi za mkopo, au njia nyingine za malipo. Tiketi yako itakufikia kwa dakika chache.
                        </p>
                    </div>
                </div>

                <!-- Step 3 -->
                <div class="flex gap-6">
                    <div class="flex-shrink-0">
                        <div class="w-16 h-16 bg-gradient-to-br from-[#2d4563] to-[#1a2942] rounded-full flex items-center justify-center">
                            <span class="text-2xl font-bold text-[#d4af37]">03</span>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-[#2d4563] mb-2">Ingia kwa Tiketi</h3>
                        <p class="text-gray-600">
                            Onyesha QR code yako kwenye mlango wa matukio. Tiketi yako ya dijitali itakuwezesha kuingia haraka na salama.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contact Modal -->
    <div id="contactModal" class="modal">
        <div class="modal-content p-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl lg:text-3xl font-bold text-[#2d4563]">Wasiliana Nasi</h2>
                <button id="closeContactModal" class="p-2 text-gray-600 hover:text-[#d4af37]">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <p class="text-gray-600 mb-8 text-center">
                Una maswali? Tuko hapa kukusaidia! Wasiliana nasi kwa njia yoyote inayokufaa
            </p>

            <div class="space-y-6">
                <!-- WhatsApp Card -->
                <a href="https://wa.me/255765762688" target="_blank" class="group block">
                    <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-2xl p-6 border-2 border-green-200 hover:border-green-400 transition-all hover:shadow-lg">
                        <div class="flex items-center gap-4 mb-3">
                            <div class="w-14 h-14 bg-green-500 rounded-xl flex items-center justify-center">
                                <svg class="w-7 h-7 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.67-1.612-.916-2.206-.242-.579-.487-.5-.67-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.29.173-1.413-.074-.123-.272-.198-.57-.347z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-800">WhatsApp</h3>
                                <p class="text-sm text-gray-600">Tupigie simu au tuma ujumbe</p>
                            </div>
                        </div>
                        <p class="text-xl font-bold text-green-600">+255 765 762688</p>
                    </div>
                </a>

                <!-- Email Card -->
                <a href="mailto:elibarikaneno@gmail.com" class="group block">
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-2xl p-6 border-2 border-blue-200 hover:border-blue-400 transition-all hover:shadow-lg">
                        <div class="flex items-center gap-4 mb-3">
                            <div class="w-14 h-14 bg-blue-500 rounded-xl flex items-center justify-center">
                                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-800">Email</h3>
                                <p class="text-sm text-gray-600">Tutumie barua pepe</p>
                            </div>
                        </div>
                        <p class="text-base font-bold text-blue-600 break-all">elibarikaneno@gmail.com</p>
                    </div>
                </a>

                <!-- Social Media Links -->
                <div class="pt-4">
                    <p class="text-gray-600 mb-4 text-center text-sm">Tufuate kwenye mitandao ya kijamii</p>
                    <div class="flex justify-center gap-4">
                        <a href="https://web.facebook.com/bari.kaneno/" class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center hover:bg-[#d4af37] hover:text-white transition-colors">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                        </a>
                        <a href="https://www.instagram.com/barikaneno/" class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center hover:bg-[#d4af37] hover:text-white transition-colors">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Menu Modal
        const menuBtn = document.getElementById('menuBtn');
        const menuModal = document.getElementById('menuModal');
        const closeMenuModal = document.getElementById('closeMenuModal');
        
        menuBtn.addEventListener('click', () => {
            menuModal.classList.add('active');
        });
        
        closeMenuModal.addEventListener('click', () => {
            menuModal.classList.remove('active');
        });
        
        // How It Works Modal
        const howItWorksBtn = document.getElementById('howItWorksBtn');
        const menuHowItWorksBtn = document.getElementById('menuHowItWorksBtn');
        const sidebarHowItWorksBtn = document.getElementById('sidebarHowItWorksBtn');
        const howItWorksModal = document.getElementById('howItWorksModal');
        const closeHowItWorksModal = document.getElementById('closeHowItWorksModal');
        
        howItWorksBtn.addEventListener('click', () => {
            howItWorksModal.classList.add('active');
        });
        
        menuHowItWorksBtn.addEventListener('click', () => {
            menuModal.classList.remove('active');
            setTimeout(() => {
                howItWorksModal.classList.add('active');
            }, 300);
        });

        sidebarHowItWorksBtn.addEventListener('click', () => {
            howItWorksModal.classList.add('active');
        });
        
        closeHowItWorksModal.addEventListener('click', () => {
            howItWorksModal.classList.remove('active');
        });
        
        // Contact Modal
        const contactBtn = document.getElementById('contactBtn');
        const menuContactBtn = document.getElementById('menuContactBtn');
        const sidebarContactBtn = document.getElementById('sidebarContactBtn');
        const contactModal = document.getElementById('contactModal');
        const closeContactModal = document.getElementById('closeContactModal');
        
        contactBtn.addEventListener('click', () => {
            contactModal.classList.add('active');
        });
        
        menuContactBtn.addEventListener('click', () => {
            menuModal.classList.remove('active');
            setTimeout(() => {
                contactModal.classList.add('active');
            }, 300);
        });

        sidebarContactBtn.addEventListener('click', () => {
            contactModal.classList.add('active');
        });
        
        closeContactModal.addEventListener('click', () => {
            contactModal.classList.remove('active');
        });
        
        // Close modals when clicking outside
        [menuModal, howItWorksModal, contactModal].forEach(modal => {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.classList.remove('active');
                }
            });
        });
        
        // Search functionality (placeholder)
        const searchBtn = document.getElementById('searchBtn');
        searchBtn.addEventListener('click', () => {
            alert('Utafutaji wa matukio unakuja hivi karibuni!');
        });
        
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                const href = this.getAttribute('href');
                if (href !== '#' && href !== '#nyumbani') {
                    e.preventDefault();
                    const target = document.querySelector(href);
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                }
            });
        });
    </script>
</body>
</html>