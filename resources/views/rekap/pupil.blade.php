@extends('layouts.app')

@section('title', 'Rekap Presensi Siswa')
@section('header', 'Rekap Presensi Siswa')
@section('subheader', 'Ringkasan kehadiran siswa per periode')

@push('styles')
<style>
    @media print {
        @page { size: A4 landscape; margin: 10mm 8mm; }
        #sidebar, header, nav, .no-print { display: none !important; }
        main { padding: 0 !important; margin: 0 !important; }
        .print-doc { box-shadow: none !important; }
        table { font-size: 8px !important; }
        th, td { padding: 3px 4px !important; }
    }
</style>
@endpush

@section('content')

{{-- Filter Bar --}}
<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 mb-6 no-print">
    <form method="GET" action="{{ route('rekap-pupil.index') }}"
        class="flex flex-wrap items-end gap-3">
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Dari</label>
            <input type="date" name="from" value="{{ $from->format('Y-m-d') }}"
                class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Sampai</label>
            <input type="date" name="to" value="{{ $to->format('Y-m-d') }}"
                class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Kelas</label>
            <select name="class_id"
                class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400 bg-white">
                <option value="">Semua Kelas</option>
                @foreach($classes as $class)
                    <option value="{{ $class->id }}" @selected($classId == $class->id)>{{ $class->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit"
            class="px-4 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm font-semibold transition">
            Tampilkan
        </button>
        <button type="button" onclick="window.print()"
            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-violet-600 hover:bg-violet-700 text-white text-sm font-semibold transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            Print / Export PDF
        </button>
    </form>
</div>

{{-- Document --}}
<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 print-doc">

    <div class="text-center mb-6">
        <h2 class="text-base font-bold uppercase tracking-wide">Rekap Presensi Siswa</h2>
        <p class="text-sm text-gray-500 mt-1">
            Periode {{ $from->translatedFormat('d F Y') }} – {{ $to->translatedFormat('d F Y') }}
        </p>
    </div>

    @if(empty($rows))
        <div class="py-16 text-center text-gray-400">
            <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 9v10a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-sm font-medium">Tidak ada data presensi siswa pada periode ini.</p>
        </div>
    @else
    <div class="overflow-x-auto">
        <table class="w-full text-xs border-collapse">
            <thead>
                <tr>
                    <th class="border border-gray-400 bg-cyan-700 text-white px-3 py-2">#</th>
                    <th class="border border-gray-400 bg-cyan-700 text-white px-3 py-2 text-left">NAMA SISWA</th>
                    <th class="border border-gray-400 bg-cyan-700 text-white px-3 py-2 text-left">KELAS</th>
                    <th class="border border-gray-400 bg-cyan-700 text-white px-3 py-2"
                        colspan="{{ count($weekLabels) }}">PEKAN (SESI HADIR)</th>
                    <th class="border border-gray-400 bg-cyan-700 text-white px-3 py-2">TOTAL SESI</th>
                    <th class="border border-gray-400 bg-cyan-700 text-white px-3 py-2">HADIR</th>
                    <th class="border border-gray-400 bg-cyan-700 text-white px-3 py-2">TDK HADIR</th>
                    <th class="border border-gray-400 bg-cyan-700 text-white px-3 py-2">% HADIR</th>
                </tr>
                <tr>
                    <th class="border border-gray-400 bg-cyan-800 text-white px-3 py-1" colspan="3"></th>
                    @foreach($weekLabels as $i => $label)
                        <th class="border border-gray-400 bg-cyan-800 text-white px-2 py-1">{{ $i + 1 }}</th>
                    @endforeach
                    <th class="border border-gray-400 bg-cyan-800 text-white px-3 py-1" colspan="4"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $i => $row)
                @php
                    $persenColor = $row['persen'] >= 80
                        ? 'text-green-700 font-bold'
                        : ($row['persen'] >= 60 ? 'text-amber-600 font-bold' : 'text-red-600 font-bold');
                @endphp
                <tr class="hover:bg-gray-50">
                    <td class="border border-gray-300 px-3 py-2 text-center text-gray-500">{{ $i + 1 }}</td>
                    <td class="border border-gray-300 px-3 py-2 font-medium whitespace-nowrap">{{ $row['pupil']->name }}</td>
                    <td class="border border-gray-300 px-3 py-2 whitespace-nowrap">{{ $row['className'] }}</td>
                    @foreach($weekLabels as $label)
                        <td class="border border-gray-300 px-2 py-2 text-center">{{ $row['weeks'][$label] ?? 0 }}</td>
                    @endforeach
                    <td class="border border-gray-300 px-2 py-2 text-center">{{ $row['total'] }}</td>
                    <td class="border border-gray-300 px-2 py-2 text-center text-green-700 font-bold">{{ $row['hadir'] }}</td>
                    <td class="border border-gray-300 px-2 py-2 text-center text-red-600">{{ $row['absen'] }}</td>
                    <td class="border border-gray-300 px-2 py-2 text-center {{ $persenColor }}">{{ $row['persen'] }}%</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

@endsection
