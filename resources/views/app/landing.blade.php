@extends('layouts.app')

@section('title', 'Home')

@section('content')
<main class="pt-20 pb-32 max-w-[1200px] mx-auto px-container-padding">

    {{-- ── Welcome + Profile ──────────────────────────────────── --}}
    <section class="flex items-center justify-between mb-8">
        <div>
            <h2 class="font-headline-md text-headline-md text-on-surface mb-1">Welcome back,</h2>
            <p class="font-display-lg text-headline-lg text-primary-container font-semibold">Julianna Thorne</p>
        </div>
        <div class="relative group cursor-pointer">
            <div class="w-14 h-14 rounded-full border-[0.5px] border-outline-variant p-1 group-hover:border-primary transition-colors">
                <img alt="User Profile"
                     class="w-full h-full rounded-full object-cover"
                     src="https://lh3.googleusercontent.com/aida-public/AB6AXuBq8Xe-5vXK1mEdVVeuMezrcJGgVDlidAL7gMdC9YwuaPvjD6Dro0FfAwZcLDGRO9JcWowsbNE_2AzFeHZeZzQLQWCnXtXS93JGE36UE_Lfu3vag1hcXVBhuuNT6e688F_nP4NSnwcUcJN-Hh_3MBmSflIm0qO7GTIh1n6sfn6VXnzR7GxnVKQilx-asWCgl3TQPNk1fS8THm7nDKy3fe3OyFrc7r6rVE9hS0FJwebO-qmH5-o7FNGkpcyiYnqDgkDjtYMMXi7d1g">
            </div>
            <span class="absolute bottom-0 right-0 w-4 h-4 bg-primary border-2 border-surface rounded-full"></span>
        </div>
    </section>

    {{-- ── Hero Stream Banner ──────────────────────────────────── --}}
    <section class="mb-8 relative w-full h-[220px] rounded-2xl overflow-hidden gold-shadow group cursor-pointer">
        @if($heroStream)
            <img src="{{ $heroStream->thumbnail ? asset('storage/'.$heroStream->thumbnail) : 'https://lh3.googleusercontent.com/aida-public/AB6AXuBH4BTpAbUQqPAkfZRmKGE-Kx6m95I9Dl3ZcBQcSBjET48HO7V-1zroTzI-_OpmbhMZlF4ZUFeH5ZaloFi2gyrAdxwm3kDUebZuuo9QMPaTwb4aW8qgqYpuX-D0M4L4dXXOHsIchRFDuK9I6cSp7fpfoHix1I9eLErgXTS-bQmOoZGDqBnE-i1OZz1TI9C0UB2unLlclddbu8eoyWcHq18g8BLDiuLPq4QOgiGOD3foS24LE7H_Sdp7ZUla41UmUlzAyXweNb_Y7A' }}"
                 alt="{{ $heroStream->title }}"
                 class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
            <div class="absolute inset-0 bg-black/20 flex items-center justify-center">
                <div class="w-16 h-16 bg-white/70 rounded-full flex items-center justify-center backdrop-blur-sm transition-transform group-hover:scale-110">
                    <span class="material-symbols-outlined text-primary text-4xl" style="font-variation-settings: 'FILL' 1;">play_arrow</span>
                </div>
            </div>
            <div class="absolute top-4 left-4 glass-champagne px-3 py-1 rounded-full flex items-center gap-2">
                <span class="w-2 h-2 bg-red-600 rounded-full animate-pulse"></span>
                <span class="font-label-sm text-on-surface text-[10px] uppercase">
                    Live Now · {{ number_format($heroStream->peak_viewer_count) }} Watching
                </span>
            </div>
        @else
            <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuBH4BTpAbUQqPAkfZRmKGE-Kx6m95I9Dl3ZcBQcSBjET48HO7V-1zroTzI-_OpmbhMZlF4ZUFeH5ZaloFi2gyrAdxwm3kDUebZuuo9QMPaTwb4aW8qgqYpuX-D0M4L4dXXOHsIchRFDuK9I6cSp7fpfoHix1I9eLErgXTS-bQmOoZGDqBnE-i1OZz1TI9C0UB2unLlclddbu8eoyWcHq18g8BLDiuLPq4QOgiGOD3foS24LE7H_Sdp7ZUla41UmUlzAyXweNb_Y7A"
                 alt="Stream Preview"
                 class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-black/20 flex items-center justify-center">
                <div class="w-16 h-16 bg-white/70 rounded-full flex items-center justify-center backdrop-blur-sm">
                    <span class="material-symbols-outlined text-primary text-4xl" style="font-variation-settings: 'FILL' 1;">play_arrow</span>
                </div>
            </div>
            <div class="absolute top-4 left-4 glass-champagne px-3 py-1 rounded-full flex items-center gap-2">
                <span class="font-label-sm text-on-surface-variant text-[10px] uppercase">No Stream Live</span>
            </div>
        @endif
    </section>

    {{-- ── Search ──────────────────────────────────────────────── --}}
    <section class="mb-section-gap">
        <div class="relative group">
            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-outline group-focus-within:text-primary transition-colors material-symbols-outlined">search</span>
            <input class="w-full pl-12 pr-4 py-4 bg-transparent border-b border-outline-variant focus:border-primary focus:ring-0 transition-all font-body-md text-on-surface outline-none"
                   placeholder="Search live auctions, artisans, or collections..."
                   type="text">
        </div>
    </section>

    {{-- ── Featured Auctions Carousel ─────────────────────────── --}}
    <section class="mb-section-gap">
        <div class="flex items-end justify-between mb-6">
            <h3 class="font-headline-md text-headline-md border-l-4 border-primary pl-4">Featured Auctions</h3>
            <a class="font-label-sm text-primary uppercase hover:tracking-widest transition-all" href="#">View All</a>
        </div>
        <div class="flex gap-gutter overflow-x-auto no-scrollbar snap-x pb-4">
            <div class="min-w-[300px] md:min-w-[600px] aspect-video relative rounded-xl overflow-hidden snap-center gold-shadow group cursor-pointer">
                <img class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                     src="https://lh3.googleusercontent.com/aida-public/AB6AXuBrddCU9J_8v4oMNnmULsHhkUEkh3XveMQH17_1dxAjmy4bmDSLTkPdLvPqXR03gToMajhya9AVVmd_pbSIUs8VwLk4-iP3N9QRtnbnPpHl4fXt4SWeXq0jyJNcFANVBhE7emZrjXoiAUPwqSdjx-1pkUndavKoJsG7U6_OvFrsYBzXp3bE1ApwYJ3CNxAS1d4wKV5OHP8TANTUt4kc13MFKmZmaKbGJox_5RDqJcS_Sp1SOSR2gesPHFM3WoRQ9ZXwK5fAV5KeUw"
                     alt="The Emerald Constellation Auction">
                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent"></div>
                <div class="absolute top-4 left-4 glass-champagne px-3 py-1 rounded-full flex items-center gap-2">
                    <span class="w-2 h-2 bg-red-600 rounded-full animate-pulse"></span>
                    <span class="font-label-sm text-on-surface text-[10px] uppercase">Live · 1.2k Viewing</span>
                </div>
                <div class="absolute bottom-6 left-6 right-6">
                    <p class="font-label-sm text-primary-fixed uppercase mb-2">Heritage Collection</p>
                    <h4 class="font-headline-lg-mobile text-white mb-2">The Emerald Constellation Auction</h4>
                    <div class="flex items-center gap-4">
                        <button class="bg-primary hover:bg-primary-container text-white px-6 py-2 rounded-full font-label-sm uppercase transition-all shadow-lg">Join Room</button>
                        <span class="text-white/80 font-body-md">Current Bid: $14,200</span>
                    </div>
                </div>
            </div>
            <div class="min-w-[300px] md:min-w-[600px] aspect-video relative rounded-xl overflow-hidden snap-center gold-shadow group cursor-pointer">
                <img class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                     src="https://lh3.googleusercontent.com/aida-public/AB6AXuCTbcW_9hvTAOXN_THpCuAUei5MIRRF8NSSOBrkr8kiWUmoBCw549QPNmYyleBF7I7SXWf7MLlr-NJ-986-_-KPB3jCwwQRdSa-M3JsEfDEgShVvwoSOIGu5ilrZO4kTjp2MpAQUkyq5Mk6lR1vvRej9__IEnZFG7t-_kVRvYAgYgW1BA53SP5pFt0KJ9qvDtTsHycCq1jPyH3ksJjjB7q0j1fuHQ5XgOtbwnZojAmDwq8GmnsBPHitnj6mdbs_T9yYFEgs48nFVw"
                     alt="Masterclass: Setting the Solitaire">
                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent"></div>
                <div class="absolute bottom-6 left-6">
                    <p class="font-label-sm text-primary-fixed uppercase mb-2">Artisan Spotlight</p>
                    <h4 class="font-headline-lg-mobile text-white mb-2">Masterclass: Setting the Solitaire</h4>
                </div>
            </div>
        </div>
    </section>

    {{-- ── Happening Now (Dynamic) ─────────────────────────────── --}}
    <section class="mb-section-gap">
        <h3 class="font-headline-md text-headline-md mb-8 border-l-4 border-primary pl-4">Happening Now</h3>
        @if($liveStreams->isNotEmpty())
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                @foreach($liveStreams as $stream)
                    <div class="space-y-3 group cursor-pointer">
                        <div class="aspect-[4/5] relative rounded-xl overflow-hidden gold-shadow">
                            <img class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                                 src="{{ $stream->thumbnail ? asset('storage/'.$stream->thumbnail) : 'https://lh3.googleusercontent.com/aida-public/AB6AXuDp_5M8OAPc1eA0Tt45VpZjdP_ZqVQJtmONPVilDKS02c1YNnnJKVQHD2v8qMhzhcNyt5gMVTclv6VpsksU3TyTHq6eyt3J_IJBfbvwLWRHvGuEwxOwTibQo_9T0NoXgIobS8i3GzcTUXhBXu1zeizDs8wswlSP57JGiwuUvsWpyenqTljLSj1qa8dD0Lplp9LO9Vd863GNENB1mp3x43GqHlQ57BjCk3XtO009WliywN68MH7ypLQsB4Gwu-7NC1QeCbtwhECnRQ' }}"
                                 alt="{{ $stream->title }}">
                            <div class="absolute top-3 right-3 glass-champagne px-2 py-0.5 rounded-full flex items-center gap-1 scale-90">
                                <span class="w-1.5 h-1.5 bg-red-600 rounded-full animate-pulse"></span>
                                <span class="text-[9px] font-bold uppercase">Live</span>
                            </div>
                        </div>
                        <div>
                            <h5 class="font-body-md font-semibold text-on-surface truncate">{{ $stream->title }}</h5>
                            <p class="text-label-sm text-outline-variant uppercase">{{ $stream->channel->name }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-16">
                <span class="material-symbols-outlined text-5xl block mb-3 text-outline-variant">live_tv</span>
                <p class="font-body-md text-on-surface-variant">No streams live right now.</p>
                <p class="font-label-sm text-outline mt-1">Check back soon or browse upcoming events below.</p>
            </div>
        @endif
    </section>

    {{-- ── VIP Membership Banner ───────────────────────────────── --}}
    <section class="mb-section-gap">
        <div class="relative w-full h-48 rounded-2xl overflow-hidden bg-[#1c1b1b] flex items-center gold-shadow">
            <div class="relative z-10 px-8 py-6 w-full md:w-2/3">
                <span class="font-label-sm text-primary-fixed uppercase tracking-[0.3em] mb-4 block">Membership</span>
                <h3 class="font-headline-lg text-white mb-2">AURELIAN PRESTIGE</h3>
                <p class="text-white/60 font-body-md mb-6 max-w-sm">Early access to limited releases, zero-commission bidding, and private concierge services.</p>
                <button class="bg-primary text-white font-label-sm uppercase px-8 py-3 rounded-full hover:bg-primary-container transition-all">Upgrade Now</button>
            </div>
        </div>
    </section>

    {{-- ── Signature Collections (Bento) ──────────────────────── --}}
    <section class="mb-section-gap">
        <h3 class="font-headline-md text-headline-md mb-8 border-l-4 border-primary pl-4">Signature Collections</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 h-[600px]">
            <div class="md:col-span-2 relative group overflow-hidden rounded-2xl gold-shadow">
                <img class="w-full h-full object-cover transition-transform duration-1000 group-hover:scale-105"
                     src="https://lh3.googleusercontent.com/aida-public/AB6AXuBPcxigxJzvoYZork1eZ7mgpSGzFMdhVhadgPn_mLbm18m2LGHS7okLICI7PcHJKeBC81vsw_g2y1zlVXPhKO4cGQKBt9c2HSgKPIEeKogfYh5KvVBoqkWQ-sxTBVmoqZaEsUlMfFtwOvda6Q1aT9usu5aOinmXX88mVrhNY5bxjl6U8FwpBP3cvs-dX8A4bqr_pOVZwrinRjRiIwUiWL6VC6JeQp-9fuaKbOcHBjgBKnA897mikTH8v097gXAfL8Brjb1rAqiStg"
                     alt="The Celestial Suite">
                <div class="absolute inset-0 bg-black/20 group-hover:bg-black/10 transition-colors"></div>
                <div class="absolute bottom-8 left-8 text-white">
                    <h4 class="font-display-lg text-headline-lg mb-2">The Celestial Suite</h4>
                    <p class="font-body-md text-white/80 max-w-md">Our most ambitious diamond collection inspired by the night sky.</p>
                    <button class="mt-4 border-b border-white text-white font-label-sm uppercase py-1 hover:tracking-widest transition-all">Explore Gallery</button>
                </div>
            </div>
            <div class="flex flex-col gap-6">
                <div class="flex-1 relative group overflow-hidden rounded-2xl gold-shadow">
                    <img class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
                         src="https://lh3.googleusercontent.com/aida-public/AB6AXuBW2HOELqgHO5e-Hl1lLMMvV6SJ3YobtVJJ3fbL6RF6IwTmk07CUqBAgP48J8lJkRU6h6fI96HqkqiIPgVKMBAypqDI5EXulvsppjbP2klv6RsTGQaFbsGNPhKMeRLaShSKOP7pQWe4b2VoFr8Oj3-pUYIckHgzEbUsJurehvHCGHEUa3664zNmhzmEfV-NrhEIv2M_L6Uex42F4U1uUerI7ydu_r-0-R-95yusXPhzpGv3jaAbTeANhS467qBjVgfz_sanCOyy5Q"
                         alt="Essential Gold">
                    <div class="absolute inset-0 bg-black/10"></div>
                    <div class="absolute bottom-4 left-4 text-white">
                        <h5 class="font-headline-md text-body-lg">Essential Gold</h5>
                    </div>
                </div>
                <div class="flex-1 relative group overflow-hidden rounded-2xl gold-shadow">
                    <img class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
                         src="https://lh3.googleusercontent.com/aida-public/AB6AXuBzClbSeA2N_QYo1yZqU507XtQ9HSvwQDLFei8KaGAiri7OCoryKmB2qqPrpKN7Abu0XteexvIsGqxQLd9UHFQ7hqAoJ1B8cDRSgdZJ5iB5d2wo3cLuyhoJ6fN2nDpQVpFVfZ6E5_nRvIN1hteqvUNPvsIGum8BowCUClMlbOP5LCaLFmuAVzxYCzSskPrl-JBJOQ6s-YGZCzm5gIJBVDxRgIugqdsrUQNCkEF8dtTmiKM-M3cDHANrmj81hIrrPhy0jZYcVsbWAQ"
                         alt="Rare Rubies">
                    <div class="absolute inset-0 bg-black/10"></div>
                    <div class="absolute bottom-4 left-4 text-white">
                        <h5 class="font-headline-md text-body-lg">Rare Rubies</h5>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ── Upcoming Events ─────────────────────────────────────── --}}
    <section class="mb-section-gap">
        <div class="flex items-end justify-between mb-8">
            <h3 class="font-headline-md text-headline-md border-l-4 border-primary pl-4">Upcoming Events</h3>
        </div>
        <div class="space-y-4">
            <div class="glass-champagne p-4 rounded-xl flex items-center justify-between hover:border-primary transition-all cursor-pointer">
                <div class="flex items-center gap-6">
                    <div class="w-16 h-16 rounded-lg overflow-hidden bg-surface-container">
                        <img class="w-full h-full object-cover"
                             src="https://lh3.googleusercontent.com/aida-public/AB6AXuAZnLcY1-oDbK0J3OxXd8DiNg0b3BmiUD7PJo-D_JDOWk2ScBzCeOW8HRM3mpSTTl3jx_0ZL2ZP522a08fyPdFZB-MG1xf0WFI8H_FN_et3yrOciswHKCitYXQ6A83K7HJ-sU6_KvYh5KtIdT0AOuQDinic85eyFiSunQc4tn1kMVEEE-eg3dnjIIJDgXnKiiH0KzXoJ38rjwRNbSAHZhetWECMNFnO_wk44Cw59B-R4oZlkWbm6k0xG9SB9Id5CCFXOfNZ7BK7kw"
                             alt="Modernist Gold Revival">
                    </div>
                    <div>
                        <h5 class="font-body-md font-bold text-on-surface">Modernist Gold Revival</h5>
                        <p class="text-label-sm text-outline">Tomorrow · 10:00 AM</p>
                    </div>
                </div>
                <button class="text-primary font-label-sm uppercase tracking-widest flex items-center gap-2">
                    <span class="material-symbols-outlined">notifications</span>
                    Remind Me
                </button>
            </div>
            <div class="glass-champagne p-4 rounded-xl flex items-center justify-between hover:border-primary transition-all cursor-pointer">
                <div class="flex items-center gap-6">
                    <div class="w-16 h-16 rounded-lg overflow-hidden bg-surface-container">
                        <img class="w-full h-full object-cover"
                             src="https://lh3.googleusercontent.com/aida-public/AB6AXuCoSEeustsN2G5_AMQHAlFXYcHq7fuul6qJIYa9uZRF1KRM3MOnc-h-1glYYxy9n3JPN3MOnc"
                             alt="The Estate Collection Preview">
                    </div>
                    <div>
                        <h5 class="font-body-md font-bold text-on-surface">The Estate Collection Preview</h5>
                        <p class="text-label-sm text-outline">Friday · 06:00 PM</p>
                    </div>
                </div>
                <button class="text-primary font-label-sm uppercase tracking-widest flex items-center gap-2">
                    <span class="material-symbols-outlined">notifications</span>
                    Remind Me
                </button>
            </div>
        </div>
    </section>

</main>
@endsection
