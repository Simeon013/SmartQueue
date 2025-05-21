@extends('layouts.admin')

@php
    use SimpleSoftwareIO\QrCode\Facades\QrCode;
@endphp

@section('header', 'Détail de la file d\'attente')

@section('content')
    <div class="bg-white p-8 rounded shadow">
        <h2 class="text-2xl font-bold mb-4 flex items-center gap-4">
            {{ $queue->name }}
            @if($queue->code)
                <span>
                    {!! QrCode::size(80)->generate(route('public.queue.show.code', $queue->code)) !!}
                </span>
            @endif
        </h2>
        <p class="mb-2"><span class="font-semibold">Tickets :</span> {{ $stats['total_tickets'] ?? 0 }}</p>
        <p class="mb-2"><span class="font-semibold">Tickets actifs :</span> {{ $stats['active_tickets'] ?? 0 }}</p>
        <p class="mb-2"><span class="font-semibold">Temps d'attente moyen :</span> {{ $stats['average_wait_time'] ? round($stats['average_wait_time']/60, 1) . ' min' : 'N/A' }}</p>
    </div>
@endsection
