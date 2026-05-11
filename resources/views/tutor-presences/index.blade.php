@extends('layouts.app')

@section('title', 'Presensi Tutor')
@section('header', 'Presensi Tutor')
@section('subheader', 'Rekap kehadiran semua tutor per minggu')

@section('content')

    {{-- Week Navigator --}}
    @if (!$dateFrom && !$dateTo)
        <div class="flex items-center justify-between mb-6">
            <a href="{{ route('tutor-presences.index', array_filter(['date' => $weekStart->copy()->subWeek()->format('Y-m-d'), 'tutor_id' => $tutorId])) }}"
                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-gray-300 bg-white text-sm text-gray-600 hover:bg-gray-50 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                <span class="hidden sm:inline">Minggu Lalu</span>
            </a>
            <div class="text-center">
                <p class="text-xs text-gray-400 uppercase tracking-wide font-semibold">Minggu</p>
                <p class="text-sm sm:text-base font-bold text-gray-800">
                    {{ $weekStart->translatedFormat('d M') }} – {{ $weekEnd->translatedFormat('d M Y') }}
                </p>
            </div>
            <a href="{{ route('tutor-presences.index', array_filter(['date' => $weekStart->copy()->addWeek()->format('Y-m-d'), 'tutor_id' => $tutorId])) }}"
                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-gray-300 bg-white text-sm text-gray-600 hover:bg-gray-50 transition">
                <span class="hidden sm:inline">Minggu Depan</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </a>
        </div>
    @else
        <div class="flex items-center gap-2 mb-6">
            <svg class="w-4 h-4 text-green-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <p class="text-sm font-semibold text-gray-700">
                {{ $weekStart->translatedFormat('d M Y') }} — {{ $weekEnd->translatedFormat('d M Y') }}
            </p>
            <span class="text-xs text-gray-400">(custom range)</span>
        </div>
    @endif

    {{-- Summary Cards --}}
    @php
        $allPresences = $sessions->flatMap->tutorPresences;
        $totalSessions = $sessions->count();
        $totalHadir = $allPresences->where('status', 'presence')->count();
        $totalEarned = $allPresences->sum(fn($p) => $p->earned);
    @endphp
    <div class="flex gap-3 mb-6">
        <div class="flex-1 bg-white rounded-xl border border-gray-200 p-3 sm:p-4 shadow-sm text-center">
            <p class="text-xl sm:text-2xl font-bold text-gray-800">{{ $totalSessions }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Total Sesi</p>
        </div>
        <div class="flex-1 bg-white rounded-xl border border-gray-200 p-3 sm:p-4 shadow-sm text-center">
            <p class="text-xl sm:text-2xl font-bold text-green-600">{{ $totalHadir }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Hadir</p>
        </div>
        <div class="flex-1 bg-white rounded-xl border border-gray-200 p-3 sm:p-4 shadow-sm text-center">
            <p class="text-sm sm:text-xl font-bold text-emerald-700">Rp {{ number_format($totalEarned, 0, ',', '.') }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Pendapatan</p>
        </div>
    </div>

    {{-- Filter & Table --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm" x-data="presenceAdmin()">

        {{-- Filter Bar --}}
        <form method="GET" action="{{ route('tutor-presences.index') }}"
            class="p-4 border-b border-gray-100">
            <div class="flex flex-col md:flex-row md:items-center gap-2">

                {{-- Search --}}
                <div class="relative w-full md:w-52 shrink-0">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z" />
                    </svg>
                    <input type="text" x-model="search" placeholder="Cari tutor atau kelas..."
                        class="w-full pl-9 pr-3 py-2 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-green-400">
                </div>

                <div class="hidden md:block w-px h-5 bg-gray-200 shrink-0"></div>

                {{-- Filters --}}
                <div class="flex flex-wrap items-center gap-2 md:ml-auto">
                    <select name="tutor_id"
                        class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400 bg-white">
                        <option value="">Semua Tutor</option>
                        @foreach ($tutors as $t)
                            <option value="{{ $t->id }}" {{ $tutorId == $t->id ? 'selected' : '' }}>
                                {{ $t->name }}</option>
                        @endforeach
                    </select>

                    <div class="flex items-center gap-1.5">
                        <input type="date" name="date_from" value="{{ $dateFrom ?? $weekStart->format('Y-m-d') }}"
                            class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400 bg-white">
                        <span class="text-gray-400 text-sm">—</span>
                        <input type="date" name="date_to" value="{{ $dateTo ?? $weekEnd->format('Y-m-d') }}"
                            class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400 bg-white">
                    </div>

                    <button type="submit"
                        class="px-4 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm font-semibold transition">
                        Terapkan
                    </button>
                    @if ($tutorId || $dateFrom || $dateTo)
                        <a href="{{ route('tutor-presences.index') }}"
                            class="inline-flex items-center px-3 py-2 rounded-lg border border-gray-300 text-sm text-gray-500 hover:bg-gray-50 transition">
                            Reset
                        </a>
                    @endif

                    @if(auth()->user()?->role?->name === 'superadmin')
                        @php
                            $rekapFrom = $dateFrom ?? $weekStart->format('Y-m-d');
                            $rekapTo   = $dateTo   ?? $weekEnd->format('Y-m-d');
                        @endphp
                        <a href="{{ route('rekap-presence.index', ['from' => $rekapFrom, 'to' => $rekapTo]) }}"
                            target="_blank"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Rekap Tutor
                        </a>
                        <a href="{{ route('rekap-pupil.index', ['from' => $rekapFrom, 'to' => $rekapTo]) }}"
                            target="_blank"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-violet-600 hover:bg-violet-700 text-white text-sm font-semibold transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Rekap Siswa
                        </a>
                    @endif
                </div>
            </div>
        </form>

        {{-- Table --}}
        <div class="p-4">
            @if ($sessions->isEmpty())
                <div class="py-12 text-center text-gray-400">
                    <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <p class="text-sm font-medium">Belum ada sesi pada rentang tanggal ini.</p>
                </div>
            @else
                <div class="overflow-x-auto rounded-lg border border-gray-200">
                    <table class="w-full text-sm min-w-[640px]">
                        <thead>
                            <tr class="bg-gray-50 text-left border-b border-gray-200">
                                <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Tanggal</th>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Kelas</th>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Tutor</th>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide hidden lg:table-cell">Materi</th>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-center">Status</th>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-right">Pendapatan</th>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-center w-16">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($sessions as $session)
                                @php
                                    $isRegular    = strtolower($session->schoolClass->courseType?->name ?? '') === 'regular';
                                    $pupilHadir   = $session->pupilPresences->where('status', 'presence')->count();
                                    $minPupils    = (int) config('presence.regular_min_pupils');
                                    $isBelowMin   = $isRegular && $pupilHadir < $minPupils;
                                @endphp
                                @if ($session->tutorPresences->isEmpty())
                                    <tr class="hover:bg-gray-50"
                                        x-show="!search || '{{ strtolower($session->schoolClass->name) }}'.includes(search.toLowerCase())">
                                        <td class="px-4 py-3 text-gray-600 whitespace-nowrap">
                                            {{ \Carbon\Carbon::parse($session->date)->translatedFormat('d M Y') }}
                                            <span class="block text-xs text-gray-400">{{ \Carbon\Carbon::parse($session->date)->translatedFormat('l') }}</span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <p class="font-medium text-gray-800">{{ $session->schoolClass->name }}</p>
                                            <p class="text-xs text-gray-400">{{ $session->schoolClass->grade->name }}</p>
                                        </td>
                                        <td colspan="5" class="px-4 py-3 text-gray-400 italic text-xs">Belum ada presensi</td>
                                    </tr>
                                @else
                                    @foreach ($session->tutorPresences as $p)
                                        @php
                                            $statusMap = [
                                                'presence'   => ['Hadir', 'bg-green-100 text-green-700'],
                                                'absent'     => ['Alpha', 'bg-red-100 text-red-700'],
                                                'sick'       => ['Sakit', 'bg-amber-100 text-amber-700'],
                                                'permission' => ['Izin',  'bg-blue-100 text-blue-700'],
                                            ];
                                            [$label, $badgeCls] = $statusMap[$p->status] ?? ['?', 'bg-gray-100 text-gray-500'];
                                            $rowSearch = strtolower($p->tutor->name . ' ' . $session->schoolClass->name);
                                        @endphp
                                        <tr class="hover:bg-gray-50"
                                            x-show="!search || '{{ $rowSearch }}'.includes(search.toLowerCase())">
                                            {{-- Tanggal --}}
                                            <td class="px-4 py-3 text-gray-600 whitespace-nowrap">
                                                {{ \Carbon\Carbon::parse($session->date)->translatedFormat('d M Y') }}
                                                <span class="block text-xs text-gray-400">{{ \Carbon\Carbon::parse($session->date)->translatedFormat('l') }}</span>
                                            </td>
                                            {{-- Kelas --}}
                                            <td class="px-4 py-3">
                                                <p class="font-medium text-gray-800">{{ $session->schoolClass->name }}</p>
                                                <p class="text-xs text-gray-400">{{ $session->schoolClass->grade->name }}</p>
                                                <a href="{{ route('class-sessions.pupil-presences.index', $session) }}"
                                                    class="inline-flex items-center gap-1 mt-1 text-xs text-blue-600 hover:underline">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    </svg>
                                                    {{ $pupilHadir }} siswa hadir
                                                </a>
                                            </td>
                                            {{-- Tutor --}}
                                            <td class="px-4 py-3">
                                                <div class="flex items-center gap-2">
                                                    <div class="w-6 h-6 rounded-full bg-green-100 flex items-center justify-center text-xs font-bold text-green-700 shrink-0">
                                                        {{ strtoupper(substr($p->tutor->name, 0, 1)) }}
                                                    </div>
                                                    <span class="font-medium text-gray-700">{{ $p->tutor->name }}</span>
                                                </div>
                                                @if($p->note)
                                                    <p class="text-xs text-gray-400 mt-0.5 italic truncate max-w-[140px]">{{ $p->note }}</p>
                                                @endif
                                            </td>
                                            {{-- Materi --}}
                                            <td class="px-4 py-3 text-gray-500 text-xs hidden lg:table-cell">
                                                {{ $session->material ?? '—' }}
                                            </td>
                                            {{-- Status badge --}}
                                            <td class="px-4 py-3 text-center">
                                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badgeCls }}">
                                                    {{ $label }}
                                                </span>
                                            </td>
                                            {{-- Pendapatan --}}
                                            <td class="px-4 py-3 text-right font-semibold whitespace-nowrap {{ $p->earned > 0 ? 'text-green-700' : 'text-gray-400' }}">
                                                @if ($p->status === 'presence' && $isRegular)
                                                    @if ($pupilHadir === 0)
                                                        <span class="text-xs text-amber-500 block">0 siswa → insentif min</span>
                                                    @elseif ($isBelowMin)
                                                        <span class="text-xs text-amber-500 block">{{ $pupilHadir }} siswa &lt; min {{ $minPupils }}</span>
                                                    @else
                                                        <span class="text-xs text-gray-400 block">{{ $pupilHadir }} siswa</span>
                                                    @endif
                                                @endif
                                                {{ $p->earned > 0 ? 'Rp ' . number_format($p->earned, 0, ',', '.') : '—' }}
                                            </td>
                                            {{-- Edit button --}}
                                            <td class="px-4 py-3 text-center">
                                                <button type="button"
                                                    @click="openEdit({
                                                        presenceId: {{ $p->id }},
                                                        actionUrl: '{{ route('tutor-presences.update', $p) }}',
                                                        tutorName: '{{ addslashes($p->tutor->name) }}',
                                                        className: '{{ addslashes($session->schoolClass->name) }}',
                                                        date: '{{ \Carbon\Carbon::parse($session->date)->translatedFormat('d M Y') }}',
                                                        sessionDate: '{{ \Carbon\Carbon::parse($session->date)->format('Y-m-d') }}',
                                                        status: '{{ $p->status }}',
                                                        note: '{{ addslashes($p->note ?? '') }}',
                                                        material: '{{ addslashes($session->material ?? '') }}',
                                                        photoUrl: '{{ $session->photo_file ? Storage::url($session->photo_file) : '' }}',
                                                        pupils: @json($session->schoolClass->pupils->map(fn($pu) => ['id' => $pu->id, 'name' => $pu->name, 'code' => $pu->code])),
                                                        presentPupilIds: @json($session->pupilPresences->where('status','presence')->pluck('pupil_id')),
                                                    })"
                                                    class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg border border-gray-300 text-xs text-gray-600 hover:bg-gray-50 transition">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                    </svg>
                                                    Edit
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            @endforeach
                        </tbody>
                        @if ($totalEarned > 0)
                            <tfoot>
                                <tr class="bg-green-50 border-t-2 border-green-200">
                                    <td colspan="6" class="px-4 py-3 text-sm font-semibold text-green-800 text-right">
                                        Total Pendapatan</td>
                                    <td class="px-4 py-3 text-right font-bold text-green-700 whitespace-nowrap">
                                        Rp {{ number_format($totalEarned, 0, ',', '.') }}
                                    </td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            @endif
        </div>

        {{-- Edit Modal --}}
        <div x-show="modalOpen" x-cloak
            class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-black/50"
            @click.self="modalOpen = false">
            <div class="bg-white rounded-t-2xl sm:rounded-2xl shadow-xl w-full sm:max-w-md p-6" @click.stop>

                {{-- Header --}}
                <div class="flex items-start justify-between mb-5">
                    <div>
                        <h3 class="font-bold text-gray-800">Edit Presensi</h3>
                        <p class="text-xs text-gray-400 mt-0.5" x-text="current.tutorName + ' · ' + current.className"></p>
                        <p class="text-xs text-gray-400" x-text="current.date"></p>
                    </div>
                    <button @click="modalOpen = false" class="text-gray-400 hover:text-gray-600 transition ml-4 shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form method="POST" :action="current.actionUrl" class="space-y-4"
                    enctype="multipart/form-data" x-data="{ preview: null }">
                    @csrf @method('PUT')

                    {{-- Tanggal --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal Sesi</label>
                        <input type="date" name="session_date" x-model="current.sessionDate"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400">
                    </div>

                    {{-- Siswa Hadir --}}
                    <div x-show="current.pupils && current.pupils.length > 0">
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Siswa Hadir</label>
                        <select name="pupil_ids[]" multiple class="pupil-multiselect-edit w-full text-sm"
                            x-ref="pupilSelect">
                            <template x-for="p in (current.pupils || [])" :key="p.id">
                                <option :value="p.id"
                                    :selected="(current.presentPupilIds || []).includes(p.id)"
                                    x-text="p.name + ' (' + p.code + ')'">
                                </option>
                            </template>
                        </select>
                    </div>

                    {{-- Materi --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">
                            Materi <span class="text-gray-400 font-normal">(opsional)</span>
                        </label>
                        <input type="text" name="material" x-model="current.material"
                            placeholder="Materi sesi..."
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400">
                    </div>

                    {{-- Catatan --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">
                            Catatan <span class="text-gray-400 font-normal">(opsional)</span>
                        </label>
                        <textarea name="note" x-model="current.note" rows="2"
                            placeholder="Catatan tambahan..."
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400 resize-none"></textarea>
                    </div>

                    {{-- Foto --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">
                            Foto Sesi <span class="text-gray-400 font-normal">(opsional — upload baru hapus yang lama)</span>
                        </label>
                        <template x-if="current.photoUrl && !preview">
                            <img :src="current.photoUrl" class="block max-w-xs h-28 object-cover rounded-lg mb-2">
                        </template>
                        <template x-if="preview">
                            <img :src="preview" class="block max-w-xs h-28 object-cover rounded-lg mb-2">
                        </template>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <span class="px-3 py-1.5 rounded-lg border border-gray-300 text-xs text-gray-600 hover:bg-gray-50 transition">
                                Pilih Foto Baru
                            </span>
                            <span class="text-xs text-gray-400"
                                x-text="preview ? 'Foto dipilih' : (current.photoUrl ? 'Ada foto sebelumnya' : 'Belum ada foto')"></span>
                            <input type="file" name="photo" accept="image/*" class="hidden"
                                @change="preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null">
                        </label>
                    </div>

                    <button type="submit"
                        class="w-full py-2.5 rounded-xl bg-green-600 hover:bg-green-700 text-white text-sm font-semibold transition">
                        Simpan Perubahan
                    </button>
                </form>

                <form method="POST" id="del-tp-presence" action=""
                    x-effect="$el.action = '/tutor-presences/' + (current.presenceId ?? '')">
                    @csrf @method('DELETE')
                </form>
                <button type="button"
                    @click="$store.deleteConfirm.show('Hapus presensi ' + current.tutorName + ' (' + current.date + ')? Sesi dan data siswa ikut terhapus jika tidak ada tutor lain.', 'del-tp-presence')"
                    class="w-full py-2.5 rounded-xl border border-red-300 text-red-600 hover:bg-red-50 text-sm font-semibold transition">
                    Hapus Presensi
                </button>
            </div>
        </div>

    </div>

@endsection

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <style>
        .select2-container--default .select2-selection--multiple { border-radius:.5rem;border-color:#d1d5db;min-height:38px;padding:2px 6px; }
        .select2-container--default.select2-container--focus .select2-selection--multiple { border-color:#4ade80;box-shadow:0 0 0 2px rgb(74 222 128/.4); }
        .select2-container--default .select2-selection--multiple .select2-selection__choice { background:#dcfce7;border-color:#86efac;color:#166534;border-radius:.375rem;font-size:.75rem;padding:1px 6px; }
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove { color:#16a34a; }
        .select2-dropdown { border-color:#d1d5db;border-radius:.5rem;font-size:.875rem; }
        .select2-search--dropdown .select2-search__field { border-radius:.375rem;border-color:#d1d5db; }
    </style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
function presenceAdmin() {
    return {
        search: '',
        modalOpen: false,
        current: {},
        openEdit(data) {
            this.current = data;
            this.modalOpen = true;
            this.$nextTick(() => {
                const form = this.$el.querySelector('form[enctype]');
                if (form) {
                    const fileInput = form.querySelector('input[type=file]');
                    if (fileInput) fileInput.value = '';
                    const alpine = form._x_dataStack?.[0];
                    if (alpine) alpine.preview = null;
                }

                // Init Select2 for pupil select after Alpine renders options
                this.$nextTick(() => {
                    const sel = this.$el.querySelector('.pupil-multiselect-edit');
                    if (sel) {
                        if ($(sel).hasClass('select2-hidden-accessible')) $(sel).select2('destroy');
                        $(sel).select2({ placeholder: '— Pilih siswa yang hadir —', allowClear: true, width: '100%' });
                        // Pre-select present pupils
                        const ids = (data.presentPupilIds || []).map(String);
                        $(sel).val(ids).trigger('change');
                    }
                });
            });
        },
    };
}
</script>
@endpush
