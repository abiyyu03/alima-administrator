@extends('layouts.app')

@section('title', 'Pengaturan')
@section('header', 'Pengaturan')
@section('subheader', 'Preferensi tampilan dan akun')

@section('content')

    <div class="max-w-2xl space-y-8">

        {{-- Tampilan --}}
        <x-card title="Tema Tampilan">
            <form method="POST" action="{{ route('settings.update') }}" x-data="{ theme: '{{ $user->theme ?? 'light' }}' }">
                @csrf @method('PUT')
                <input type="hidden" name="section" value="theme">

                <div class="grid grid-cols-2 gap-3 mb-5">
                    {{-- Light --}}
                    <label class="relative flex flex-col gap-3 p-4 rounded-xl border-2 cursor-pointer transition"
                        :class="theme === 'light' ? 'border-green-500 bg-green-50' : 'border-gray-200 hover:border-gray-300'">
                        <input type="radio" name="theme" value="light" x-model="theme" class="hidden">
                        <div class="w-full h-16 rounded-lg border border-gray-200 overflow-hidden flex"
                            style="background:#f8fafc">
                            <div class="w-10 h-full" style="background:#f1f5f9"></div>
                            <div class="flex-1 p-2 space-y-1">
                                <div class="h-1.5 rounded-full w-3/4" style="background:#e2e8f0"></div>
                                <div class="h-1.5 rounded-full w-1/2" style="background:#e2e8f0"></div>
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-gray-800">Light</p>
                                <p class="text-xs text-gray-400">Terang & bersih</p>
                            </div>
                            <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center"
                                :class="theme === 'light' ? 'border-green-500' : 'border-gray-300'">
                                <div class="w-2.5 h-2.5 rounded-full bg-green-500"
                                    :class="theme === 'light' ? 'opacity-100' : 'opacity-0'"></div>
                            </div>
                        </div>
                    </label>

                    {{-- Dark --}}
                    <label class="relative flex flex-col gap-3 p-4 rounded-xl border-2 cursor-pointer transition"
                        :class="theme === 'dark' ? 'border-green-500 bg-green-50' : 'border-gray-200 hover:border-gray-300'">
                        <input type="radio" name="theme" value="dark" x-model="theme" class="hidden">
                        <div class="w-full h-16 rounded-lg overflow-hidden flex" style="background:#0f172a">
                            <div class="w-10 h-full" style="background:#020617"></div>
                            <div class="flex-1 p-2 space-y-1">
                                <div class="h-1.5 rounded-full w-3/4" style="background:#1e293b"></div>
                                <div class="h-1.5 rounded-full w-1/2" style="background:#1e293b"></div>
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-gray-800">Dark</p>
                                <p class="text-xs text-gray-400">Gelap & nyaman</p>
                            </div>
                            <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center"
                                :class="theme === 'dark' ? 'border-green-500' : 'border-gray-300'">
                                <div class="w-2.5 h-2.5 rounded-full bg-green-500"
                                    :class="theme === 'dark' ? 'opacity-100' : 'opacity-0'"></div>
                            </div>
                        </div>
                    </label>
                </div>

                <button type="submit" class="px-5 py-2 rounded-lg text-sm font-semibold text-white transition"
                    style="background:#15803d; hover:background:#166534">
                    Simpan Tema
                </button>
            </form>
        </x-card>

        {{-- Info Akun --}}
        <x-card title="Info Akun">
            <div class="divide-y divide-gray-100">
                <div class="flex justify-between items-center py-3">
                    <span class="text-sm text-gray-500">Nama</span>
                    <span class="text-sm font-medium text-gray-800">{{ $user->name }}</span>
                </div>
                <div class="flex justify-between items-center py-3">
                    <span class="text-sm text-gray-500">Email</span>
                    <span class="text-sm font-medium text-gray-800">{{ $user->email }}</span>
                </div>
                <div class="flex justify-between items-center py-3">
                    <span class="text-sm text-gray-500">Role</span>
                    @php
                        $roleColor = match ($user->role?->name) {
                            'superadmin' => 'bg-purple-100 text-purple-700',
                            'tutor' => 'bg-blue-100 text-blue-700',
                            default => 'bg-gray-100 text-gray-600',
                        };
                    @endphp
                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $roleColor }}">
                        {{ ucfirst($user->role?->name ?? '-') }}
                    </span>
                </div>
                @if ($user->tutor)
                    <div class="flex justify-between items-center py-3">
                        <span class="text-sm text-gray-500">Linked Tutor</span>
                        <span class="text-sm font-medium text-gray-800">{{ $user->tutor->name }}</span>
                    </div>
                @endif
                <div class="flex justify-between items-center py-3">
                    <span class="text-sm text-gray-500">Bergabung</span>
                    <span class="text-sm text-gray-600">{{ $user->created_at->translatedFormat('d F Y') }}</span>
                </div>
            </div>
        </x-card>

        {{-- Ganti Password --}}
        <x-card title="Ganti Password">
            <form method="POST" action="{{ route('settings.password') }}">
                @csrf @method('PUT')
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Password Baru</label>
                        <input type="password" name="password" autocomplete="new-password" placeholder="Minimal 8 karakter"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400">
                        @error('password')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password</label>
                        <input type="password" name="password_confirmation" autocomplete="new-password"
                            placeholder="Ulangi password baru"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400">
                    </div>
                    <button type="submit" class="px-5 py-2 rounded-lg text-sm font-semibold text-white transition"
                        style="background:#15803d">
                        Ganti Password
                    </button>
                </div>
            </form>
        </x-card>

    </div>

@endsection
