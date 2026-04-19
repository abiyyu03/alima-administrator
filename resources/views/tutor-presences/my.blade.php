@extends('layouts.app')

@section('title', 'Presensi Saya')
@section('header', 'Presensi Saya')
@section('subheader', 'Catat dan pantau kehadiranmu per sesi kelas')

@section('content')

{{-- Week Navigator --}}
<div class="flex items-center justify-between mb-6">
    <a href="{{ route('my-presences', ['date' => $weekStart->copy()->subWeek()->format('Y-m-d')]) }}"
       class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-gray-300 bg-white text-sm text-gray-600 hover:bg-gray-50 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        <span class="hidden sm:inline">Minggu Lalu</span>
    </a>
    <div class="text-center">
        <p class="text-xs text-gray-400 uppercase tracking-wide font-semibold">Minggu Ini</p>
        <p class="text-sm sm:text-base font-bold text-gray-800">
            {{ $weekStart->translatedFormat('d M') }} – {{ $weekEnd->translatedFormat('d M Y') }}
        </p>
    </div>
    <a href="{{ route('my-presences', ['date' => $weekStart->copy()->addWeek()->format('Y-m-d')]) }}"
       class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-gray-300 bg-white text-sm text-gray-600 hover:bg-gray-50 transition">
        <span class="hidden sm:inline">Minggu Depan</span>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
    </a>
</div>

{{-- Stats Bar --}}
<div class="grid grid-cols-3 gap-3 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-3 sm:p-4 text-center shadow-sm">
        <p class="text-xl sm:text-2xl font-bold text-gray-800">{{ $statsWeek['total'] }}</p>
        <p class="text-xs text-gray-500 mt-0.5">Sesi</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-3 sm:p-4 text-center shadow-sm">
        <p class="text-xl sm:text-2xl font-bold text-green-600">{{ $statsWeek['hadir'] }}</p>
        <p class="text-xs text-gray-500 mt-0.5">Hadir</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-3 sm:p-4 text-center shadow-sm">
        <p class="text-base sm:text-xl font-bold text-emerald-700">
            Rp {{ number_format($statsWeek['earned'], 0, ',', '.') }}
        </p>
        <p class="text-xs text-gray-500 mt-0.5">Pendapatan</p>
    </div>
</div>

{{-- Class Cards --}}
{{-- Mobile: 1 column stacked | md+: horizontal scroll row --}}
<div class="flex flex-col gap-4 mb-8 md:flex-row md:overflow-x-auto md:pb-2">
    @forelse($classes as $class)
    @php $classSessions = $sessions->get($class->id, collect()); @endphp
    <div x-data="presenceCard({{ $class->id }})"
         class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden w-full md:w-[380px] md:flex-shrink-0">

        {{-- Card Header --}}
        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between gap-3">
            <div class="min-w-0">
                <p class="font-semibold text-gray-800 text-sm leading-tight truncate">{{ $class->name }}</p>
                <div class="flex items-center gap-1.5 mt-1">
                    <span class="text-xs text-gray-400">{{ $class->grade->name }}</span>
                    <span class="w-1 h-1 rounded-full bg-gray-300 shrink-0"></span>
                    <span class="text-xs px-2 py-0.5 rounded-full font-medium shrink-0
                        {{ $class->courseType->name === 'Regular' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' }}">
                        {{ $class->courseType->name }}
                    </span>
                </div>
            </div>
            <div class="text-right shrink-0">
                <p class="text-xs text-gray-400">Rate</p>
                <p class="text-sm font-bold text-green-700">Rp {{ number_format($class->pivot->amount, 0, ',', '.') }}</p>
            </div>
        </div>

        {{-- Sessions This Week --}}
        <div class="px-4 py-3 space-y-3">
            @if($classSessions->isEmpty())
                <p class="text-xs text-gray-400 text-center py-3">Belum ada sesi minggu ini</p>
            @else
                @foreach($classSessions as $session)
                @php $sp = $session->tutorPresences->first() @endphp
                <div class="rounded-xl bg-gray-50 border border-gray-100 overflow-hidden">

                    {{-- Tanggal & Materi --}}
                    <div class="px-3 pt-3 pb-2">
                        <p class="text-sm font-semibold text-gray-700">
                            {{ \Carbon\Carbon::parse($session->date)->translatedFormat('l, d M Y') }}
                        </p>
                        @if($session->material)
                            <p class="text-xs text-gray-400 mt-0.5">{{ $session->material }}</p>
                        @endif
                    </div>

                    {{-- Status Segmented Control --}}
                    @if($sp)
                    <form method="POST" action="{{ route('my-presences.update', $sp) }}"
                          class="px-3 pb-3" x-data>
                        @csrf @method('PUT')
                        <input type="hidden" name="week" value="{{ $weekStart->format('Y-m-d') }}">
                        <select name="status" @change="$el.form.submit()"
                            class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-400 bg-white">
                            <option value="presence" {{ $sp->status === 'presence'   ? 'selected' : '' }}>Hadir</option>
                            <option value="sick"     {{ $sp->status === 'sick'       ? 'selected' : '' }}>Sakit</option>
                            <option value="permission" {{ $sp->status === 'permission' ? 'selected' : '' }}>Izin</option>
                            <option value="absent"   {{ $sp->status === 'absent'     ? 'selected' : '' }}>Alpha</option>
                        </select>
                    </form>
                    @else
                        <div class="px-3 pb-3">
                            <span class="text-xs text-gray-400 italic">Belum ada presensi</span>
                        </div>
                    @endif
                </div>
                @endforeach
            @endif
        </div>

        {{-- Add Session Button --}}
        <div class="px-4 pb-4">
            <button @click="open = true"
                class="w-full flex items-center justify-center gap-2 py-2.5 rounded-xl border-2 border-dashed border-gray-300 text-xs font-medium text-gray-400 hover:border-green-400 hover:text-green-600 transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Sesi
            </button>
        </div>

        {{-- Add Session Modal --}}
        <div x-show="open" x-cloak
             class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-black/50"
             @click.self="open = false">
            <div class="bg-white rounded-t-2xl sm:rounded-2xl shadow-xl w-full sm:max-w-sm p-6" @click.stop>
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h3 class="font-bold text-gray-800">Tambah Sesi</h3>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $class->name }}</p>
                    </div>
                    <button @click="open = false" class="text-gray-400 hover:text-gray-600 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('my-presences.store') }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="class_id" value="{{ $class->id }}">
                    <input type="hidden" name="week" value="{{ $weekStart->format('Y-m-d') }}">

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal Sesi</label>
                        <input type="date" name="date" required
                            value="{{ now()->format('Y-m-d') }}"
                            min="{{ $weekStart->format('Y-m-d') }}"
                            max="{{ $weekEnd->format('Y-m-d') }}"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">
                            Materi <span class="text-gray-400 font-normal">(opsional)</span>
                        </label>
                        <input type="text" name="material" placeholder="Materi yang diajarkan..."
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Status Kehadiran</label>
                        <select name="status"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400 bg-white">
                            <option value="presence">Hadir</option>
                            <option value="sick">Sakit</option>
                            <option value="permission">Izin</option>
                            <option value="absent">Alpha</option>
                        </select>
                    </div>

                    <button type="submit"
                        class="w-full py-2.5 rounded-xl bg-green-600 hover:bg-green-700 text-white text-sm font-semibold transition">
                        Simpan Presensi
                    </button>
                </form>
            </div>
        </div>

    </div>
    @empty
        <div class="bg-white rounded-xl border border-gray-200 p-12 text-center text-gray-400 shadow-sm">
            <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
            <p class="text-sm font-medium">Kamu belum diampu di kelas manapun.</p>
            <p class="text-xs mt-1">Hubungi admin untuk mendapatkan assignment kelas.</p>
        </div>
    @endforelse
