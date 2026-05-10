@extends('layouts.app')

@section('title', 'Sesi Kelas')
@section('header', 'Sesi Kelas')
@section('subheader', 'Kelola sesi per kelas setiap minggu')

@section('content')

{{-- Week Navigator --}}
<div class="flex items-center justify-between mb-6">
    <a href="{{ route('class-sessions.index', ['date' => $weekStart->copy()->subWeek()->format('Y-m-d')]) }}"
       class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-gray-300 bg-white text-sm text-gray-600 hover:bg-gray-50 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Minggu Lalu
    </a>

    <div class="text-center">
        <p class="text-xs text-gray-400 uppercase tracking-wide font-semibold">Minggu</p>
        <p class="text-base font-bold text-gray-800">
            {{ $weekStart->translatedFormat('d M') }} – {{ $weekEnd->translatedFormat('d M Y') }}
        </p>
    </div>

    <a href="{{ route('class-sessions.index', ['date' => $weekStart->copy()->addWeek()->format('Y-m-d')]) }}"
       class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-gray-300 bg-white text-sm text-gray-600 hover:bg-gray-50 transition">
        Minggu Depan
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Session List --}}
    <div class="lg:col-span-2 space-y-4">
        @if($sessions->isEmpty())
            <div class="bg-white rounded-xl border border-gray-200 p-12 text-center text-gray-400 shadow-sm">
                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <p class="text-sm font-medium">Belum ada sesi minggu ini.</p>
                <p class="text-xs mt-1">Tambahkan sesi menggunakan form di sebelah kanan.</p>
            </div>
        @else
            @foreach($sessions as $classId => $classSessions)
            @php $firstSession = $classSessions->first() @endphp
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <p class="font-semibold text-gray-800 text-sm">{{ $firstSession->schoolClass->name }}</p>
                        <p class="text-xs text-gray-400">{{ $firstSession->schoolClass->grade->name }} · {{ $firstSession->schoolClass->courseType->name }}</p>
                    </div>
                    <span class="text-xs bg-green-100 text-green-700 font-medium px-2.5 py-0.5 rounded-full">
                        {{ $classSessions->count() }} sesi
                    </span>
                </div>
                <div class="divide-y divide-gray-50">
                    @foreach($classSessions as $session)
                    <div class="flex items-center justify-between px-4 py-3 hover:bg-gray-50">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-gray-700">
                                {{ \Carbon\Carbon::parse($session->date)->translatedFormat('l, d M Y') }}
                            </p>
                            @if($session->material)
                                <p class="text-xs text-gray-400 truncate">{{ $session->material }}</p>
                            @endif
                            @if($session->tutorPresences->isNotEmpty())
                                <div class="flex flex-wrap gap-1 mt-1">
                                    @foreach($session->tutorPresences as $tp)
                                    @php
                                        $cls = match($tp->status) {
                                            'presence'   => 'bg-green-100 text-green-700',
                                            'absent'     => 'bg-red-100 text-red-700',
                                            'sick'       => 'bg-yellow-100 text-yellow-700',
                                            'permission' => 'bg-blue-100 text-blue-700',
                                            default      => 'bg-gray-100 text-gray-500',
                                        };
                                    @endphp
                                    <span class="text-xs px-1.5 py-0.5 rounded font-medium {{ $cls }}">
                                        {{ $tp->tutor->name }}
                                    </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <form method="POST" action="{{ route('class-sessions.destroy', $session) }}" class="shrink-0 ml-3" id="del-session-{{ $session->id }}">
                            @csrf @method('DELETE')
                            <input type="hidden" name="week" value="{{ $weekStart->format('Y-m-d') }}">
                        </form>
                        <button type="button" x-data class="shrink-0 ml-3 p-1.5 rounded-lg text-gray-400 hover:bg-red-50 hover:text-red-500 transition"
                            @click="$store.deleteConfirm.show('Hapus sesi {{ addslashes($session->schoolClass->name ?? '') }} ini? Tindakan ini tidak dapat dibatalkan.', 'del-session-{{ $session->id }}')">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        @endif
    </div>

    {{-- Add Session Form --}}
    <div>
        <x-card title="Tambah Sesi">
            <form method="POST" action="{{ route('class-sessions.store') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="week" value="{{ $weekStart->format('Y-m-d') }}">

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Kelas</label>
                    <select name="class_id" required
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400 bg-white">
                        <option value="">Pilih kelas...</option>
                        @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>
                            {{ $class->name }} ({{ $class->grade->name }})
                        </option>
                        @endforeach
                    </select>
                    @error('class_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal Sesi</label>
                    <input type="date" name="date" required
                        value="{{ old('date', now()->format('Y-m-d')) }}"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400">
                    @error('date') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Materi (opsional)</label>
                    <input type="text" name="material" value="{{ old('material') }}"
                        placeholder="Materi yang diajarkan..."
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Jumlah Siswa</label>
                    <input type="number" name="number_of_pupils" value="{{ old('number_of_pupils', 0) }}"
                        min="0"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400">
                </div>

                <x-btn type="submit" class="w-full justify-center">Tambah Sesi</x-btn>
            </form>
        </x-card>
    </div>

</div>

@endsection
