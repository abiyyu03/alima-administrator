@extends('layouts.app')

@section('title', 'Presensi Siswa')
@section('header', 'Presensi Siswa')
@section('subheader', $classSession->schoolClass->name . ' — ' . $classSession->date->translatedFormat('l, d M Y'))

@section('breadcrumb')
    <a href="{{ route('pupil-presences.index') }}" class="hover:text-green-600">Presensi Siswa</a>
    <span class="mx-2">/</span>
    <span class="text-gray-700 font-medium">Presensi Siswa</span>
@endsection

@section('content')

@php
    $isRegular = $classSession->schoolClass->courseType?->name === 'Regular';
    $hadirCount = $existing->where('status', 'presence')->count();
@endphp

{{-- Info Bar --}}
<div class="flex flex-wrap items-center gap-3 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 px-4 py-3 shadow-sm flex items-center gap-2">
        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
        </svg>
        <span class="text-sm font-medium text-gray-700">{{ $classSession->schoolClass->name }}</span>
        <span class="text-xs px-2 py-0.5 rounded-full font-medium
            {{ $isRegular ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' }}">
            {{ $classSession->schoolClass->courseType?->name }}
        </span>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 px-4 py-3 shadow-sm text-sm text-gray-600">
        <span class="font-bold text-green-700">{{ $hadirCount }}</span> / {{ $pupils->count() }} siswa hadir
    </div>
    @if($isRegular && $classSession->tutorPresences->isNotEmpty())
    <div class="bg-white rounded-xl border border-gray-200 px-4 py-3 shadow-sm text-sm text-gray-600">
        Gaji tutor dihitung otomatis dari jumlah siswa hadir
    </div>
    @endif
</div>

@if($pupils->isEmpty())
    <div class="bg-white rounded-xl border border-gray-200 p-12 text-center text-gray-400 shadow-sm">
        <p class="text-sm font-medium">Belum ada siswa aktif di kelas ini.</p>
    </div>
@else
<form method="POST" action="{{ route('class-sessions.pupil-presences.store', $classSession) }}">
    @csrf
    <x-card title="Daftar Siswa">
        <x-slot:actions>
            <button type="submit"
                class="px-4 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm font-semibold transition">
                Simpan Semua
            </button>
        </x-slot:actions>

        <x-table>
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">#</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Siswa</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase hidden md:table-cell">Kode</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase hidden lg:table-cell">Catatan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100" x-data="{ counts: {} }" x-init="
                $nextTick(() => {
                    document.querySelectorAll('[data-hadir-select]').forEach(sel => {
                        counts[sel.dataset.hadirSelect] = sel.value;
                    });
                })
            ">
                @foreach($pupils as $i => $pupil)
                @php $p = $existing->get($pupil->id); @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-gray-400 text-sm">{{ $i + 1 }}</td>
                    <td class="px-4 py-3 font-medium text-gray-800">{{ $pupil->name }}</td>
                    <td class="px-4 py-3 text-sm font-mono text-gray-500 hidden md:table-cell">{{ $pupil->code }}</td>
                    <td class="px-4 py-3 text-center" x-data>
                        <select name="presences[{{ $pupil->id }}][status]"
                            data-hadir-select="{{ $pupil->id }}"
                            @change="
                                $dispatch('hadir-change');
                            "
                            class="rounded-lg border px-2 py-1.5 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-green-400 cursor-pointer
                                {{ ($p?->status ?? 'presence') === 'presence'   ? 'bg-green-100 text-green-700 border-green-200' : '' }}
                                {{ ($p?->status ?? '') === 'absent'             ? 'bg-red-100 text-red-700 border-red-200'       : '' }}
                                {{ ($p?->status ?? '') === 'sick'               ? 'bg-amber-100 text-amber-700 border-amber-200' : '' }}
                                {{ ($p?->status ?? '') === 'permission'         ? 'bg-blue-100 text-blue-700 border-blue-200'    : '' }}"
                            x-on:change="
                                const cls = {
                                    presence: 'bg-green-100 text-green-700 border-green-200',
                                    absent:   'bg-red-100 text-red-700 border-red-200',
                                    sick:     'bg-amber-100 text-amber-700 border-amber-200',
                                    permission:'bg-blue-100 text-blue-700 border-blue-200',
                                };
                                $el.className = 'rounded-lg border px-2 py-1.5 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-green-400 cursor-pointer ' + (cls[$el.value] ?? '');
                            ">
                            <option value="presence"   {{ ($p?->status ?? 'presence') === 'presence'   ? 'selected' : '' }}>Hadir</option>
                            <option value="sick"       {{ ($p?->status) === 'sick'       ? 'selected' : '' }}>Sakit</option>
                            <option value="permission" {{ ($p?->status) === 'permission' ? 'selected' : '' }}>Izin</option>
                            <option value="absent"     {{ ($p?->status) === 'absent'     ? 'selected' : '' }}>Alpha</option>
                        </select>
                    </td>
                    <td class="px-4 py-3 hidden lg:table-cell">
                        <input type="text" name="presences[{{ $pupil->id }}][note]"
                            value="{{ $p?->note }}"
                            placeholder="Catatan..."
                            class="w-full rounded-lg border border-gray-200 px-2 py-1 text-xs text-gray-600 focus:outline-none focus:ring-2 focus:ring-green-400">
                    </td>
                </tr>
                @endforeach
            </tbody>
        </x-table>

        {{-- Tutor salary preview for Regular --}}
        @if($isRegular && $classSession->tutorPresences->where('status', 'presence')->isNotEmpty())
        <div class="px-4 py-3 border-t border-gray-100 bg-amber-50">
            <p class="text-xs text-amber-700 font-medium">
                Catatan: gaji tutor kelas Regular dihitung ulang otomatis saat presensi disimpan.
                Saat ini <strong>{{ $hadirCount }} siswa hadir</strong>.
            </p>
        </div>
        @endif
    </x-card>
</form>
@endif

@endsection
