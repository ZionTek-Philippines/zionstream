<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Sign In — Maddox</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .input-line {
            border: none;
            border-bottom: 1px solid rgba(127, 118, 101, 0.3);
            background: transparent !important;
            transition: border-color 0.3s ease;
        }
        .input-line:focus {
            outline: none;
            border-bottom: 2px solid #785a00;
            box-shadow: none;
        }
        .input-line.is-invalid {
            border-bottom-color: #ba1a1a;
        }
    </style>
</head>
<body class="min-h-screen bg-surface font-body-md text-on-surface overflow-x-hidden selection:bg-primary/20">

{{-- Floating Particles --}}
<div class="fixed inset-0 -z-10 pointer-events-none overflow-hidden" id="particles-bg"></div>

{{-- Right Decorative Panel (desktop only) --}}
<div class="fixed top-0 right-0 h-full w-1/3 hidden lg:block overflow-hidden pointer-events-none">
    <div class="w-full h-full relative">
        <img alt="Luxury Jewelry"
             class="w-full h-full object-cover brightness-[0.85] contrast-[1.1]"
             src="https://lh3.googleusercontent.com/aida-public/AB6AXuBme1uIm0DQ1KL2FJkOAUmzuZBEFv2lYJsRQC5dpskJiN1U6ce7HKPR1EnhmONGVn8jcN_8Hhaf1mWB8kqAWqt0vdnMnXT2dFuTRVZ3tYphw_x4oB1Dd5EJ1akXuEpXuZr-UtXa1TyFg-ehQVh54mHHGmrJAjEjB57HJe7GouYaySKtjOKGaMfvcHT_diyspZCAV6Wm9DI1Owaap1FfRkUGCslDnaYZmxagZL70iQnmLfPY61vEbSSrBEso6Ng-wDkSCaXcV3Jn7g">
        <div class="absolute inset-0 bg-gradient-to-l from-transparent via-surface/20 to-surface"></div>
    </div>
</div>

