@extends('layouts.app')

@section('title', 'Dashboard')
@section('header', 'Hi, ' . $user->name . '! 😊')
@section('subheader', 'Bagaimana harimu? Berikut ringkasan aktivitasmu')

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@section('content')

@if(! $tutor)
    <div class="bg-amber-50 border border-amber-200 rounded-xl px-5 py-4 text-sm text-amber-700">
        Akun kamu belum terhubung ke data tutor. Hubungi admin untuk menghubungkan akunmu.
    </div>
@else

{{-- Stat Cards --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm px-5 py-4">
        <p class="text-xs text-gray-400 mb-1">Penghasilan Bulan Ini</p>
        <p class="text-xl font-bold text-green-600">Rp {{ number_format($gajiBulanIni, 0, ',', '.') }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm px-5 py-4">
        <p class="text-xs text-gray-400 mb-1">Total Penghasilan</p>
        <p class="text-xl font-bold text-gray-800">Rp {{ number_format($gajiTotal, 0, ',', '.') }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm px-5 py-4">
        <p class="text-xs text-gray-400 mb-1">Sesi Bulan Ini</p>
        <p class="text-xl font-bold text-blue-600">{{ $sesiBulanIni }} sesi</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm px-5 py-4">
        <p class="text-xs text-gray-400 mb-1">Total Sesi Hadir</p>
        <p class="text-xl font-bold text-gray-800">{{ $sesiTotal }} sesi</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-5">

    {{-- Grafik Penghasilan + Sesi --}}
    <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 shadow-sm p-5">
        <p class="text-sm font-semibold text-gray-700 mb-4">Tren Penghasilan & Sesi (10 Minggu Terakhir)</p>
        <canvas id="tutorChart" height="130"></canvas>
    </div>

    {{-- Kelas Diampu --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
        <p class="text-sm font-semibold text-gray-700 mb-4">Kelas yang Diampu</p>
        @forelse($classes as $class)
        <div class="flex items-start gap-2 mb-3">
            <span class="inline-flex mt-0.5 px-1.5 py-0.5 rounded text-xs font-medium
                {{ $class->courseType?->name === 'Regular' ? 'bg-blue-100 text-blue-600' : 'bg-purple-100 text-purple-600' }}">
                {{ $class->courseType?->name }}
            </span>
            <div>
                <p class="text-sm font-medium text-gray-800">{{ $class->name }}</p>
                <p class="text-xs text-gray-400">{{ $class->grade->name }}</p>
            </div>
        </div>
        @empty
        <p class="text-sm text-gray-400">Belum ada kelas yang diampu.</p>
        @endforelse
    </div>

</div>

{{-- Presensi Terbaru --}}
<x-card title="Presensi Terbaru">
    <x-table>
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Tanggal</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Kelas</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Status</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Penghasilan</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($recentPresences as $presence)
            @php
                $statusLabel = match($presence->status) {
                    'presence'   => ['Hadir',  'bg-green-100 text-green-700'],
                    'absent'     => ['Alpha',  'bg-red-100 text-red-700'],
                    'sick'       => ['Sakit',  'bg-amber-100 text-amber-700'],
                    'permission' => ['Izin',   'bg-blue-100 text-blue-700'],
                    default      => [$presence->status, 'bg-gray-100 text-gray-600'],
                };
            @endphp
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3">
                    <div class="text-sm font-medium text-gray-800">{{ $presence->classSession->date->translatedFormat('d M Y') }}</div>
                    <div class="text-xs text-gray-400">{{ $presence->classSession->date->translatedFormat('l') }}</div>
                </td>
                <td class="px-4 py-3">
                    <div class="text-sm text-gray-800">{{ $presence->classSession->schoolClass->name }}</div>
                    @if($presence->classSession->schoolClass->courseType)
                    <span class="text-xs px-1.5 py-0.5 rounded font-medium
                        {{ $presence->classSession->schoolClass->courseType->name === 'Regular' ? 'bg-blue-100 text-blue-600' : 'bg-purple-100 text-purple-600' }}">
                        {{ $presence->classSession->schoolClass->courseType->name }}
                    </span>
                    @endif
                </td>
                <td class="px-4 py-3 text-center">
                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $statusLabel[1] }}">
                        {{ $statusLabel[0] }}
                    </span>
                </td>
                <td class="px-4 py-3 text-right text-sm font-semibold {{ $presence->amount > 0 ? 'text-green-600' : 'text-gray-400' }}">
                    {{ $presence->amount > 0 ? 'Rp ' . number_format($presence->amount, 0, ',', '.') : '-' }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-400">Belum ada presensi tercatat.</td>
            </tr>
            @endforelse
        </tbody>
    </x-table>
    <div class="px-4 py-3 border-t border-gray-100">
        <a href="{{ route('my-presences') }}" class="text-sm text-green-600 hover:underline">Lihat semua presensi →</a>
    </div>
</x-card>

@endif

@push('scripts')
@if($tutor)
<script>
const ctx = document.getElementById('tutorChart')?.getContext('2d');
if (ctx) {
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: {!! json_encode(array_column($weeks, 'label')) !!},
            datasets: [
                {
                    label: 'Penghasilan (Rp)',
                    data: {!! json_encode(array_column($weeks, 'gaji')) !!},
                    backgroundColor: 'rgba(22,163,74,0.15)',
                    borderColor: '#16a34a',
                    borderWidth: 2,
                    borderRadius: 6,
                    yAxisID: 'y',
                    order: 2,
                },
                {
                    label: 'Sesi Hadir',
                    data: {!! json_encode(array_column($weeks, 'sesi')) !!},
                    type: 'line',
                    borderColor: '#3b82f6',
                    backgroundColor: 'transparent',
                    borderWidth: 2,
                    pointRadius: 4,
                    pointBackgroundColor: '#3b82f6',
                    tension: 0.3,
                    yAxisID: 'y2',
                    order: 1,
                },
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } },
            },
            scales: {
                y:  { beginAtZero: true, position: 'left',  ticks: { font: { size: 10 }, callback: v => 'Rp' + (v/1000) + 'k' } },
                y2: { beginAtZero: true, position: 'right', ticks: { stepSize: 1, font: { size: 10 } }, grid: { drawOnChartArea: false } },
                x:  { ticks: { font: { size: 11 } } },
            }
        }
    });
}
</script>
@endif
@endpush

@endsection
