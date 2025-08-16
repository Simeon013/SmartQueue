@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100">
                <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <h2 class="mt-3 text-xl font-medium text-gray-900">Merci pour votre avis !</h2>
            <p class="mt-2 text-sm text-gray-500">
                Votre avis a été enregistré avec succès. Nous apprécions votre retour et l'utiliserons pour améliorer nos services.
            </p>
            <div class="mt-6">
                <a href="{{ url('/') }}" class="text-indigo-600 hover:text-indigo-500">
                    &larr; Retour à l'accueil
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
