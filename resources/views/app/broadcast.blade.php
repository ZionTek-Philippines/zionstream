<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Broadcast — {{ $stream->title }} — Maddox</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/agora-broadcaster.js'])
    <script>
        window.AgoraBroadcastConfig = {
            channelName: @json($stream->agora_channel_name),
            uid:         {{ $stream->agora_uid ?? $stream->id }},
            streamId:    {{ $stream->id }},
        };
    </script>
</head>
<body class="bg-black text-on-surface font-body-md overflow-hidden h-screen w-screen">

{{-- ── Full-Screen Camera Background ───────────────────────── --}}
<div class="absolute inset-0 z-0 bg-black">
    <div class="absolute inset-0 bg-gradient-to-b from-black/40 via-transparent to-black/60 z-10 pointer-events-none"></div>

    <div id="local-video" class="w-full h-full"></div>

    {{-- Camera placeholder — hidden once video track plays --}}
    <div id="camera-placeholder" class="absolute inset-0 flex flex-col items-center justify-center gap-3 bg-neutral-950">
        <span class="material-symbols-outlined text-white/20 text-7xl">videocam_off</span>
        <p class="text-white/30 text-xs uppercase tracking-widest">Camera preview will appear once you go live</p>
    </div>
</div>

{{-- ── Sparkle Overlay ──────────────────────────────────────── --}}
<div class="absolute inset-0 pointer-events-none z-20" id="sparkle-container"></div>

{{-- ── Top Navigation ───────────────────────────────────────── --}}
<header class="fixed top-0 left-0 w-full z-50 flex justify-between items-center px-container-padding h-16 bg-transparent">
    <div class="flex items-center gap-4">
        <a href="{{ route('app.landing') }}" class="text-white hover:opacity-80 transition-opacity">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full border-2 border-primary overflow-hidden">
                <img alt="{{ $stream->channel->name }}"
                     class="w-full h-full object-cover"
                     src="{{ $stream->channel->thumbnail
                        ? asset('storage/'.$stream->channel->thumbnail)
                        : 'https://ui-avatars.com/api/?name='.urlencode($stream->channel->name).'&background=785a00&color=fff&bold=true' }}">
            </div>
            <div>
                <h2 class="text-white font-headline-md text-label-sm tracking-tight">{{ $stream->channel->name }}</h2>
                <p class="text-white/70 text-[10px] uppercase tracking-widest font-bold">Live Host</p>
            </div>
        </div>
    </div>

    <div class="flex items-center gap-1.5 flex-wrap justify-end max-w-[55%] sm:max-w-none">
        {{-- Streamer badge — hidden once live to save space --}}
        <div id="streamer-badge" class="bg-primary/20 border border-primary/40 px-2.5 py-1 rounded-full flex items-center gap-1.5 shrink-0">
            <span class="material-symbols-outlined text-primary text-[12px]" style="font-variation-settings: 'FILL' 1;">videocam</span>
            <span class="text-primary text-[10px] font-bold uppercase tracking-widest whitespace-nowrap">Streamer View</span>
        </div>
        {{-- LIVE badge --}}
        <div id="live-badge" class="hidden bg-error px-2.5 py-1 rounded-full flex items-center gap-1.5 pulse-live shrink-0">
            <span class="w-2 h-2 bg-white rounded-full"></span>
            <span class="text-white text-[10px] font-bold uppercase tracking-widest">LIVE</span>
        </div>
        {{-- End Stream — shown when live --}}
        <button id="end-btn"
                class="hidden bg-error/90 hover:bg-error text-white px-3 py-1.5 rounded-full text-[10px] font-bold uppercase tracking-widest flex items-center gap-1 transition-all active:scale-95 shrink-0 whitespace-nowrap">
            <span class="material-symbols-outlined text-[14px]">stop_circle</span>
            End
        </button>
        {{-- Status — shown when not live --}}
        <span id="status-msg" class="text-white/50 text-xs shrink-0">Ready to broadcast</span>
    </div>
</header>

