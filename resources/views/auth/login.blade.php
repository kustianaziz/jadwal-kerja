@extends('layouts.guest')

@section('content')
    <form method="POST" action="{{ route('login') }}" class="space-y-6">
        @csrf

        <div>
            <label for="email" class="block text-sm font-medium text-dark-navy mb-2">Email Address</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-steel-gray" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="skeuo-input pl-10">
            </div>
            @error('email')
                <p class="mt-2 text-sm text-gauge-red">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-dark-navy mb-2">Password</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-steel-gray" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <input id="password" type="password" name="password" required autocomplete="current-password" class="skeuo-input pl-10">
            </div>
            @error('password')
                <p class="mt-2 text-sm text-gauge-red">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <input id="remember_me" type="checkbox" name="remember" class="w-4 h-4 text-cyan-glow border-steel-gray rounded shadow-inner focus:ring-cyan-glow bg-ice-blue">
                <label for="remember_me" class="ml-2 block text-sm text-dark-navy">Ingat Saya</label>
            </div>

            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-sm text-cyan-glow hover:text-tech-blue font-medium">Lupa password?</a>
            @endif
        </div>

        <div>
            <button type="submit" class="skeuo-btn w-full text-lg py-3">
                Login
            </button>
        </div>
        
        <div class="text-center mt-4">
            <p class="text-sm text-dark-navy">Belum punya akun? <a href="{{ route('register') }}" class="text-cyan-glow hover:text-tech-blue font-medium">Daftar sekarang</a></p>
        </div>
    </form>
@endsection
