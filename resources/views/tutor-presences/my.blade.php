@extends('layouts.app')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <style>
        .select2-container--default .select2-selection--multiple {
            border-radius: 0.5rem;
            border-color: #d1d5db;
            min-height: 38px;
            padding: 2px 6px;
        }
        .select2-container--default.select2-container--focus .select2-selection--multiple {
            border-color: #4ade80;
            outline: none;
            box-shadow: 0 0 0 2px rgb(74 222 128 / 0.4);
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #dcfce7;
            border-color: #86efac;
            color: #166534;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            padding: 1px 6px;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            color: #16a34a;
        }
        .select2-dropdown { border-color: #d1d5db; border-radius: 0.5rem; font-size: 0.875rem; }
        .select2-search--dropdown .select2-search__field { border-radius: 0.375rem; border-color: #d1d5db; }
    </style>
@endpush

@section('title', 'Presensi Saya')
@section('header', 'Presensi Saya')
@section('subheader', 'Catat dan pantau kehadiranmu per sesi kelas')

@section('content')

    {{-- Week Navigator --}}
    <div class="flex items-center justify-between mb-6">
        <a href="{{ route('my-presences', ['date' => $weekStart->copy()->subWeek()->format('Y-m-d')]) }}"
            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-gray-300 bg-white text-sm text-gray-600 hover:bg-gray-50 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
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
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
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
                class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden w-full md:w-[400px] md:flex-shrink-0">

                {{-- Card Header --}}
                <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <p class="font-semibold text-gray-800 text-sm leading-tight truncate">{{ $class->name }}</p>
                        <div class="flex items-center gap-1.5 mt-1">
                            <span class="text-xs text-gray-400">{{ $class->grade->name }}</span>
                            <span class="w-1 h-1 rounded-full bg-gray-300 shrink-0"></span>
                            <span
                                class="text-xs px-2 py-0.5 rounded-full font-medium shrink-0
                        {{ $class->courseType->name === 'Regular' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' }}">
                                {{ $class->courseType->name }}
                            </span>
                        </div>
                    </div>
                    @php
                        $pivotAmt     = (int) $class->pivot->amount;
                        $effectiveRate = $pivotAmt > 0 ? $pivotAmt
                            : (strtolower($class->courseType->name) === 'private'
                                ? (int) config('presence.tutor_rate_private')
                                : (int) config('presence.tutor_rate_regular'));
                        $isRegular    = strtolower($class->courseType->name) === 'regular';
                        $minPupils    = (int) config('presence.regular_min_pupils');
                        $minIncentive = (int) config('presence.regular_min_incentive');
                        $extraFee     = (int) ($class->pivot->extra_fee ?? 0);
                    @endphp
                    <div class="text-right shrink-0">
                        <p class="text-xs text-gray-400">{{ $isRegular ? 'Rate / siswa' : 'Rate / sesi' }}</p>
                        <p class="text-sm font-bold text-green-700">Rp {{ number_format($effectiveRate, 0, ',', '.') }}</p>
                        @if($isRegular)
                            <p class="text-xs text-gray-400 mt-0.5">&lt; {{ $minPupils }} siswa → Rp {{ number_format($minIncentive + $extraFee, 0, ',', '.') }}</p>
                        @endif
                    </div>
                </div>

                {{-- Sessions This Week --}}
                <div class="px-4 py-3 space-y-3">
                    @if ($classSessions->isEmpty())
                        <p class="text-xs text-gray-400 text-center py-3">Belum ada sesi minggu ini</p>
                    @else
                        @foreach ($classSessions as $session)
                            @php
                                $sp = $session->tutorPresences->first();
                                $pupilHadir = $session->pupilPresences->where('status', 'presence')->count();
                                $pupilTotal = $session->pupilPresences->count();
                            @endphp
                            <div class="rounded-xl bg-gray-50 border border-gray-100 overflow-hidden">

                                {{-- Foto sesi --}}
                                {{-- @if ($session->photo_file)
                                    <img src="{{ Storage::url($session->photo_file) }}" alt="Foto sesi"
                                        class="w-full h-32 object-cover">
                                @endif --}}

                                {{-- Info sesi --}}
                                <div class="px-3 pt-3 pb-3 flex items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-gray-700">
                                            {{ \Carbon\Carbon::parse($session->date)->translatedFormat('l, d M Y') }}
                                        </p>
                                        @if ($session->material)
                                            <p class="text-xs text-gray-400 mt-0.5">{{ $session->material }}</p>
                                        @endif
                                        @if ($pupilTotal > 0)
                                            <p class="text-xs text-gray-500 mt-0.5">
                                                <span
                                                    class="font-medium text-green-700">{{ $pupilHadir }}</span>/{{ $pupilTotal }}
                                                siswa hadir
                                            </p>
                                        @endif
                                        @if ($sp?->note)
                                            <p class="text-xs text-gray-500 mt-1 italic truncate">📝 {{ $sp->note }}
                                            </p>
                                        @endif
                                        @if ($sp)
                                            @php
                                                $spMap = [
                                                    'presence' => ['Hadir', 'bg-green-100 text-green-700'],
                                                    'absent' => ['Alpha', 'bg-red-100 text-red-700'],
                                                    'sick' => ['Sakit', 'bg-amber-100 text-amber-700'],
                                                    'permission' => ['Izin', 'bg-blue-100 text-blue-700'],
                                                ];
                                                [$spLabel, $spCls] = $spMap[$sp->status] ?? [
                                                    '?',
                                                    'bg-gray-100 text-gray-500',
                                                ];
                                            @endphp
                                            <span
                                                class="inline-flex mt-1.5 px-2 py-0.5 rounded-full text-xs font-medium {{ $spCls }}">{{ $spLabel }}</span>
                                        @else
                                            <span class="text-xs text-gray-400 italic mt-1 block">Belum ada presensi</span>
                                        @endif
                                    </div>
                                    @if ($sp)
                                        <button type="button"
                                            @click="editPresence({
                                presenceId: {{ $sp->id }},
                                actionUrl: '{{ route('my-presences.update', $sp) }}',
                                week: '{{ $weekStart->format('Y-m-d') }}',
                                status: '{{ $sp->status }}',
                                note: '{{ addslashes($sp->note ?? '') }}',
                                material: '{{ addslashes($session->material ?? '') }}',
                                date: '{{ \Carbon\Carbon::parse($session->date)->translatedFormat('l, d M Y') }}',
                                sessionDate: '{{ \Carbon\Carbon::parse($session->date)->format('Y-m-d') }}',
                                photoUrl: '{{ $session->photo_file ? Storage::url($session->photo_file) : '' }}',
                                pupils: {{ Js::from($class->pupils->map(fn($pu) => ['id' => $pu->id, 'name' => $pu->name, 'code' => $pu->code])) }},
                                presentPupilIds: {{ Js::from($session->pupilPresences->where('status','presence')->pluck('pupil_id')) }},
                            })"
                                            class="shrink-0 inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg border border-gray-300 text-xs text-gray-500 hover:bg-gray-100 transition">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                            Edit
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>

                {{-- Add Session Button --}}
                <div class="px-4 pb-4">
                    <button @click="open = true"
                        class="w-full flex items-center justify-center gap-2 py-2.5 rounded-xl border-2 border-dashed border-gray-300 text-xs font-medium text-gray-400 hover:border-green-400 hover:text-green-600 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Tambah Sesi
                    </button>
                </div>

                {{-- Add Session Modal --}}
                <div x-show="open" x-cloak
                    class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:px-[25%] bg-black/50"
                    @click.self="open = false">
                    <div class="bg-white rounded-t-2xl sm:rounded-2xl shadow-xl w-full p-6" @click.stop>
                        <div class="flex items-center justify-between mb-5">
                            <div>
                                <h3 class="font-bold text-gray-800">Tambah Sesi</h3>
                                <p class="text-xs text-gray-400 mt-0.5">{{ $class->name }}</p>
                            </div>
                            <button @click="open = false" class="text-gray-400 hover:text-gray-600 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <form method="POST" action="{{ route('my-presences.store') }}" class="space-y-4"
                            enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="class_id" value="{{ $class->id }}">
                            <input type="hidden" name="week" value="{{ $weekStart->format('Y-m-d') }}">

                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal Sesi</label>
                                <input type="date" name="date" required value="{{ now()->format('Y-m-d') }}"
                                    min="{{ $weekStart->format('Y-m-d') }}" max="{{ $weekEnd->format('Y-m-d') }}"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400">
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">
                                    Materi <span class="text-gray-400 font-normal">(opsional)</span>
                                </label>
                                <input type="text" name="material" placeholder="Materi yang diajarkan..."
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400">
                            </div>

                            {{-- Siswa Hadir --}}
                            @if ($class->pupils->isNotEmpty())
                                <div x-data="{
                                    count: 0,
                                    isRegular: {{ $isRegular ? 'true' : 'false' }},
                                    minPupils: {{ $minPupils }},
                                    minIncentive: {{ $minIncentive + $extraFee }},
                                    ratePerPupil: {{ $effectiveRate }},
                                    extraFee: {{ $extraFee }},
                                    get estimasi() {
                                        if (!this.isRegular) return null;
                                        if (this.count === 0) return null;
                                        if (this.count < this.minPupils) return this.minIncentive;
                                        return (this.ratePerPupil * this.count) + this.extraFee;
                                    },
                                    get label() {
                                        if (this.estimasi === null) return '';
                                        if (this.count < this.minPupils) return 'Insentif minimum';
                                        return 'Estimasi gaji';
                                    }
                                }">
                                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Siswa Hadir</label>
                                    <select name="pupil_ids[]" multiple
                                        id="pupil-select-{{ $class->id }}"
                                        class="pupil-multiselect w-full text-sm"
                                        @change="count = Array.from($event.target.selectedOptions).filter(o => o.value).length">
                                        @foreach ($class->pupils as $pupil)
                                            <option value="{{ $pupil->id }}">
                                                {{ $pupil->name }} ({{ $pupil->code }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @if($isRegular)
                                    <p class="text-xs mt-1.5" x-show="estimasi !== null"
                                        :class="count < minPupils ? 'text-amber-500' : 'text-green-600'">
                                        <span x-text="label"></span>:
                                        Rp <span x-text="estimasi?.toLocaleString('id-ID')"></span>
                                        <template x-if="count < minPupils && count > 0">
                                            <span class="text-gray-400"> (murid &lt; {{ $minPupils }})</span>
                                        </template>
                                    </p>
                                    @endif
                                </div>
                            @endif

                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">
                                    Catatan <span class="text-gray-400 font-normal">(opsional)</span>
                                </label>
                                <textarea name="note" rows="2" placeholder="Catatan tambahan..."
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400 resize-none"></textarea>
                            </div>

                            {{-- Foto Sesi --}}
                            <div x-data="{ preview: null }">
                                <label class="block text-xs font-medium text-gray-600 mb-1">
                                    Foto Sesi <span class="text-gray-400 font-normal">(opsional, maks 5MB)</span>
                                </label>
                                <label
                                    class="flex flex-col items-center justify-center gap-2 w-full rounded-lg border-2 border-dashed border-gray-300 py-4 cursor-pointer hover:border-green-400 hover:bg-green-50 transition"
                                    x-bind:class="preview ? 'border-green-400' : ''">
                                    <template x-if="!preview">
                                        <div class="text-center">
                                            <svg class="w-6 h-6 text-gray-400 mx-auto" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            <p class="text-xs text-gray-400 mt-1">Klik untuk pilih foto</p>
                                        </div>
                                    </template>
                                    <template x-if="preview">
                                        <img :src="preview" class="max-h-32 max-w-xl rounded-lg object-cover">
                                    </template>
                                    <input type="file" name="photo" accept="image/*" class="hidden"
                                        @change="
                                            const f = $event.target.files[0];
                                            if (f && f.size > 5 * 1024 * 1024) {
                                                alert('Ukuran file melebihi batas maksimal 5 MB.');
                                                $event.target.value = '';
                                                preview = null;
                                            } else {
                                                preview = f ? URL.createObjectURL(f) : null;
                                            }
                                        ">
                                </label>
                                <template x-if="preview">
                                    <button type="button"
                                        @click="preview = null; $el.closest('div').querySelector('input[type=file]').value = ''"
                                        class="mt-1 text-xs text-red-400 hover:underline">Hapus foto</button>
                                </template>
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
                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                <p class="text-sm font-medium">Kamu belum diampu di kelas manapun.</p>
                <p class="text-xs mt-1">Hubungi admin untuk mendapatkan assignment kelas.</p>
            </div>
        @endforelse
    </div>

    {{-- History: 4 Minggu Terakhir --}}
    @if ($history->isNotEmpty())
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
            <div class="px-5 py-4 border-b border-gray-100">
                <h2 class="font-bold text-gray-800">Riwayat 4 Minggu Terakhir</h2>
            </div>
            <div class="divide-y divide-gray-100">
                @foreach ($history as $weekKey => $presences)
                    @php
                        $ws = \Carbon\Carbon::parse($weekKey);
                        $we = $ws->copy()->endOfWeek(\Carbon\Carbon::SUNDAY);
                        $earned = $presences->sum(fn($p) => $p->earned);
                        $hadir = $presences->where('status', 'presence')->count();
                    @endphp
                    <details class="group">
                        <summary
                            class="flex items-center justify-between px-5 py-3.5 cursor-pointer hover:bg-gray-50 select-none list-none">
                            <div class="flex items-center gap-2 sm:gap-3 min-w-0">
                                <div
                                    class="w-2 h-2 rounded-full shrink-0 {{ $ws->isCurrentWeek() ? 'bg-green-500' : 'bg-gray-300' }}">
                                </div>
                                <span class="text-sm font-medium text-gray-700 truncate">
                                    {{ $ws->translatedFormat('d M') }} – {{ $we->translatedFormat('d M Y') }}
                                </span>
                                <span class="text-xs text-gray-400 shrink-0">{{ $presences->count() }} sesi ·
                                    {{ $hadir }} hadir</span>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <span class="text-sm font-semibold text-green-700">Rp
                                    {{ number_format($earned, 0, ',', '.') }}</span>
                                <svg class="w-4 h-4 text-gray-400 group-open:rotate-180 transition-transform"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </summary>
                        <div class="px-5 pb-4 space-y-2">
                            @foreach ($presences as $p)
                                @php
                                    $statusMap = [
                                        'presence' => ['Hadir', 'bg-green-100 text-green-700'],
                                        'absent' => ['Alpha', 'bg-red-100 text-red-700'],
                                        'sick' => ['Sakit', 'bg-amber-100 text-amber-700'],
                                        'permission' => ['Izin', 'bg-blue-100 text-blue-700'],
                                    ];
                                    [$slabel, $scls] = $statusMap[$p->status] ?? ['?', 'bg-gray-100 text-gray-500'];
                                @endphp
                                <div class="flex items-center justify-between py-2 border-t border-gray-50 first:border-0">
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-gray-700">
                                            {{ $p->classSession->schoolClass->name ?? '-' }}</p>
                                        <p class="text-xs text-gray-400">
                                            {{ \Carbon\Carbon::parse($p->classSession->date)->translatedFormat('l, d M Y') }}
                                            @if ($p->classSession->material)
                                                · {{ $p->classSession->material }}
                                            @endif
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-2 shrink-0 ml-3">
                                        <span
                                            class="text-xs px-2.5 py-1 rounded-full font-semibold {{ $scls }}">{{ $slabel }}</span>
                                        @if ($p->earned > 0)
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

    {{-- Edit Presence Modal (shared, outside card loop) --}}
    <div x-data="editPresenceModal()" x-cloak>
        <div x-show="open"
            class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:px-[25%] bg-black/50"
            @click.self="open = false">
            <div class="bg-white rounded-t-2xl sm:rounded-2xl shadow-xl w-full p-6" @click.stop>
                <div class="flex items-start justify-between mb-5">
                    <div>
                        <h3 class="font-bold text-gray-800">Edit Presensi</h3>
                        <p class="text-xs text-gray-400 mt-0.5" x-text="current.date"></p>
                    </div>
                    <button @click="open = false" class="text-gray-400 hover:text-gray-600 transition ml-4 shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form method="POST" :action="current.actionUrl" class="space-y-4" enctype="multipart/form-data"
                    x-data="{ preview: null }">
                    @csrf @method('PUT')
                    <input type="hidden" name="week" :value="current.week">

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal Sesi</label>
                        <input type="date" name="session_date" x-model="current.sessionDate"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400">
                    </div>

                    {{-- Siswa Hadir --}}
                    <div x-show="current.pupils && current.pupils.length > 0">
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Siswa Hadir</label>
                        <select name="pupil_ids[]" multiple class="pupil-multiselect-my-edit w-full text-sm">
                            <template x-for="p in (current.pupils || [])" :key="p.id">
                                <option :value="p.id"
                                    :selected="(current.presentPupilIds || []).includes(p.id)"
                                    x-text="p.name + ' (' + p.code + ')'">
                                </option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">
                            Materi <span class="text-gray-400 font-normal">(opsional)</span>
                        </label>
                        <input type="text" name="material" x-model="current.material" placeholder="Materi sesi..."
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">
                            Catatan <span class="text-gray-400 font-normal">(opsional)</span>
                        </label>
                        <textarea name="note" x-model="current.note" rows="2" placeholder="Catatan tambahan..."
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400 resize-none"></textarea>
                    </div>

                    {{-- Photo --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">
                            Foto Sesi <span class="text-gray-400 font-normal">(opsional — upload baru akan hapus yang
                                lama)</span>
                        </label>
                        <template x-if="current.photoUrl && !preview">
                            <img :src="current.photoUrl" class="max-w-xs h-32 object-cover rounded-lg mb-2">
                        </template>
                        <template x-if="preview">
                            <img :src="preview" class="max-w-xs h-32 object-cover rounded-lg mb-2">
                        </template>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <span
                                class="px-3 py-1.5 rounded-lg border border-gray-300 text-xs text-gray-600 hover:bg-gray-50 transition">
                                Pilih Foto Baru
                            </span>
                            <span class="text-xs text-gray-400"
                                x-text="preview ? 'Foto dipilih' : (current.photoUrl ? 'Ada foto sebelumnya' : 'Belum ada foto')"></span>
                            <input type="file" name="photo" accept="image/*" class="hidden"
                                @change="
                                    const f = $event.target.files[0];
                                    if (f && f.size > 5 * 1024 * 1024) {
                                        alert('Ukuran file melebihi batas maksimal 5 MB.');
                                        $event.target.value = '';
                                        preview = null;
                                    } else {
                                        preview = f ? URL.createObjectURL(f) : null;
                                    }
                                ">
                        </label>
                    </div>

                    <button type="submit"
                        class="w-full py-2.5 rounded-xl bg-green-600 hover:bg-green-700 text-white text-sm font-semibold transition">
                        Simpan Perubahan
                    </button>
                </form>

                <form method="POST" id="del-my-presence" action="" class="mt-2"
                    x-effect="$el.action = '/my-presences/' + (current.presenceId ?? '')">
                    @csrf @method('DELETE')
                </form>
                <button type="button"
                    @click="$store.deleteConfirm.show('Hapus presensi ini? Sesi dan data kehadiran siswa ikut terhapus.', 'del-my-presence')"
                    class="w-full py-2.5 rounded-xl border border-red-300 text-red-600 hover:bg-red-50 text-sm font-semibold transition">
                    Hapus Presensi
                </button>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        function initPupilSelects() {
            $('.pupil-multiselect').each(function () {
                if (!$(this).hasClass('select2-hidden-accessible')) {
                    $(this).select2({
                        placeholder: '— Pilih siswa yang hadir —',
                        allowClear: true,
                        width: '100%',
                    });
                    $(this).on('change', function () {
                        this.dispatchEvent(new Event('change', { bubbles: true }));
                    });
                }
            });
        }
        document.addEventListener('DOMContentLoaded', initPupilSelects);
    </script>
    <script>
        function presenceCard(classId) {
            return {
                open: false,
                classId,
                editPresence(data) {
                    window.dispatchEvent(new CustomEvent('open-edit-presence', {
                        detail: data
                    }));
                },
            };
        }

        function editPresenceModal() {
            return {
                open: false,
                current: {},
                init() {
                    window.addEventListener('open-edit-presence', (e) => {
                        this.current = e.detail;
                        this.open = true;
                        this.$nextTick(() => {
                            const form = this.$el.querySelector('form');
                            if (form) {
                                const fileInput = form.querySelector('input[type=file]');
                                if (fileInput) fileInput.value = '';
                                const alpine = form._x_dataStack?.[0];
                                if (alpine) alpine.preview = null;
                            }

                            // Init Select2 for edit pupil select
                            this.$nextTick(() => {
                                const sel = this.$el.querySelector('.pupil-multiselect-my-edit');
                                if (sel) {
                                    if ($(sel).hasClass('select2-hidden-accessible')) $(sel).select2('destroy');
                                    $(sel).select2({ placeholder: '— Pilih siswa yang hadir —', allowClear: true, width: '100%' });
                                    const ids = (e.detail.presentPupilIds || []).map(String);
                                    $(sel).val(ids).trigger('change');
                                }
                            });
                        });
                    });
                },
            };
        }
    </script>
@endpush
