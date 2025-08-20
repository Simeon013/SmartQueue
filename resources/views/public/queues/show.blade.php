@extends('layouts.public')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-4xl mx-auto px-4 py-8 sm:px-6 lg:px-8">
        <!-- En-tête simplifié -->
        <div class="mb-6 text-center">
            <h1 class="text-2xl font-bold text-gray-900 mb-1">{{ $queue->name }}</h1>
            <div class="text-sm text-gray-500 mb-4">{{ $queue->establishment->name }}</div>
            
            @php
                $statusInfo = [
                    'open' => ['class' => 'bg-green-100 text-green-800', 'label' => 'Ouverte'],
                    'paused' => ['class' => 'bg-yellow-100 text-yellow-800', 'label' => 'En pause'],
                    'closed' => ['class' => 'bg-red-100 text-red-800', 'label' => 'Fermée']
                ][$queue->status->value] ?? ['class' => 'bg-gray-100 text-gray-800', 'label' => 'Inconnu'];
            @endphp
            
            <div class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium {{ $statusInfo['class'] }} mb-4">
                {{ $statusInfo['label'] }}
            </div>
        </div>

        <!-- Messages d'alerte -->
        @if(session('error'))
            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-r">
                <div class="flex items-start">
                    <i class="fas fa-exclamation-circle text-red-500 mt-0.5 mr-3"></i>
                    <p class="text-sm text-red-700">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        @if($queue->status->value === 'paused')
            <div class="mb-6 p-4 bg-yellow-50 border-l-4 border-yellow-500 rounded-r">
                <div class="flex items-start">
                    <i class="fas fa-info-circle text-yellow-500 mt-0.5 mr-3"></i>
                    <p class="text-sm text-yellow-700">Cette file d'attente est actuellement en pause.</p>
                </div>
            </div>
        @endif

        <!-- Carte principale - File d'attente -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden mb-6 border border-gray-100">
            <div class="p-6">
                <!-- En-tête avec icône -->
                <div class="flex items-center justify-center mb-6">
                    <div class="bg-blue-50 p-4 rounded-full">
                        <i class="fas fa-users text-3xl text-blue-500"></i>
                    </div>
                </div>
                
                <!-- Nombre de personnes en attente -->
                <div class="text-center mb-8">
                    <div class="text-5xl font-bold text-gray-900 mb-2">{{ $waitingTicketsCount }}</div>
                    <div class="text-gray-500">personne(s) en attente</div>
                </div>
                
                <!-- Bouton principal -->
                <form action="{{ route('public.queue.join', $queue) }}" method="POST" class="mb-4">
                    @csrf
                    <button type="submit" class="w-full py-4 px-6 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg text-lg transition duration-200 flex items-center justify-center">
                        <i class="fas fa-user-plus mr-2"></i> Rejoindre la file
                    </button>
                </form>
                
                <!-- Bouton secondaire -->
                <div class="text-center">
                    <a href="{{ route('public.queues.index') }}" class="inline-flex items-center text-blue-600 hover:text-blue-500 text-sm font-medium">
                        <i class="fas fa-arrow-left mr-1"></i> Retour à l'accueil
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Section Informations supplémentaires -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
            <!-- Carte Temps d'attente -->
            <div class="bg-white p-4 rounded-lg border border-gray-100 shadow-sm">
                <div class="flex items-center">
                    <div class="bg-blue-50 p-3 rounded-full mr-4">
                        <i class="far fa-clock text-blue-500"></i>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Temps d'attente</div>
                        <div class="font-medium">{{ $queue->estimated_wait_time ?? '--' }} minutes</div>
                    </div>
                </div>
            </div>
            
            <!-- Carte Type d'établissement -->
            <div class="bg-white p-4 rounded-lg border border-gray-100 shadow-sm">
                <div class="flex items-center">
                    <div class="bg-green-50 p-3 rounded-full mr-4">
                        <i class="fas fa-store text-green-500"></i>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Type d'établissement</div>
                        <div class="font-medium">{{ $queue->establishment->type }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
