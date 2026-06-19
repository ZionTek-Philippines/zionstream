@extends('layouts.app')

@section('title', 'Settings')

@section('content')
<div class="pt-16 pb-24 min-h-screen bg-surface">

    {{-- Page Header --}}
    <div class="px-container-padding py-6 border-b border-outline-variant/20">
        <h1 class="text-headline-md font-medium text-on-surface tracking-tight">Settings</h1>
    </div>

    {{-- Profile Card --}}
    <div class="px-container-padding py-6">
        <div class="glass-panel rounded-2xl p-5 flex items-center gap-4 gold-shadow">
            <div class="w-16 h-16 rounded-full overflow-hidden border-2 border-primary/30 flex-shrink-0">
                <img
                    src="{{ auth()->user()->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode(auth()->user()->name).'&background=785a00&color=fff&bold=true&size=128' }}"
                    alt="{{ auth()->user()->name }}"
                    class="w-full h-full object-cover">
            </div>
            <div class="flex-1 min-w-0">
                <h2 class="text-headline-md font-semibold text-on-surface truncate">{{ auth()->user()->name }}</h2>
                <p class="text-on-surface-variant text-sm truncate">{{ auth()->user()->email }}</p>
                <div class="mt-2">
                    @foreach(auth()->user()->getRoleNames() as $role)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-widest
                            {{ match($role) {
                                'admin'     => 'bg-error/10 text-error',
                                'streamer'  => 'bg-primary/10 text-primary',
                                'moderator' => 'bg-secondary-container text-on-secondary-container',
                                default     => 'bg-surface-variant text-on-surface-variant',
                            } }}">
                            {{ $role }}
                        </span>
                    @endforeach
                </div>
            </div>
            <a href="#" class="text-primary hover:opacity-70 transition-opacity flex-shrink-0">
                <span class="material-symbols-outlined">edit</span>
            </a>
        </div>
    </div>

    {{-- Settings Sections --}}
    <div class="px-container-padding space-y-6 pb-6">

        {{-- Account --}}
        <div>
            <p class="text-[10px] text-outline uppercase tracking-[0.15em] font-semibold mb-3 px-1">Account</p>
            <div class="bg-white rounded-2xl overflow-hidden gold-shadow divide-y divide-outline-variant/20">

                <a href="#" class="flex items-center gap-4 px-5 py-4 hover:bg-surface-container-low transition-colors group">
                    <span class="w-9 h-9 rounded-full bg-primary/8 flex items-center justify-center text-primary flex-shrink-0">
                        <span class="material-symbols-outlined text-[20px]">person</span>
                    </span>
                    <span class="flex-1 text-on-surface text-[15px]">Edit Profile</span>
                    <span class="material-symbols-outlined text-outline/40 group-hover:text-primary transition-colors text-[18px]">chevron_right</span>
                </a>

                <a href="#" class="flex items-center gap-4 px-5 py-4 hover:bg-surface-container-low transition-colors group">
                    <span class="w-9 h-9 rounded-full bg-primary/8 flex items-center justify-center text-primary flex-shrink-0">
                        <span class="material-symbols-outlined text-[20px]">lock</span>
                    </span>
                    <span class="flex-1 text-on-surface text-[15px]">Change Password</span>
                    <span class="material-symbols-outlined text-outline/40 group-hover:text-primary transition-colors text-[18px]">chevron_right</span>
                </a>

                <a href="#" class="flex items-center gap-4 px-5 py-4 hover:bg-surface-container-low transition-colors group">
                    <span class="w-9 h-9 rounded-full bg-primary/8 flex items-center justify-center text-primary flex-shrink-0">
                        <span class="material-symbols-outlined text-[20px]">notifications</span>
                    </span>
                    <span class="flex-1 text-on-surface text-[15px]">Notifications</span>
                    <span class="material-symbols-outlined text-outline/40 group-hover:text-primary transition-colors text-[18px]">chevron_right</span>
                </a>

            </div>
        </div>

        {{-- Subscription (customers + streamers) --}}
        @unless(auth()->user()->hasRole('admin'))
        <div>
            <p class="text-[10px] text-outline uppercase tracking-[0.15em] font-semibold mb-3 px-1">Subscription</p>
            <div class="bg-white rounded-2xl overflow-hidden gold-shadow divide-y divide-outline-variant/20">

                <a href="#" class="flex items-center gap-4 px-5 py-4 hover:bg-surface-container-low transition-colors group">
                    <span class="w-9 h-9 rounded-full bg-primary/8 flex items-center justify-center text-primary flex-shrink-0">
                        <span class="material-symbols-outlined text-[20px]">workspace_premium</span>
                    </span>
                    <div class="flex-1">
                        <p class="text-on-surface text-[15px]">My Plan</p>
                        @if(auth()->user()->activeSubscription)
                            <p class="text-primary text-[11px] font-semibold uppercase tracking-wide">
                                {{ auth()->user()->activeSubscription->plan->name ?? 'Active' }}
                            </p>
                        @else
                            <p class="text-outline text-[11px]">No active plan</p>
                        @endif
                    </div>
                    <span class="material-symbols-outlined text-outline/40 group-hover:text-primary transition-colors text-[18px]">chevron_right</span>
                </a>

            </div>
        </div>
        @endunless

        {{-- Support --}}
        <div>
            <p class="text-[10px] text-outline uppercase tracking-[0.15em] font-semibold mb-3 px-1">Support</p>
            <div class="bg-white rounded-2xl overflow-hidden gold-shadow divide-y divide-outline-variant/20">

                <a href="#" class="flex items-center gap-4 px-5 py-4 hover:bg-surface-container-low transition-colors group">
                    <span class="w-9 h-9 rounded-full bg-surface-variant flex items-center justify-center text-on-surface-variant flex-shrink-0">
                        <span class="material-symbols-outlined text-[20px]">help</span>
                    </span>
                    <span class="flex-1 text-on-surface text-[15px]">Help & FAQ</span>
                    <span class="material-symbols-outlined text-outline/40 group-hover:text-primary transition-colors text-[18px]">chevron_right</span>
                </a>

                <a href="#" class="flex items-center gap-4 px-5 py-4 hover:bg-surface-container-low transition-colors group">
                    <span class="w-9 h-9 rounded-full bg-surface-variant flex items-center justify-center text-on-surface-variant flex-shrink-0">
                        <span class="material-symbols-outlined text-[20px]">shield</span>
                    </span>
                    <span class="flex-1 text-on-surface text-[15px]">Privacy Policy</span>
                    <span class="material-symbols-outlined text-outline/40 group-hover:text-primary transition-colors text-[18px]">chevron_right</span>
                </a>

                <a href="#" class="flex items-center gap-4 px-5 py-4 hover:bg-surface-container-low transition-colors group">
                    <span class="w-9 h-9 rounded-full bg-surface-variant flex items-center justify-center text-on-surface-variant flex-shrink-0">
                        <span class="material-symbols-outlined text-[20px]">article</span>
                    </span>
                    <span class="flex-1 text-on-surface text-[15px]">Terms of Service</span>
                    <span class="material-symbols-outlined text-outline/40 group-hover:text-primary transition-colors text-[18px]">chevron_right</span>
                </a>

            </div>
        </div>

        {{-- App Version --}}
        <p class="text-center text-[10px] text-outline/40 uppercase tracking-widest pt-2">
            Maddox · v1.0.0
        </p>

    </div>

    {{-- Logout --}}
    <div class="px-container-padding pb-4">
        <form method="POST" action="{{ route('app.auth.logout') }}">
            @csrf
            <button type="submit"
                    class="w-full flex items-center justify-center gap-3 py-4 rounded-2xl border border-error/30 text-error hover:bg-error/5 transition-all duration-200 font-label-sm uppercase tracking-[0.15em] active:scale-[0.98]">
                <span class="material-symbols-outlined text-[20px]">logout</span>
                Sign Out
            </button>
        </form>
    </div>

</div>
@endsection
