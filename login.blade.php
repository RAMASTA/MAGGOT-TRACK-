@extends('layouts.guest')
@section('title', 'Masuk')
@section('content')
    <h2 class="text-xl font-bold">Masuk ke akun Anda</h2>
    <p class="mb-6 mt-1 text-sm text-slate-500">Kelola budidaya maggot BSF dengan lebih mudah.</p>
    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf
        <div>
            <label class="label" for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus class="input @error('email') border-red-500 @enderror">
            @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="label" for="password">Kata sandi</label>
            <input id="password" name="password" type="password" required class="input @error('password') border-red-500 @enderror">
            @error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="remember" value="1" class="rounded border-slate-300 text-forest focus:ring-lime-500"> Ingat saya</label>
        <button type="submit" class="btn-primary w-full">Masuk</button>
    </form>
    <p class="mt-6 text-center text-sm text-slate-500">Belum punya akun? <a href="{{ route('register') }}" class="font-semibold text-forest hover:underline dark:text-lime-400">Daftar</a></p>
@endsection
