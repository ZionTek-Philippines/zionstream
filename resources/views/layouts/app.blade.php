<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>@yield('title', 'AURELIAN') — ZionStream</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>

<body class="bg-surface text-on-surface font-body-md selection:bg-primary-container selection:text-on-primary-container">

{{-- ── Top App Bar ─────────────────────────────────────────── --}}
<header class="fixed top-0 left-0 w-full z-50 flex justify-between items-center px-container-padding h-16 bg-surface gold-shadow">
    <button class="text-primary hover:opacity-80 transition-opacity">
        <span class="material-symbols-outlined">menu</span>
    </button>
    <span class="font-display-lg text-headline-lg-mobile text-primary tracking-[0.2em] uppercase">Maddox</span>
    <button class="text-primary hover:opacity-80 transition-opacity">
        <span class="material-symbols-outlined">shopping_bag</span>
    </button>
</header>

@yield('content')

{{-- ── Bottom Navigation ───────────────────────────────────── --}}
<nav class="fixed bottom-0 left-0 w-full z-50 flex justify-around items-end pb-6 px-4 h-20 bg-white/70 backdrop-blur-xl border-t-[0.5px] border-outline-variant/30 shadow-[0px_-4px_20px_rgba(200,162,74,0.1)] rounded-t-3xl">
    @php
        $nav = [
            ['icon' => 'home',        'label' => 'Home',     'route' => 'app.landing'],
            ['icon' => 'chat_bubble', 'label' => 'Chat',     'route' => 'app.chat'],
            ['icon' => 'storefront',  'label' => 'Shop',     'route' => 'app.shop'],
            ['icon' => 'settings',    'label' => 'Settings', 'route' => 'app.settings'],
        ];
    @endphp

    @foreach($nav as $index => $item)
        {{-- Insert the centre Live FAB between Chat and Shop --}}
        @if($index === 2)
            {{-- Centre Live FAB — role-aware --}}
            @auth
                @if(auth()->user()->hasRole('streamer'))
                    @php
                        $myStream = auth()->user()->channel?->streams()
                            ->whereIn('status', ['live', 'scheduled'])
                            ->latest()
                            ->first();
                    @endphp
                    <a href="{{ $myStream ? route('app.broadcast', $myStream) : '#' }}"
                    class="scale-125 bg-primary text-on-primary rounded-full -translate-y-4 shadow-lg p-4 animate-pulse-gold flex items-center justify-center transition-transform active:scale-95"
                    title="Go Live">
                        <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">videocam</span>
                    </a>
                @else
                    <a href="{{ route('app.landing') }}"
                    class="scale-125 bg-primary text-on-primary rounded-full -translate-y-4 shadow-lg p-4 animate-pulse-gold flex items-center justify-center transition-transform active:scale-95"
                    title="Watch Live">
                        <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">live_tv</span>
                    </a>
                @endif
            @else
                <a href="{{ route('app.auth.login') }}"
                class="scale-125 bg-primary text-on-primary rounded-full -translate-y-4 shadow-lg p-4 animate-pulse-gold flex items-center justify-center transition-transform active:scale-95">
                    <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">live_tv</span>
                </a>
            @endauth

        @endif

        @php $active = request()->routeIs($item['route']); @endphp
        <a href="{{ Route::has($item['route']) ? route($item['route']) : '#' }}"
           class="flex flex-col items-center justify-center transition-colors {{ $active ? 'text-primary font-bold' : 'text-on-surface-variant/60 hover:text-primary' }}">
            <span class="material-symbols-outlined">{{ $item['icon'] }}</span>
            <span class="font-label-sm tracking-widest uppercase mt-1">{{ $item['label'] }}</span>
        </a>
    @endforeach
</nav>

@stack('scripts')
<script>
    document.querySelectorAll('.group').forEach(card => {
        card.addEventListener('mouseenter', () => card.classList.add('shadow-2xl'));
        card.addEventListener('mouseleave', () => card.classList.remove('shadow-2xl'));
    });
</script>
</body>
</html>
