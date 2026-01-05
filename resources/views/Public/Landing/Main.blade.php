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
        }
        .event-card {
            transition: all 0.3s ease;
        }
        .event-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0,0,0,0.08);
        }
        .hero-gradient {
            background: linear-gradient(135deg, #f5f3f0 0%, #faf8f6 50%, #f0ebe5 100%);
        }
        .glass-card {
            background: rgba(212, 175, 55, 0.08);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(212, 175, 55, 0.2);
        }
        .gold-glow {
            box-shadow: 0 4px 20px rgba(212, 175, 55, 0.3);
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        .floating {
            animation: float 3s ease-in-out infinite;
        }
        .mobile-menu {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }
        .mobile-menu.active {
            max-height: 400px;
        }
        .logo-container img {
            width: 150px;
            height: auto;
        }
        .nav-link {
            position: relative;
        }
        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: #d4af37;
            transition: width 0.3s ease;
        }
        .nav-link:hover::after {
            width: 100%;
        }
    </style>
</head>
<body class="text-gray-800 bg-gray-50">
    
    <!-- Top Navigation Bar -->
    <nav class="sticky top-0 z-50 bg-white/95 backdrop-blur-sm border-b border-gray-200 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <!-- Logo -->
                <a href="#nyumbani" class="logo-container">
                    <img class="logo" alt="BARI-TICKETS" src="{{asset('assets/images/logo-dark.png')}}"/>
                </a>

                <!-- Desktop Navigation -->
                <div class="hidden lg:flex items-center space-x-8 bg-gray-50">
                    <a href="#nyumbani" class="nav-link text-[#2d4563] font-medium hover:text-[#d4af37] transition-colors">Nyumbani</a>
                    <a href="#matukio" class="nav-link text-[#2d4563] font-medium hover:text-[#d4af37] transition-colors">Matukio</a>
                    <a href="#how-it-works" class="nav-link text-[#2d4563] font-medium hover:text-[#d4af37] transition-colors">Jinsi Inavyofanya Kazi</a>
                    <a href="#contact" class="nav-link text-[#2d4563] font-medium hover:text-[#d4af37] transition-colors">Wasiliana Nasi</a>
                    <a href="{{ route('login') }}" class="px-6 py-2.5 bg-[#d4af37] text-[#2d4563] rounded-lg hover:bg-[#b8942d] transition-colors font-semibold">
                        Ingia
                    </a>
                </div>

                <!-- Mobile Menu Button -->
                <button id="mobileMenuBtn" class="lg:hidden p-2 text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>

            <!-- Mobile Menu -->
            <div id="mobileMenu" class="mobile-menu lg:hidden bg-gray-50">
                <a href="#nyumbani" class="block px-4 py-3 text-sm font-medium text-[#2d4563] hover:bg-gray-50 border-b border-gray-100">Nyumbani</a>
                <a href="#matukio" class="block px-4 py-3 text-sm font-medium text-[#2d4563] hover:bg-gray-50 border-b border-gray-100">Matukio</a>
                <a href="#how-it-works" class="block px-4 py-3 text-sm font-medium text-[#2d4563] hover:bg-gray-50 border-b border-gray-100">Jinsi Inavyofanya Kazi</a>
                <a href="#contact" class="block px-4 py-3 text-sm font-medium text-[#2d4563] hover:bg-gray-50 border-b border-gray-100">Wasiliana Nasi</a>
                <a href="{{ route('login') }}" class="block mx-4 my-3 px-4 py-3 bg-[#d4af37] text-[#2d4563] rounded-lg hover:bg-[#b8942d] transition-colors font-semibold text-center">
                    Ingia
                </a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="nyumbani" class="hero-gradient relative overflow-hidden">
        <!-- Background Pattern -->
        <div class="absolute inset-0 opacity-3">
            <div class="absolute inset-0" style="background-image: repeating-linear-gradient(45deg, transparent, transparent 35px, rgba(212, 175, 55, .05) 35px, rgba(212, 175, 55, .05) 70px);"></div>
        </div>

        <div class="relative max-w-7xl mx-auto px-4 lg:px-8 py-12 lg:py-20">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <!-- Left Content -->
                <div class="text-gray-800 space-y-6 lg:space-y-8">
                    <div class="inline-block px-4 py-2 bg-[#d4af37]/15 rounded-full border border-[#d4af37]/30">
                        <span class="text-[#2d4563] text-sm font-semibold">🎉 Jukwaa la Kisasa la Tiketi</span>
                    </div>
                    
                    <h1 class="text-4xl lg:text-6xl font-bold leading-tight text-[#2d4563]">
                        Nunua Tiketi<br/>
                        <span class="text-[#d4af37]">Kirahisi</span>
                    </h1>
                    
                    <p class="text-lg lg:text-xl text-gray-600">
                        Nunua tiketi, shiriki matukio, na furahia uzoefu wa kipekee. 
                        Jukwaa la kisasa la usimamizi wa tiketi na matukio Tanzania.
                    </p>
                    
                    <div class="flex flex-col sm:flex-row gap-4">
                        <a href="#matukio" class="inline-flex items-center justify-center px-8 py-4 bg-[#d4af37] text-[#2d4563] rounded-xl font-bold text-lg hover:bg-[#b8942d] transition-all gold-glow">
                            Tazama Matukio
                            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                            </svg>
                        </a>
                        <a href="{{ route('login') }}" class="inline-flex items-center justify-center px-8 py-4 bg-[#2d4563] text-white rounded-xl font-bold text-lg hover:bg-[#1a2942] transition-all">
                            Ingia 
                        </a>
                    </div>
                </div>

                <!-- Right Content - Phone Mockup -->
                <div class="relative hidden lg:block">
                    <div class="relative z-10 floating">
                        <!-- Main Phone -->
                        <div class="relative mx-auto w-80 h-[600px] bg-[#2d4563] rounded-[3rem] shadow-2xl p-3 border-8 border-[#1a2942]">
                            <div class="w-full h-full bg-white rounded-[2.5rem] overflow-hidden">
                                <!-- Phone Screen Content -->
                                <div class="h-full flex flex-col">
                                    <!-- App Header -->
                                    <div class="bg-[#2d4563] px-6 pb-4">
                                        <img style="width: 100px;" class="logo" alt="BARI-TICKETS" src="{{asset('assets/images/logo-light.png')}}"/>
                                    </div>

                                    <!-- Ticket Card -->
                                    <div class="flex-1 bg-gradient-to-b from-[#2d4563] to-gray-50 p-4">
                                        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                                            <div class="bg-[#d4af37] px-4 py-2 flex justify-between items-center">
                                                <span class="text-[#2d4563] text-xs font-bold">TIKETI</span>
                                                <span class="text-[#2d4563] text-xs font-bold">VILIVYO</span>
                                            </div>
                                            
                                            <!-- QR Code -->
                                            <div class="p-6 flex justify-center bg-white">
                                                <div class="w-40 h-40 bg-[#2d4563] rounded-xl flex items-center justify-center">
                                                    <div class="w-32 h-32 bg-white rounded-lg grid grid-cols-8 grid-rows-8 gap-0.5 p-1">
                                                        <!-- QR Code Pattern -->
                                                        <div class="bg-[#2d4563] rounded-sm col-span-3 row-span-3"></div>
                                                        <div class="bg-white"></div>
                                                        <div class="bg-[#2d4563] rounded-sm"></div>
                                                        <div class="bg-white col-span-2"></div>
                                                        <div class="bg-[#2d4563] rounded-sm"></div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Event Info -->
                                            <div class="px-6 pb-6 space-y-3">
                                                <h3 class="text-[#2d4563] text-lg font-bold text-center">
                                                    SERENGETI MUSIC<br/>FESTIVAL 2024
                                                </h3>
                                                <div class="flex items-center justify-center gap-2 text-gray-600 text-sm">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                    </svg>
                                                    SAT, OCT 26 | 18:00
                                                </div>
                                                <div class="flex items-center justify-center gap-2 text-gray-600 text-sm">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    </svg>
                                                    UHURU GROUNDS
                                                </div>
                                                <button class="w-full bg-[#d4af37] text-[#2d4563] font-bold py-3 rounded-xl mt-4">
                                                    TAZAMA MAELEZO
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Floating Cards -->
                    <div class="absolute -top-10 -left-10 glass-card rounded-2xl p-4 shadow-xl" style="animation: float 4s ease-in-out infinite;">
                        <div class="text-[#d4af37] font-bold text-sm">✓ Usalama</div>
                        <div class="text-gray-700 text-xs">Malipo Salama</div>
                    </div>
                    
                    <div class="absolute -bottom-10 -right-10 glass-card rounded-2xl p-4 shadow-xl" style="animation: float 3.5s ease-in-out infinite; animation-delay: 0.5s;">
                        <div class="text-[#d4af37] font-bold text-sm">⚡ Haraka</div>
                        <div class="text-gray-700 text-xs">Tiketi Mara Moja</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Wave Divider -->
        <div class="absolute bottom-0 left-0 right-0">
            <svg viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M0 120L60 110C120 100 240 80 360 70C480 60 600 60 720 65C840 70 960 80 1080 85C1200 90 1320 90 1380 90L1440 90V120H1380C1320 120 1200 120 1080 120C960 120 840 120 720 120C600 120 480 120 360 120C240 120 120 120 60 120H0Z" fill="white"/>
            </svg>
        </div>
    </section>

    <!-- Featured Events Section -->
    <main id="matukio" class="py-16 lg:py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 lg:px-8">
            <!-- Section Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h2 class="text-3xl font-bold text-[#2d4563]">Matukio Yajayo</h2>
                </div>
                <a href="#matukio" class="hidden lg:inline-flex items-center gap-2 text-[#d4af37] font-semibold hover:gap-3 transition-all">
                    Tazama Yote
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                    </svg>
                </a>
            </div>

            @if($events->count() > 0)
            <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 lg:gap-6">
                @foreach($events as $event)
                <!-- Event Card -->
                <a href="{{ route('showEventPage', ['event_id' => $event->id, 'event_slug' => Str::slug($event->title)]) }}" 
                   class="event-card relative rounded-3xl overflow-hidden cursor-pointer h-72 lg:h-80 bg-white shadow-md border border-gray-100 {{ $loop->index >= 6 ? 'hidden lg:block' : '' }} {{ $loop->index >= 7 ? 'hidden xl:block' : '' }}">
                    
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
                    
                    <div class="absolute inset-0 bg-gradient-to-t from-gray-900/90 via-gray-900/40 to-transparent"></div>
                    
                    <!-- Event Badge -->
                    <div class="absolute top-4 right-4">
                        <span class="inline-block px-3 py-1 bg-[#d4af37] text-[#2d4563] text-xs font-bold rounded-full">
                            TIKETI ZINAPATIKANA
                        </span>
                    </div>
                    
                    <div class="absolute bottom-0 left-0 right-0 p-5">
                        <div class="text-xs font-semibold text-[#d4af37] mb-2">
                            {{ $event->start_date->format('M d') }}
                            @if($event->start_date->format('Y-m-d') !== $event->end_date->format('Y-m-d'))
                                - {{ $event->end_date->format('M d') }}
                            @endif
                        </div>
                        <h3 class="text-xl font-bold mb-1 leading-tight text-white">
                            {{ Str::limit($event->title, 40) }}
                        </h3>
                        <p class="text-sm text-gray-200 flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            </svg>
                            {{ $event->venue_name ?? $event->location_address_line_1 ?? 'Matukio ya Mtandaoni' }}
                        </p>
                    </div>
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

            <!-- View All Button Mobile -->
            <div class="mt-8 lg:hidden text-center">
                <a href="#" class="inline-flex items-center gap-2 text-[#d4af37] font-semibold">
                    Tazama Matukio Yote
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                    </svg>
                </a>
            </div>
        </div>
    </main>

    <!-- How It Works Section -->
    <section id="how-it-works" class="py-16 lg:py-24 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 lg:px-8">
            <div class="text-center mb-12 lg:mb-16">
                <h2 class="text-3xl lg:text-5xl font-bold text-[#2d4563] mb-4">
                    Jinsi Inavyofanya Kazi
                </h2>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                    Nunua tiketi kwa hatua tatu tu - haraka, salama, na rahisi
                </p>
            </div>

            <div class="grid md:grid-cols-3 gap-8 lg:gap-12">
                <!-- Step 1 -->
                <div class="text-center">
                    <div class="mb-6 flex justify-center">
                        <div class="relative w-24 h-24 lg:w-32 lg:h-32 bg-gradient-to-br from-[#2d4563] to-[#1a2942] rounded-full flex items-center justify-center">
                            <div class="text-center">
                                <div class="text-5xl font-bold text-[#d4af37] mb-1">01</div>
                            </div>
                        </div>
                    </div>
                    <h3 class="text-xl lg:text-2xl font-bold text-[#2d4563] mb-3">
                        Tafuta na Chagua
                    </h3>
                    <p class="text-gray-600 leading-relaxed">
                        Chunguza matukio mbalimbali yanayopatikana kwenye jukwaa la BARI-TICKETS. Chagua matukio ambayo unapendezwa nayo na angalia maelezo yote.
                    </p>
                </div>

                <!-- Step 2 -->
                <div class="text-center">
                    <div class="mb-6 flex justify-center">
                        <div class="relative w-24 h-24 lg:w-32 lg:h-32 bg-gradient-to-br from-[#2d4563] to-[#1a2942] rounded-full flex items-center justify-center">
                            <div class="text-center">
                                <div class="text-5xl font-bold text-[#d4af37] mb-1">02</div>
                            </div>
                        </div>
                    </div>
                    <h3 class="text-xl lg:text-2xl font-bold text-[#2d4563] mb-3">
                        Lipa Salama
                    </h3>
                    <p class="text-gray-600 leading-relaxed">
                        Lipa kwa njia salama kwa kutumia M-Pesa, kadi za mkopo, au njia nyingine za malipo. Tiketi yako itakufikia kwa dakika chache.
                    </p>
                </div>

                <!-- Step 3 -->
                <div class="text-center">
                    <div class="mb-6 flex justify-center">
                        <div class="relative w-24 h-24 lg:w-32 lg:h-32 bg-gradient-to-br from-[#2d4563] to-[#1a2942] rounded-full flex items-center justify-center">
                            <div class="text-center">
                                <div class="text-5xl font-bold text-[#d4af37] mb-1">03</div>
                            </div>
                        </div>
                    </div>
                    <h3 class="text-xl lg:text-2xl font-bold text-[#2d4563] mb-3">
                        Ingia kwa Tiketi
                    </h3>
                    <p class="text-gray-600 leading-relaxed">
                        Onyesha QR code yako kwenye mlango wa matukio. Tiketi yako ya dijitali itakuwezesha kuingia haraka na salama.
                    </p>    
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="py-16 lg:py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl lg:text-5xl font-bold text-[#2d4563] mb-4">
                    Wasiliana Nasi
                </h2>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                    Una maswali? Tuko hapa kukusaidia! Wasiliana nasi kwa njia yoyote inayokufaa
                </p>
            </div>

            <div class="max-w-4xl mx-auto">
                <div class="grid md:grid-cols-2 gap-8">
                    <!-- WhatsApp Card -->
                    <a href="https://wa.me/255765762688" target="_blank" class="group">
                        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-3xl p-8 border-2 border-green-200 hover:border-green-400 transition-all hover:shadow-xl">
                            <div class="flex items-center gap-4 mb-4">
                                <div class="w-16 h-16 bg-green-500 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform">
                                    <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.555 0 11.89-5.335 11.893-11.892a11.82 11.82 0 00-3.03-8.413"/>
                                    </svg>
                                    </div>
                                <div>
                                    <h3 class="text-xl font-bold text-gray-800 mb-1">WhatsApp</h3>
                                    <p class="text-sm text-gray-600">Tupigie simu au tuma ujumbe</p>
                                </div>
                            </div>
                            <p class="text-2xl font-bold text-green-600">+255 765 762688</p>
                            <div class="mt-4 flex items-center text-green-600 font-semibold">
                                Tuma Ujumbe
                                <svg class="w-5 h-5 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                                </svg>
                            </div>
                        </div>
                    </a>

                    <!-- Email Card -->
                    <a href="mailto:elibarikaneno@gmail.com" class="group">
                        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-3xl p-8 border-2 border-blue-200 hover:border-blue-400 transition-all hover:shadow-xl">
                            <div class="flex items-center gap-4 mb-4">
                                <div class="w-16 h-16 bg-blue-500 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform">
                                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-gray-800 mb-1">Email</h3>
                                    <p class="text-sm text-gray-600">Tutumie barua pepe</p>
                                </div>
                            </div>
                            <p class="text-lg font-bold text-blue-600 break-all">elibarikaneno@gmail.com</p>
                            <div class="mt-4 flex items-center text-blue-600 font-semibold">
                                Tuma Email
                                <svg class="w-5 h-5 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                                </svg>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Social Media Links -->
                <div class="mt-12 text-center">
                    <p class="text-gray-600 mb-6">Tufuate kwenye mitandao ya kijamii</p>
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
    </section>

    <!-- Footer -->
    <footer class="bg-[#2d4563] text-white py-8">
        <div class="max-w-7xl mx-auto px-4 lg:px-8 text-center">
            <p class="text-gray-300">&copy; 2026 BARI-TICKETS. Haki zote zimehifadhiwa.</p>
        </div>
    </footer>

    <script>
        // Mobile menu toggle
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const mobileMenu = document.getElementById('mobileMenu');
        
        mobileMenuBtn.addEventListener('click', () => {
            mobileMenu.classList.toggle('active');
        });

        // Close mobile menu when clicking on a link
        const mobileMenuLinks = mobileMenu.querySelectorAll('a');
        mobileMenuLinks.forEach(link => {
            link.addEventListener('click', () => {
                mobileMenu.classList.remove('active');
            });
        });

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>
</html>