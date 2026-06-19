<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Broadcast — {{ $stream->title }}</title>
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
<body class="bg-black text-white font-body-md h-screen w-screen overflow-hidden flex flex-col">

{{-- Top Bar --}}
<header class="flex items-center justify-between px-6 h-14 bg-black/80 border-b border-white/10 shrink-0">
    <div class="flex items-center gap-3">
        <span class="font-display-lg text-primary text-label-sm tracking-[0.2em] uppercase">AURELIAN</span>
        <span class="text-white/40">·</span>
        <span class="text-white/70 text-sm truncate max-w-xs">{{ $stream->title }}</span>
    </div>
    <div class="flex items-center gap-3">
        <div id="live-badge" class="hidden bg-error px-3 py-1 rounded-full flex items-center gap-2 pulse-live">
            <span class="w-2 h-2 bg-white rounded-full"></span>
            <span class="text-white text-[10px] font-bold uppercase tracking-widest">LIVE</span>
        </div>
        <span id="status-msg" class="text-white/50 text-xs">Ready to broadcast</span>
    </div>
</header>

{{-- Main Area --}}
<div class="flex flex-1 overflow-hidden">

    {{-- Camera Preview --}}
    <div class="flex-1 relative bg-neutral-950 flex items-center justify-center">
        <div id="local-video" class="w-full h-full"></div>

        {{-- Placeholder when not live --}}
        <div id="camera-placeholder" class="absolute inset-0 flex flex-col items-center justify-center gap-4 text-white/30">
            <span class="material-symbols-outlined text-6xl">videocam_off</span>
            <p class="text-sm">Camera preview will appear here</p>
        </div>

        {{-- Active product banner --}}
        @if($stream->activeStreamProduct)
            <div class="absolute top-4 left-4 right-4 glass-panel p-3 rounded-xl flex items-center justify-between max-w-sm">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-primary text-sm">shopping_bag</span>
                    <div>
                        <p class="text-on-surface text-xs font-bold">{{ $stream->activeStreamProduct->product->name }}</p>
                        <p class="text-primary text-xs">₱{{ number_format($stream->activeStreamProduct->effectivePrice(), 2) }}</p>
                    </div>
                </div>
                <span class="text-[9px] text-white/50 uppercase">Active</span>
            </div>
        @endif

        {{-- Controls --}}
        <div class="absolute bottom-8 left-1/2 -translate-x-1/2 flex items-center gap-4">

            <button id="go-live-btn"
                    class="bg-primary hover:bg-primary-container text-white px-10 py-4 rounded-full font-label-sm uppercase tracking-widest transition-all shadow-xl flex items-center gap-2">
                <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">videocam</span>
                Go Live
            </button>

            <button id="mute-btn"
                    class="hidden w-14 h-14 bg-white/10 hover:bg-white/20 rounded-full flex items-center justify-center transition-all">
                <span class="material-symbols-outlined">mic</span>
            </button>

            {{-- Filter toggle — hidden until live --}}
            <div id="filter-wrapper" class="hidden flex-col items-center gap-1">
                <button id="filter-btn"
                        class="w-14 h-14 bg-white/10 text-white/60 rounded-full flex items-center justify-center transition-all hover:bg-white/20"
                        title="Cycle video filter">
                    <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">auto_fix_high</span>
                </button>
                <span id="filter-label" class="text-[9px] uppercase tracking-widest text-white/40">Off</span>
            </div>

            <button id="end-btn"
                    class="hidden bg-error hover:bg-error/80 text-white px-8 py-4 rounded-full font-label-sm uppercase tracking-widest transition-all flex items-center gap-2">
                <span class="material-symbols-outlined">stop_circle</span>
                End Stream
            </button>

        </div>



    </div>

    {{-- Chat Sidebar --}}
    <div class="w-80 bg-neutral-900 border-l border-white/10 flex flex-col">
        <div class="px-4 py-3 border-b border-white/10 flex items-center gap-2">
            <span class="material-symbols-outlined text-sm text-primary">chat</span>
            <span class="text-xs font-bold uppercase tracking-widest text-white/60">Live Chat</span>
        </div>
        <div class="flex-1 overflow-y-auto p-4 space-y-3 text-sm" id="broadcast-chat">
            <p class="text-white/30 text-xs text-center">Chat messages will appear here once Reverb is wired up.</p>
        </div>
        <div class="p-3 border-t border-white/10">
            <div class="relative">
                <input type="text"
                       placeholder="Reply to chat…"
                       class="w-full bg-white/5 border border-white/10 rounded-lg text-white placeholder:text-white/30 text-sm px-3 py-2 pr-10 focus:ring-1 focus:ring-primary outline-none">
                <button class="absolute right-2 top-1/2 -translate-y-1/2 text-primary">
                    <span class="material-symbols-outlined text-sm">send</span>
                </button>
            </div>
        </div>
    </div>

</div>

<script>
    // Hide camera placeholder when video starts
    const observer = new MutationObserver(() => {
        const hasVideo = document.getElementById('local-video')?.querySelector('video');
        document.getElementById('camera-placeholder')?.classList.toggle('hidden', !!hasVideo);
    });
    const localVideoDiv = document.getElementById('local-video');
    if (localVideoDiv) observer.observe(localVideoDiv, { childList: true });
</script>
</body>
</html>