{{-- ── Right Controls (Streamer only — same slot as viewer's action btns) ─── --}}
<div class="fixed right-4 top-1/2 -translate-y-1/2 z-40 flex flex-col gap-6">
    <button id="mute-btn"
            class="hidden w-12 h-12 rounded-full glass-panel flex items-center justify-center text-primary hover:scale-110 transition-all shadow-lg">
        <span class="material-symbols-outlined">mic</span>
    </button>

    <div id="filter-wrapper" class="hidden flex-col items-center gap-1">
        <button id="filter-btn"
                class="w-12 h-12 rounded-full glass-panel flex items-center justify-center text-white/60 hover:scale-110 transition-all shadow-lg"
                title="Cycle video filter">
            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">auto_fix_high</span>
        </button>
        <span id="filter-label" class="text-[9px] uppercase tracking-widest text-white/40">Off</span>
    </div>
</div>

{{-- ── Bottom: Go Live CTA (pre-stream) ───────────────────────── --}}
<div id="go-live-area" class="fixed bottom-0 left-0 w-full z-30 px-container-padding pb-8">
    <button id="go-live-btn"
            class="w-full bg-primary hover:bg-primary-container text-white py-5 rounded-full font-label-sm uppercase tracking-[0.2em] gold-shadow transition-all flex items-center justify-center gap-3 shadow-xl active:scale-[0.98]">
        <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">videocam</span>
        Go Live
    </button>
</div>

{{-- ── Bottom: Live Area (same layout as viewer) ──────────────── --}}
<div id="live-area" class="hidden fixed bottom-0 left-0 w-full z-30 px-container-padding pb-8 flex flex-col gap-4">

    {{-- Live Chat --}}
    <div class="max-w-xs md:max-w-md h-64 flex flex-col justify-end gap-3 overflow-hidden chat-fade-mask"
         id="chat-container">
        @if($stream->activeStreamProduct)
            <div class="chat-message glass-panel px-3 py-1 rounded-full self-start">
                <p class="text-primary text-[10px] font-bold uppercase tracking-tighter">
                    🛍 Now: {{ $stream->activeStreamProduct->product->name }}
                </p>
            </div>
        @endif
    </div>

    {{-- Active Product Spotlight (no claim button for streamer) --}}
    @if($stream->activeStreamProduct)
        @php
            $activeProduct = $stream->activeStreamProduct->product;
            $productImages = $activeProduct->images ?? [];
        @endphp
        <div class="glass-panel p-4 rounded-2xl flex items-center gap-4 max-w-lg">
            <div class="flex items-center gap-3">
                <div class="w-14 h-14 rounded-xl overflow-hidden bg-white/30 border border-primary/20 flex items-center justify-center">
                    @if(count($productImages) > 0)
                        <img alt="{{ $activeProduct->name }}"
                             class="w-full h-full object-cover"
                             src="{{ asset('storage/'.$productImages[0]) }}">
                    @else
                        <span class="material-symbols-outlined text-primary/50 text-3xl">diamond</span>
                    @endif
                </div>
                <div>
                    <h3 class="text-on-surface font-headline-md text-sm tracking-tight">{{ $activeProduct->name }}</h3>
                    <div class="flex items-center gap-2 mt-0.5">
                        <span class="text-primary font-bold text-label-sm">
                            ₱{{ number_format($stream->activeStreamProduct->effectivePrice(), 2) }}
                        </span>
                        @if($stream->activeStreamProduct->featured_price)
                            <span class="bg-primary/10 text-primary text-[9px] px-2 py-0.5 rounded-full uppercase font-bold tracking-widest">Sale</span>
                        @endif
                    </div>
                    <p class="text-white/50 text-[9px] mt-0.5">Customers can claim this item</p>
                </div>
            </div>
            <span class="text-[9px] text-primary/60 uppercase tracking-widest font-bold">Active</span>
        </div>
    @endif

    {{-- Chat Input --}}
    <div class="relative mt-2">
        <input class="w-full bg-transparent border-0 border-b border-white/30 text-white placeholder:text-white/50 focus:ring-0 focus:border-primary px-0 py-3 text-sm transition-all outline-none"
               placeholder="Say something to your viewers…"
               type="text"
               id="chat-input">
        <button class="absolute right-0 top-1/2 -translate-y-1/2 text-primary hover:scale-110 transition-transform"
                id="send-btn">
            <span class="material-symbols-outlined">send</span>
        </button>
    </div>

</div>

<script>
    // Sparkles (same as viewer)
    (function sparkles() {
        const container = document.getElementById('sparkle-container');
        function create() {
            const el = document.createElement('div');
            el.classList.add('sparkle-particle');
            const size = Math.random() * 4 + 2;
            el.style.cssText = `width:${size}px;height:${size}px;left:${Math.random()*100}%;top:${Math.random()*100}%;animation-duration:${Math.random()*2+1}s`;
            container.appendChild(el);
            setTimeout(() => el.remove(), 3000);
        }
        setInterval(create, 300);
    })();

    // Hide camera placeholder once local video track starts playing
    const observer = new MutationObserver(() => {
        const hasVideo = document.getElementById('local-video')?.querySelector('video');
        document.getElementById('camera-placeholder')?.classList.toggle('hidden', !!hasVideo);
    });
    const localVideoDiv = document.getElementById('local-video');
    if (localVideoDiv) observer.observe(localVideoDiv, { childList: true });
</script>
</body>
</html>
