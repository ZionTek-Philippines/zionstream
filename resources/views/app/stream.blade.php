<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>{{ $stream->title }} — AURELIAN</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/agora-viewer.js'])
    <script>
    window.AgoraStreamConfig = {
        channelName: @json($stream->agora_channel_name),
    };
</script>

</head>
<body class="bg-background text-on-surface font-body-md overflow-hidden h-screen w-screen">

{{-- ── Full-Screen Video Background ────────────────────────── --}}
{{-- TODO: Replace img with Agora RTC <div id="agora-video"> once streaming is wired --}}
{{-- ── Full-Screen Video Background ────────────────────────── --}}
<div class="absolute inset-0 z-0 bg-black">
    <div class="absolute inset-0 bg-gradient-to-b from-black/40 via-transparent to-black/60 z-10 pointer-events-none"></div>

    {{-- Agora fills this div when the host publishes video --}}
    <div id="agora-video" class="w-full h-full"></div>

    {{-- Loading overlay — removed by JS once video starts --}}
    <div id="agora-loading" class="absolute inset-0 flex flex-col items-center justify-center gap-4 bg-black">
        @if($stream->thumbnail)
            <img alt="{{ $stream->title }}"
                 class="absolute inset-0 w-full h-full object-cover opacity-40"
                 src="{{ asset('storage/'.$stream->thumbnail) }}">
        @endif
        <div class="relative z-10 flex flex-col items-center gap-3">
            <div class="w-8 h-8 border-2 border-primary border-t-transparent rounded-full animate-spin"></div>
            <p class="text-white/60 text-xs tracking-widest uppercase">Connecting to stream…</p>
        </div>
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
                        : 'https://ui-avatars.com/api/?name='.urlencode($stream->channel->name).'&background=785a00&color=fff&bold=true' }}"
>
            </div>
            <div>
                <h2 class="text-white font-headline-md text-label-sm tracking-tight">{{ $stream->channel->name }}</h2>
                <p class="text-white/70 text-[10px] uppercase tracking-widest font-bold">Live Host</p>
            </div>
        </div>
    </div>
    <div class="flex items-center gap-2">
        <div class="bg-error px-3 py-1 rounded-full flex items-center gap-2 pulse-live">
            <span class="w-2 h-2 bg-white rounded-full"></span>
            <span class="text-white text-[10px] font-bold uppercase tracking-widest">LIVE</span>
        </div>
        <div class="glass-panel px-3 py-1 rounded-full flex items-center gap-2">
            <span class="material-symbols-outlined text-[14px] text-primary">visibility</span>
            <span class="text-on-surface-variant text-[10px] font-bold tracking-widest">
                {{ number_format($stream->peak_viewer_count) }}
            </span>
        </div>
    </div>
</header>

{{-- ── Right Action Buttons ─────────────────────────────────── --}}
<div class="fixed right-4 top-1/2 -translate-y-1/2 z-40 flex flex-col gap-6">
    <button class="w-12 h-12 rounded-full glass-panel flex items-center justify-center text-primary hover:scale-110 transition-all shadow-lg"
            id="heart-btn" title="Like">
        <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">favorite</span>
    </button>
    <button class="w-12 h-12 rounded-full glass-panel flex items-center justify-center text-primary hover:scale-110 transition-transform shadow-lg"
            title="Share">
        <span class="material-symbols-outlined">share</span>
    </button>
    <button class="w-12 h-12 rounded-full glass-panel flex items-center justify-center text-primary hover:scale-110 transition-transform shadow-lg"
            id="celebrate-btn" title="Celebrate">
        <span class="material-symbols-outlined">celebration</span>
    </button>
    <button class="w-12 h-12 rounded-full glass-panel flex items-center justify-center text-primary hover:scale-110 transition-transform shadow-lg"
            title="Shop">
        <span class="material-symbols-outlined">shopping_bag</span>
    </button>
</div>

