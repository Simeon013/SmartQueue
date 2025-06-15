@extends('layouts.public')

@section('content')
    <div class="w-full sm:max-w-md px-6 py-4 mt-6 overflow-hidden sm:rounded-lg text-center">
        <h1 class="text-4xl font-bold text-blue-600 mb-2">VirtualQ</h1>
        <p class="text-gray-600">Bienvenue ! Choisissez une file d'attente.</p>
    </div>

    <div class="w-full sm:max-w-md mt-6 p-6 bg-white shadow-md overflow-hidden sm:rounded-lg text-center">
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Rejoindre une File</h2>

        <p class="text-gray-600 mb-6">
            Scannez le QR code de l'établissement ou saisissez le code ci-dessous.
        </p>

        <!-- Formulaire de saisie du code -->
        <form method="POST" action="{{ route('public.queue.find') }}" class="space-y-4">
            @csrf
            <div>
                <input type="text" name="queue_code" placeholder="Code de la file" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
            </div>
            <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Rejoindre la file
            </button>
        </form>

        <div class="mt-6 text-sm text-gray-500">
             Ceci est une simulation. Aucune donnée réelle n'est traitée.
        </div>
    </div>

    {{-- Section pour les files d'attente cliquables si nécessaire, ou un bouton QR --}}
    {{-- Pour l'instant, nous avons le formulaire et la note de simulation --}}

@endsection