{{-- Main Content --}}
<main class="flex items-center justify-center min-h-screen px-container-padding lg:w-2/3">
    <div class="w-full max-w-[440px] flex flex-col gap-12 py-16">

        {{-- Brand --}}
        <header class="flex flex-col items-center text-center">
            <h1 class="font-display-lg text-headline-lg-mobile md:text-headline-lg text-primary tracking-[0.2em] uppercase mb-4">
                MADDOX
            </h1>
            <div class="w-8 h-[1px] bg-primary-container mb-12"></div>
            <h2 class="text-[28px] font-light tracking-tight text-on-surface mb-2">
                Welcome Back
            </h2>
            <p class="text-on-surface-variant/80 text-[15px] max-w-[280px] text-center leading-relaxed">
                Sign in to continue your experience.
            </p>

        </header>

        {{-- Session Error --}}
        @if(session('error'))
            <div class="bg-error-container/60 text-error px-4 py-3 rounded-lg text-sm text-center border border-error/20">
                {{ session('error') }}
            </div>
        @endif

        {{-- Form --}}
        <section class="flex flex-col gap-8">
            <form method="POST" action="{{ route('app.auth.post') }}" class="flex flex-col gap-10">
                @csrf

                <div class="space-y-8">

                    {{-- Email --}}
                    <div class="flex flex-col">
                        <label class="text-label-sm text-outline uppercase tracking-widest mb-2 px-1" for="email">
                            Email Address
                        </label>
                        <input class="input-line px-1 py-3 text-on-surface text-[16px] {{ $errors->has('email') ? 'is-invalid' : '' }}"
                               id="email" name="email" type="email"
                               placeholder="alexander@prestige.com"
                               value="{{ old('email') }}" required autofocus>
                        @error('email')
                            <p class="text-error text-[11px] mt-2 px-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div class="flex flex-col">
                        <div class="flex justify-between items-end mb-2 px-1">
                            <label class="text-label-sm text-outline uppercase tracking-widest" for="password">
                                Password
                            </label>
                            <a class="text-[10px] text-outline hover:text-primary transition-colors uppercase tracking-widest" href="#">
                                Forgot?
                            </a>
                        </div>
                        <div class="relative">
                            <input class="input-line w-full px-1 py-3 text-on-surface text-[16px] pr-8 {{ $errors->has('password') ? 'is-invalid' : '' }}"
                                   id="password" name="password" type="password"
                                   placeholder="••••••••••••" required>
                            <button class="absolute right-0 bottom-3 text-outline hover:text-primary transition-colors"
                                    type="button" id="toggle-password">
                                <span class="material-symbols-outlined text-[20px]" id="eye-icon">visibility</span>
                            </button>
                        </div>
                        @error('password')
                            <p class="text-error text-[11px] mt-2 px-1">{{ $message }}</p>
                        @enderror
                    </div>

                </div>

                <button class="w-full bg-primary text-on-primary py-5 rounded-lg text-label-sm uppercase tracking-[0.2em] gold-shadow hover:bg-primary-container transition-all duration-300 active:scale-[0.98]"
                        type="submit">
                    Sign In
                </button>
            </form>

            {{-- Divider --}}
            <div class="flex items-center gap-4">
                <div class="h-[0.5px] flex-1 bg-outline-variant/30"></div>
                <span class="text-[10px] text-outline/50 uppercase tracking-widest">Or authenticate via</span>
                <div class="h-[0.5px] flex-1 bg-outline-variant/30"></div>
            </div>

            {{-- Facebook --}}
            <a href="{{ route('app.auth.facebook') }}"
            class="flex items-center justify-center gap-3 py-4 rounded-lg border border-[#1877F2]/30 bg-white hover:bg-[#1877F2]/5 hover:border-[#1877F2]/60 transition-all duration-200 group">
                <svg class="w-5 h-5 flex-shrink-0" viewBox="0 0 24 24" fill="#1877F2">
                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                </svg>
                <span class="text-[12px] text-[#1877F2] uppercase tracking-[0.15em] font-medium group-hover:font-semibold transition-all">
                    Continue with Facebook
                </span>
            </a>

        </section>

        {{-- Footer --}}
        <footer class="flex flex-col items-center gap-6">
            <p class="text-on-surface-variant text-sm">
                Don't have an account?
                <a class="text-primary font-semibold hover:underline decoration-primary/30 underline-offset-4 ml-1" href="#">
                    Register Now
                </a>
            </p>
            <div class="flex items-center gap-4 opacity-40">
                <a class="text-[10px] uppercase tracking-widest hover:opacity-100 transition-opacity" href="#">Privacy</a>
                <span class="text-[8px] text-outline">•</span>
                <a class="text-[10px] uppercase tracking-widest hover:opacity-100 transition-opacity" href="#">Terms</a>
                <span class="text-[8px] text-outline">•</span>
                <a class="text-[10px] uppercase tracking-widest hover:opacity-100 transition-opacity" href="#">Support</a>
            </div>
        </footer>


    </div>
</main>

<script>
    // Password toggle
    document.getElementById('toggle-password').addEventListener('click', function () {
        const input = document.getElementById('password');
        const icon  = document.getElementById('eye-icon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.textContent = 'visibility_off';
        } else {
            input.type = 'password';
            icon.textContent = 'visibility';
        }
    });

    // Floating gold particles
    const bg = document.getElementById('particles-bg');
    const createParticle = () => {
        const p = document.createElement('div');
        const size = Math.random() * 4 + 2;
        Object.assign(p.style, {
            position: 'absolute',
            width: `${size}px`, height: `${size}px`,
            borderRadius: '50%',
            background: 'rgba(120, 90, 0, 0.15)',
            left: `${Math.random() * 100}%`,
            top: `${Math.random() * 100}%`,
            opacity: 0,
        });
        bg.appendChild(p);
        p.animate([
            { opacity: 0, transform: 'translateY(0) scale(1)' },
            { opacity: 1, transform: 'translateY(-80px) scale(1.5)' },
            { opacity: 0, transform: 'translateY(-160px) scale(0.5)' },
        ], { duration: Math.random() * 3000 + 5000, easing: 'ease-out' }).onfinish = () => p.remove();
    };
    setInterval(createParticle, 800);

    // Subtle parallax on main card
    document.addEventListener('mousemove', (e) => {
        const main = document.querySelector('main > div');
        const mx = (e.clientX - window.innerWidth / 2) / 60;
        const my = (e.clientY - window.innerHeight / 2) / 60;
        main.style.transform = `translate(${mx}px, ${my}px)`;
    });
</script>

</body>
</html>