{{-- ── Bottom Content Area ──────────────────────────────────── --}}
<div class="fixed bottom-0 left-0 w-full z-30 px-container-padding pb-8 flex flex-col gap-4">

    {{-- Live Chat --}}
    {{-- TODO: Wire to Laravel Echo / Reverb for real-time messages --}}
    <div class="max-w-xs md:max-w-md h-64 flex flex-col justify-end gap-3 overflow-hidden chat-fade-mask"
         id="chat-container">
        <div class="chat-message">
            <div class="flex items-start gap-2">
                <span class="text-primary font-bold text-label-sm">Sarah_W:</span>
                <p class="text-white text-sm">The clarity on that stone is incredible! ✨</p>
            </div>
        </div>
        <div class="chat-message border-l-2 border-primary pl-3">
            <div class="flex items-start gap-2">
                <span class="text-primary-fixed-dim font-bold text-label-sm">Mark_Jewels:</span>
                <p class="text-white text-sm">Is this a limited edition piece?</p>
            </div>
        </div>
        <div class="chat-message">
            <div class="flex items-start gap-2">
                <span class="text-primary font-bold text-label-sm">LuxuryCollector:</span>
                <p class="text-white text-sm">Stunning gold-tone shadows in the facets.</p>
            </div>
        </div>
        @if($stream->activeStreamProduct)
            <div class="chat-message glass-panel px-3 py-1 rounded-full self-start">
                <p class="text-primary text-[10px] font-bold uppercase tracking-tighter">
                    🛍 Now: {{ $stream->activeStreamProduct->product->name }}
                </p>
            </div>
        @endif
    </div>

    {{-- Active Product Spotlight --}}
    @if($stream->activeStreamProduct)
        @php
            $activeProduct    = $stream->activeStreamProduct->product;
            $productImages    = $activeProduct->images ?? [];
            $claimKeyword     = $stream->claim_keywords[0] ?? 'mine';
        @endphp
        <div class="glass-panel p-4 rounded-2xl flex items-center justify-between gap-4 max-w-lg">
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
                    <p class="text-white/50 text-[9px] mt-0.5">Type "{{ $claimKeyword }}" to claim</p>
                </div>
            </div>
            <button class="bg-primary hover:bg-primary-container text-white px-5 py-3 rounded-full font-label-sm text-[10px] tracking-widest uppercase transition-colors shadow-lg active:scale-95 duration-200"
                    id="claim-btn">
                CLAIM
            </button>
        </div>
    @endif

    {{-- Comment / Claim Input --}}
    <div class="relative mt-2">
        <input class="w-full bg-transparent border-0 border-b border-white/30 text-white placeholder:text-white/50 focus:ring-0 focus:border-primary px-0 py-3 text-sm transition-all outline-none"
               placeholder='Say something… type "{{ $stream->claim_keywords[0] ?? "mine" }}" to claim'
               type="text"
               id="chat-input">
        <button class="absolute right-0 top-1/2 -translate-y-1/2 text-primary hover:scale-110 transition-transform"
                id="send-btn">
            <span class="material-symbols-outlined">send</span>
        </button>
    </div>

</div>

<script>
    // Atmospheric sparkles
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

    // Heart toggle
    const heartBtn  = document.getElementById('heart-btn');
    let hearted = true;
    heartBtn.addEventListener('click', () => {
        hearted = !hearted;
        heartBtn.querySelector('.material-symbols-outlined').style.fontVariationSettings =
            hearted ? "'FILL' 1, 'wght' 400" : "'FILL' 0, 'wght' 400";
        heartBtn.classList.toggle('bg-error/20', hearted);
        heartBtn.classList.toggle('scale-125');
        setTimeout(() => heartBtn.classList.remove('scale-125'), 200);
    });

    // Celebrate burst
    const celebrateBtn = document.getElementById('celebrate-btn');
    celebrateBtn.addEventListener('click', () => {
        celebrateBtn.classList.add('scale-125', 'rotate-12');
        setTimeout(() => celebrateBtn.classList.remove('scale-125', 'rotate-12'), 300);
    });

    // Claim shortcut — typing the claim keyword and hitting Enter
    @if($stream->activeStreamProduct)
    const chatInput   = document.getElementById('chat-input');
    const claimKeyword = @json($stream->claim_keywords ?? ['mine']);
    chatInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && claimKeyword.includes(this.value.trim().toLowerCase())) {
            document.getElementById('claim-btn')?.classList.add('scale-110', 'bg-primary-container');
            setTimeout(() => document.getElementById('claim-btn')?.classList.remove('scale-110', 'bg-primary-container'), 400);
        }
    });
    @endif

    // Simulated chat (placeholder until Reverb is wired)
    const msgs = [
        { user: 'Aurelia_Fan',    text: 'That shimmer is divine! ❤️' },
        { user: 'JewelHunt',      text: 'Can we see the side profile?' },
        { user: 'Elena_V',        text: 'Absolutely gorgeous craftsmanship.' },
        { user: 'DiamondLover',   text: 'Is this 18k or 22k gold?' },
    ];
    function addChatMessage() {
        const container = document.getElementById('chat-container');
        const msg = msgs[Math.floor(Math.random() * msgs.length)];
        const div = document.createElement('div');
        div.className = 'chat-message border-l-2 border-primary/40 pl-3 opacity-0 translate-y-4 transition-all duration-500';
        div.innerHTML = `<div class="flex items-start gap-2">
            <span class="text-primary font-bold text-label-sm">${msg.user}:</span>
            <p class="text-white text-sm">${msg.text}</p>
        </div>`;
        container.appendChild(div);
        requestAnimationFrame(() => {
            div.classList.remove('opacity-0', 'translate-y-4');
        });
        if (container.children.length > 10) container.removeChild(container.firstChild);
    }
    setInterval(addChatMessage, 4000);
</script>

</body>
</html>
