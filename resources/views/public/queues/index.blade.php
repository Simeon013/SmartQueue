@extends('layouts.public')

@section('content')
    <div class="text-center mb-8 sm:mb-12 px-4">
        <h1 class="text-4xl sm:text-5xl font-bold text-primary mb-2 sm:mb-3">{{ config('app.name', 'VirtualQ') }}</h1>
        <p class="text-lg sm:text-xl text-secondary">Votre solution d'attente intelligente</p>
    </div>

    <div class="public-card max-w-md mx-4 sm:mx-auto p-6 sm:p-8 text-center">
        <div class="mb-8">
            <i class="fas fa-qrcode text-5xl text-blue-500 mb-4"></i>
            <h2 class="text-2xl font-semibold text-primary mb-3">Rejoindre une file d'attente</h2>
            <p class="text-secondary mb-6">
                Scannez le QR code de la file d'attente ou entrez son code ci-dessous
            </p>
        </div>

        <!-- Formulaire de saisie du code -->
        <form method="POST" action="{{ route('public.queue.find') }}" class="space-y-6">
            @csrf
            <div class="space-y-2">
                <input type="text"
                       name="queue_code"
                       placeholder="Code de la file (ex: A1B2C3)"
                       class="public-input w-full text-base sm:text-md"
                       required
                       autofocus
                       inputmode="text"
                       autocapitalize="characters"
                       autocomplete="off">
                @error('queue_code')
                    <p class="text-red-500 text-sm mt-1 text-left">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="btn-primary w-full">
                <i class="fas fa-sign-in-alt mr-2"></i>Rejoindre la file
            </button>
        </form>

        <div class="mt-8 pt-6 border-t border-gray-100">
            <p class="text-sm text-gray-500">
                Vous êtes un agent ?
                <a href="{{ route('login') }}" class="text-blue-600 hover:underline">Connectez-vous ici</a>
            </p>
        </div>
    </div>
@endsection
