@extends('layouts.public')

@section('content')
    <div class="w-full px-6 py-4 mt-6 overflow-hidden text-center sm:max-w-md sm:rounded-lg">
        <h1 class="mb-2 text-4xl font-bold text-blue-600">VirtualQ</h1>
        <p class="text-gray-600">Bienvenue !</p>
    </div>

    <div class="w-full p-6 mt-6 overflow-hidden text-center bg-white shadow-md sm:max-w-md sm:rounded-lg">
        <h2 class="mb-4 text-2xl font-semibold text-gray-800">Rejoindre une file d'attente</h2>

        <p class="mb-6 text-gray-600">
            Veuillez scanner un QRCode de file afin de rejoindre une file d'attente
        </p>

        <!-- Formulaire de saisie du code -->
        {{-- <form method="POST" action="{{ route('public.queue.find') }}" class="space-y-4">
            @csrf
            <div>
                <input type="text" name="queue_code" placeholder="Code de la file" class="block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
            </div>
            <button type="submit" class="inline-flex items-center justify-center w-full px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Rejoindre la file
            </button>
        </form>

        <div class="mt-6 text-sm text-gray-500">
             Ceci est une simulation. Aucune donnée réelle n'est traitée.
        </div> --}}
    </div>

    {{-- Section pour les files d'attente cliquables si nécessaire, ou un bouton QR --}}
    {{-- Pour l'instant, nous avons le formulaire et la note de simulation --}}

@endsection
