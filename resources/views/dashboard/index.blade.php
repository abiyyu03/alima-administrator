@extends('layouts.app')

@section('title', 'Dashboard')
@section('header', 'Hi, ' . auth()->user()->name . '! 😊')
@section('subheader', 'Bagaimana harimu? Berikut ringkasan panel administrasi Alima')

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@section('content')

{{-- Stat Cards --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <x-card>
        <div class="flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0" style="background:#dcfce7">
                <svg class="w-5 h-5" style="color:#16a34a" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-800">{{ $totalPupils }}</p>
                <p class="text-xs text-gray-500">Siswa Aktif</p>
            </div>
        </div>
    </x-card>

    <x-card>
        <div class="flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0" style="background:#dbeafe">
                <svg class="w-5 h-5" style="color:#2563eb" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-800">{{ $totalTutors }}</p>
                <p class="text-xs text-gray-500">Total Tutor</p>
            </div>
        </div>
    </x-card>

    <x-card>
        <div class="flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0" style="background:#fef9c3">
                <svg class="w-5 h-5" style="color:#ca8a04" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/>
                </svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-800">{{ $totalClasses }}</p>
                <p class="text-xs text-gray-500">Total Kelas</p>
            </div>
        </div>
    </x-card>

    <x-card>
        <div class="flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0" style="background:#f3e8ff">
                <svg class="w-5 h-5" style="color:#9333ea" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-800">{{ $totalSessionsToday }}</p>
                <p class="text-xs text-gray-500">Sesi Hari Ini</p>
            </div>
        </div>
    </x-card>
</div>

{{-- Keuangan Bulan Ini --}}
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm px-5 py-4">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Gaji Tutor — {{ now()->translatedFormat('F Y') }}</p>
        <p class="text-2xl font-bold text-red-500">Rp {{ number_format($gajiTutorBulanIni, 0, ',', '.') }}</p>
        <p class="text-xs text-gray-400 mt-1">Total gaji terhitung dari presensi bulan ini</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm px-5 py-4">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Tagihan Dev Class — {{ now()->translatedFormat('F Y') }}</p>
        <p class="text-2xl font-bold text-orange-500">Rp {{ number_format($tagihanDevBulanIni, 0, ',', '.') }}</p>
        <p class="text-xs text-gray-400 mt-1">Akumulasi rate per anak × sesi hadir Development Class bulan ini</p>
    </div>
</div>

{{-- Grafik + Top Tutor --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- Grafik Presensi Siswa 8 Minggu --}}
    <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 shadow-sm p-5">
        <p class="text-sm font-semibold text-gray-700 mb-4">Tren Kehadiran Siswa (8 Minggu Terakhir)</p>
        <canvas id="presenceChart" height="120"></canvas>
    </div>

    {{-- Top Tutor Gaji --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
        <p class="text-sm font-semibold text-gray-700 mb-4">Top Earned Tutor — {{ now()->translatedFormat('F Y') }}</p>
        @forelse($topTutors as $i => $row)
        <div class="flex items-center gap-3 mb-3">
            <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold shrink-0"
                style="background:{{ ['#dcfce7','#dbeafe','#fef9c3','#ffe4e6','#f3e8ff'][$i] ?? '#f3f4f6' }};
                       color:{{ ['#15803d','#1d4ed8','#a16207','#be123c','#7e22ce'][$i] ?? '#374151' }}">
                {{ $i + 1 }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-800 truncate">{{ $row->tutor?->name ?? '-' }}</p>
                <p class="text-xs text-gray-400">Rp {{ number_format($row->total, 0, ',', '.') }}</p>
            </div>
        </div>
        @empty
        <p class="text-sm text-gray-400">Belum ada data bulan ini.</p>
        @endforelse
    </div>

</div>

@push('scripts')
<script>
const ctx = document.getElementById('presenceChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: {!! json_encode(array_column($weeks, 'label')) !!},
        datasets: [
            {
                label: 'Siswa Hadir',
                data: {!! json_encode(array_column($weeks, 'hadir')) !!},
                backgroundColor: 'rgba(22,163,74,0.15)',
                borderColor: '#16a34a',
                borderWidth: 2,
                borderRadius: 6,
                order: 2,
            },
            {
                label: 'Total Sesi',
                data: {!! json_encode(array_column($weeks, 'sesi')) !!},
                type: 'line',
                borderColor: '#f97316',
                backgroundColor: 'transparent',
                borderWidth: 2,
                pointRadius: 4,
                pointBackgroundColor: '#f97316',
                tension: 0.3,
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
            y: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 11 } } },
            x: { ticks: { font: { size: 11 } } },
        }
    }
});
</script>
@endpush

@endsection
