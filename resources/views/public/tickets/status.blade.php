@extends('layouts.public')

@section('content')
@livewire('public.realtime-ticket-status', ['queue' => $queue, 'ticket' => $ticket])
@endsection
