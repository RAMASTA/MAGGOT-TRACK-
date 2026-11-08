@extends('layouts.guest')
@section('title', 'Daftar')
@section('content')
    <h2 class="text-xl font-bold">Buat akun baru</h2>
    <p class="mb-6 mt-1 text-sm text-slate-500">Mulai catat budidaya maggot Anda hari ini.</p>
    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf
        @foreach ([['name', 'Nama lengkap', 'text'], ['email', 'Email', 'email'], ['password', 'Kata sandi (min. 8 karakter)', 'password'], ['password_confirmation', 'Ulangi kata sandi', 'password']] as [$n, $l, $t])
            <div>
                <label class="label" for="{{ $n }}">{{ $l }}</label>
                <input id="{{ $n }}" name="{{ $n }}" type="{{ $t }}" @if (in_array($t, ['text', 'email'])) value="{{ old($n) }}" @endif required class="input @error($n) border-red-500 @enderror">
                @error($n)<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
        @endforeach
        <button type="submit" class="btn-primary w-full">Daftar</button>
    </form>
    <p class="mt-6 text-center text-sm text-slate-500">Sudah punya akun? <a href="{{ route('login') }}" class="font-semibold text-forest hover:underline dark:text-lime-400">Masuk</a></p>
@endsection
