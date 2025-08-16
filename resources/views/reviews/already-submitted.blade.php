@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100">
                <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h2 class="mt-3 text-xl font-medium text-gray-900">Avis déjà soumis</h2>
            <p class="mt-2 text-sm text-gray-500">
                Vous avez déjà soumis un avis pour ce ticket. Nous vous remercions pour votre retour !
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
