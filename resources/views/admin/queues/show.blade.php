@extends('layouts.admin')

@php
    use SimpleSoftwareIO\QrCode\Facades\QrCode;
@endphp

@section('header', "Détail de la file d'attente")

@section('content')
    <livewire:admin.queue-tickets :queue="$queue" />
@endsection
