@extends('layouts.app')
@section('title', 'Profil & Pengaturan')
@section('content')
<div class="mx-auto max-w-2xl space-y-6">
    <h1 class="text-2xl font-bold">Profil & Pengaturan Akun</h1>

    <form method="POST" action="{{ route('profile.update') }}" class="card space-y-4 p-6">
        @csrf @method('PUT')
        <h2 class="font-semibold">Data profil</h2>
        @foreach ([['name', 'Nama', 'text'], ['email', 'Email', 'email']] as [$n, $l, $t])
            <div><label class="label" for="p-{{ $n }}">{{ $l }}</label>
                <input id="p-{{ $n }}" type="{{ $t }}" name="{{ $n }}" value="{{ old($n, $user->{$n}) }}" required class="input @error($n) border-red-500 @enderror">
                @error($n)<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
        @endforeach
        <p class="text-xs text-slate-400">Peran: {{ ucfirst($user->role) }} · Bergabung {{ $user->created_at->translatedFormat('d M Y') }}</p>
        <div class="flex justify-end"><button type="submit" class="btn-primary">Simpan profil</button></div>
    </form>

    <form method="POST" action="{{ route('profile.password') }}" class="card space-y-4 p-6">
        @csrf @method('PUT')
        <h2 class="font-semibold">Ganti kata sandi</h2>
        @foreach ([['current_password', 'Kata sandi saat ini'], ['password', 'Kata sandi baru (min. 8 karakter)'], ['password_confirmation', 'Ulangi kata sandi baru']] as [$n, $l])
            <div><label class="label" for="p-{{ $n }}">{{ $l }}</label>
                <input id="p-{{ $n }}" type="password" name="{{ $n }}" required autocomplete="{{ $n === 'current_password' ? 'current-password' : 'new-password' }}" class="input @error($n) border-red-500 @enderror">
                @error($n)<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
        @endforeach
        <div class="flex justify-end"><button type="submit" class="btn-primary">Ganti kata sandi</button></div>
    </form>

    <div class="card flex items-center justify-between gap-4 p-6">
        <div><h2 class="font-semibold">Tampilan</h2><p class="text-sm text-slate-500">Pilihan mode gelap disimpan di browser ini.</p></div>
        <button type="button" class="btn-ghost" onclick="document.getElementById('theme-btn').click()"><i data-lucide="moon" class="h-4 w-4"></i> Ganti tema</button>
    </div>
</div>
@endsection
