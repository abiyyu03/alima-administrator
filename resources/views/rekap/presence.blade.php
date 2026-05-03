@extends('layouts.app')

@section('title', 'Rekap Presensi Tutor')
@section('header', 'Rekap Presensi Tutor')
@section('subheader', 'Ringkasan kehadiran tutor per periode')

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
    <form method="GET" action="{{ route('rekap-presence.index') }}"
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
        <button type="submit"
            class="px-4 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm font-semibold transition">
            Tampilkan
        </button>
        <button type="button" onclick="window.print()"
            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold transition">
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

    {{-- Doc Header --}}
    <div class="text-center mb-6">
        <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-1">{{ config('app.name') }}</p>
        <h2 class="text-lg font-bold uppercase tracking-wide">Rekap Presensi Tutor</h2>
        <p class="text-sm text-gray-500 mt-1">
            Periode {{ $from->translatedFormat('d F Y') }} – {{ $to->translatedFormat('d F Y') }}
        </p>
        <div class="mt-2 flex justify-center gap-4 text-xs text-gray-400">
            <span>Dicetak: {{ now()->translatedFormat('d F Y, H:i') }}</span>
        </div>
    </div>

    @if(empty($rows))
        <div class="py-16 text-center text-gray-400">
            <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 9v10a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-sm font-medium">Tidak ada data presensi pada periode ini.</p>
        </div>
    @else
    <div class="overflow-x-auto">
        <table class="w-full text-xs border-collapse">
            <thead style="background-color:#0e7490;color:#ffffff;">
                <tr>
                    <th class="border border-gray-400 px-3 py-2"
                        colspan="{{ 3 + count($weekLabels) }}">KELAS REGULER</th>
                    <th class="border border-gray-400 px-3 py-2"
                        colspan="{{ 3 + count($weekLabels) }}">KELAS PRIVAT</th>
                </tr>
                <tr>
                    <th class="border border-gray-400 px-3 py-2 text-left" rowspan="2">NAMA TUTOR</th>
                    <th class="border border-gray-400 px-3 py-2" colspan="{{ count($weekLabels) }}">PEKAN</th>
                    <th class="border border-gray-400 px-3 py-2" rowspan="2">JUMLAH</th>
                    <th class="border border-gray-400 px-3 py-2" rowspan="2">GAJI</th>
                    <th class="border border-gray-400 px-3 py-2 text-left" rowspan="2">NAMA TUTOR</th>
                    <th class="border border-gray-400 px-3 py-2" colspan="{{ count($weekLabels) }}">PEKAN</th>
                    <th class="border border-gray-400 px-3 py-2" rowspan="2">JUMLAH</th>
                    <th class="border border-gray-400 px-3 py-2" rowspan="2">GAJI</th>
                    <th class="border border-gray-400 px-3 py-2" rowspan="2">TOTAL GAJI</th>
                </tr>
                <tr>
                    @foreach($weekLabels as $i => $label)
                    @php $w = $weeks[$label]; @endphp
                        <th class="border border-gray-400 px-2 py-2 text-center leading-tight">
                            <div>P{{ $i + 1 }}</div>
                            <div class="font-normal opacity-80" style="font-size:9px">
                                {{ $w['from']->format('d/m') }}–{{ $w['to']->format('d/m') }}
                            </div>
                        </th>
                    @endforeach
                    @foreach($weekLabels as $i => $label)
                    @php $w = $weeks[$label]; @endphp
                        <th class="border border-gray-400 px-2 py-2 text-center leading-tight">
                            <div>P{{ $i + 1 }}</div>
                            <div class="font-normal opacity-80" style="font-size:9px">
                                {{ $w['from']->format('d/m') }}–{{ $w['to']->format('d/m') }}
                            </div>
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $row)
                <tr class="hover:bg-gray-50">
                    <td class="border border-gray-300 px-3 py-2 font-medium whitespace-nowrap">{{ $row['tutor']->name }}</td>
                    @foreach($weekLabels as $label)
                        <td class="border border-gray-300 px-2 py-2 text-center">
                            {{ $row['regular'] ? ($row['regular']['weeks'][$label] ?? 0) : 0 }}
                        </td>
                    @endforeach
                    <td class="border border-gray-300 px-2 py-2 text-center font-bold">
                        {{ $row['regular'] ? $row['regular']['total'] : 0 }}
                    </td>
                    <td class="border border-gray-300 px-3 py-2 text-right whitespace-nowrap">
                        {{ $row['regular'] ? 'Rp ' . number_format($row['regular']['gaji'], 0, ',', '.') : 'Rp 0' }}
                    </td>
                    <td class="border border-gray-300 px-3 py-2 font-medium whitespace-nowrap">{{ $row['tutor']->name }}</td>
                    @foreach($weekLabels as $label)
                        <td class="border border-gray-300 px-2 py-2 text-center">
                            {{ $row['private'] ? ($row['private']['weeks'][$label] ?? 0) : 0 }}
                        </td>
                    @endforeach
                    <td class="border border-gray-300 px-2 py-2 text-center font-bold">
                        {{ $row['private'] ? $row['private']['total'] : 0 }}
                    </td>
                    <td class="border border-gray-300 px-3 py-2 text-right whitespace-nowrap">
                        {{ $row['private'] ? 'Rp ' . number_format($row['private']['gaji'], 0, ',', '.') : 'Rp 0' }}
                    </td>
                    @php $totalGaji = ($row['regular']['gaji'] ?? 0) + ($row['private']['gaji'] ?? 0); @endphp
                    <td class="border border-gray-300 px-3 py-2 text-right font-bold text-green-700 whitespace-nowrap">
                        Rp {{ number_format($totalGaji, 0, ',', '.') }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

@endsection
