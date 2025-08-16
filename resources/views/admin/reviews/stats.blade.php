@extends('layouts.admin')

@section('header', 'Statistiques des Avis Clients')

@section('content')
<div class="bg-white shadow overflow-hidden sm:rounded-lg">
    <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
        <div>
            <h3 class="text-lg leading-6 font-medium text-gray-900">
                Statistiques des avis clients
            </h3>
            <p class="mt-1 max-w-2xl text-sm text-gray-500">
                Analyse des retours clients pour améliorer la qualité de service.
            </p>
        </div>
        <a href="{{ route('admin.reviews.index') }}" 
           class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            Retour à la liste
        </a>
    </div>
    
    <div class="border-t border-gray-200 px-4 py-5 sm:px-6">
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
            <!-- Note moyenne -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-indigo-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                            </svg>
                        </div>
                        <div class="ml-5">
                            <p class="text-sm font-medium text-gray-500 truncate">Note moyenne</p>
                            <div class="flex items-baseline">
                                <p class="text-2xl font-semibold text-gray-900">{{ number_format($stats['average_rating'], 1) }}/5</p>
                                <p class="ml-2 text-sm text-gray-500">sur {{ $stats['total_reviews'] }} avis</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Répartition des notes -->
            <div class="bg-white overflow-hidden shadow rounded-lg sm:col-span-2">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-base font-medium text-gray-900 mb-4">Répartition des notes</h3>
                    <div class="space-y-4">
                        @for($i = 5; $i >= 1; $i--)
                            @php
                                $count = $stats['ratings_distribution'][$i] ?? 0;
                                $percentage = $stats['total_reviews'] > 0 ? ($count / $stats['total_reviews']) * 100 : 0;
                            @endphp
                            <div class="flex items-center">
                                <div class="w-8 text-sm font-medium text-gray-900">{{ $i }} étoile{{ $i > 1 ? 's' : '' }}</div>
                                <div class="w-full mx-4">
                                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                                        <div class="bg-yellow-400 h-2.5 rounded-full" style="width: {{ $percentage }}%"></div>
                                    </div>
                                </div>
                                <div class="w-8 text-right text-sm text-gray-500">
                                    {{ $count }}
                                </div>
                            </div>
                        @endfor
                    </div>
                </div>
            </div>
        </div>

        <!-- Derniers avis -->
        <div class="mt-8">
            <h3 class="text-base font-medium text-gray-900 mb-4">Derniers avis</h3>
            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                <ul class="divide-y divide-gray-200">
                    @forelse(\App\Models\Review::with('ticket')
                        ->whereNotNull('submitted_at')
                        ->latest('submitted_at')
                        ->take(5)
                        ->get() as $review)
                        <li class="hover:bg-gray-50">
                            <div class="px-4 py-4 sm:px-6">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-medium text-indigo-600">
                                        Ticket #{{ $review->ticket->code_ticket }}
                                    </p>
                                    <div class="ml-2 flex-shrink-0 flex">
                                        @for($i = 0; $i < 5; $i++)
                                            @if($i < $review->rating)
                                                <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                </svg>
                                            @else
                                                <svg class="h-5 w-5 text-gray-300" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                </svg>
                                            @endif
                                        @endfor
                                    </div>
                                </div>
                                @if($review->comment)
                                    <div class="mt-2 text-sm text-gray-600">
                                        "{{ $review->comment }}"
                                    </div>
                                @endif
                                <div class="mt-2 text-sm text-gray-500">
                                    Soumis le {{ $review->submitted_at->format('d/m/Y à H:i') }}
                                </div>
                            </div>
                        </li>
                    @empty
                        <li class="px-4 py-4 sm:px-6 text-center text-gray-500">
                            Aucun avis n'a encore été soumis.
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