</div>

{{-- History: 4 Minggu Terakhir --}}
@if($history->isNotEmpty())
<div class="bg-white rounded-xl border border-gray-200 shadow-sm">
    <div class="px-5 py-4 border-b border-gray-100">
        <h2 class="font-bold text-gray-800">Riwayat 4 Minggu Terakhir</h2>
    </div>
    <div class="divide-y divide-gray-100">
        @foreach($history as $weekKey => $presences)
        @php
            $ws = \Carbon\Carbon::parse($weekKey);
            $we = $ws->copy()->endOfWeek(\Carbon\Carbon::SUNDAY);
            $earned = $presences->sum(fn($p) => $p->earned);
            $hadir  = $presences->where('status', 'presence')->count();
        @endphp
        <details class="group">
            <summary class="flex items-center justify-between px-5 py-3.5 cursor-pointer hover:bg-gray-50 select-none list-none">
                <div class="flex items-center gap-2 sm:gap-3 min-w-0">
                    <div class="w-2 h-2 rounded-full shrink-0 {{ $ws->isCurrentWeek() ? 'bg-green-500' : 'bg-gray-300' }}"></div>
                    <span class="text-sm font-medium text-gray-700 truncate">
                        {{ $ws->translatedFormat('d M') }} – {{ $we->translatedFormat('d M Y') }}
                    </span>
                    <span class="text-xs text-gray-400 shrink-0">{{ $presences->count() }} sesi · {{ $hadir }} hadir</span>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <span class="text-sm font-semibold text-green-700">Rp {{ number_format($earned, 0, ',', '.') }}</span>
                    <svg class="w-4 h-4 text-gray-400 group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>
            </summary>
            <div class="px-5 pb-4 space-y-2">
                @foreach($presences as $p)
                @php
                    $statusMap = [
                        'presence'   => ['Hadir', 'bg-green-100 text-green-700'],
                        'absent'     => ['Alpha', 'bg-red-100 text-red-700'],
                        'sick'       => ['Sakit', 'bg-amber-100 text-amber-700'],
                        'permission' => ['Izin',  'bg-blue-100 text-blue-700'],
                    ];
                    [$slabel, $scls] = $statusMap[$p->status] ?? ['?', 'bg-gray-100 text-gray-500'];
                @endphp
                <div class="flex items-center justify-between py-2 border-t border-gray-50 first:border-0">
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-gray-700">{{ $p->classSession->schoolClass->name ?? '-' }}</p>
                        <p class="text-xs text-gray-400">
                            {{ \Carbon\Carbon::parse($p->classSession->date)->translatedFormat('l, d M Y') }}
                            @if($p->classSession->material)
                                · {{ $p->classSession->material }}
                            @endif
                        </p>
                    </div>
                    <div class="flex items-center gap-2 shrink-0 ml-3">
                        <span class="text-xs px-2.5 py-1 rounded-full font-semibold {{ $scls }}">{{ $slabel }}</span>
                        @if($p->earned > 0)
                            <span class="text-xs text-green-700 font-semibold hidden sm:inline">
                                +Rp {{ number_format($p->earned, 0, ',', '.') }}
                            </span>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </details>
        @endforeach
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
function presenceCard(classId) {
    return { open: false, classId };
}
</script>
@endpush
