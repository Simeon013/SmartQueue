@extends('layouts.admin')

@section('header', 'Statistiques des Avis Clients')

@section('content')
<div class="overflow-hidden bg-white shadow sm:rounded-lg">
    <div class="flex justify-between items-center px-4 py-5 sm:px-6">
        <div>
            <h3 class="text-lg font-medium leading-6 text-gray-900">
                Statistiques des avis clients
            </h3>
            <p class="mt-1 max-w-2xl text-sm text-gray-500">
                Analyse des retours clients pour améliorer la qualité de service.
            </p>
        </div>
        <a href="{{ url()->previous() }}" 
           class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white rounded-md border border-gray-300 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            Retour à la liste
        </a>
    </div>
    
    <div class="px-4 py-5 border-t border-gray-200 sm:px-6">
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
            <!-- Note moyenne -->
            <div class="overflow-hidden bg-white rounded-lg shadow">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 p-3 bg-indigo-500 rounded-md">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
            <div class="overflow-hidden bg-white rounded-lg shadow sm:col-span-2">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="mb-4 text-base font-medium text-gray-900">Répartition des notes</h3>
                    <div class="space-y-4">
                        @for($i = 5; $i >= 1; $i--)
                            @php
                                $count = $stats['ratings_distribution'][$i] ?? 0;
                                $percentage = $stats['total_reviews'] > 0 ? ($count / $stats['total_reviews']) * 100 : 0;
                            @endphp
                            <div class="flex items-center">
                                <div class="w-8 text-sm font-medium text-gray-900">{{ $i }} étoile{{ $i > 1 ? 's' : '' }}</div>
                                <div class="mx-4 w-full">
                                    <div class="w-full h-2.5 bg-gray-200 rounded-full">
                                        <div class="h-2.5 bg-yellow-400 rounded-full" style="width: {{ $percentage }}%"></div>
                                    </div>
                                </div>
                                <div class="w-8 text-sm text-right text-gray-500">
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
            <h3 class="mb-4 text-base font-medium text-gray-900">Derniers avis</h3>
            <div class="overflow-hidden bg-white shadow sm:rounded-md">
                <ul class="divide-y divide-gray-200">
                    @forelse(\App\Models\Review::with('ticket')
                        ->whereNotNull('submitted_at')
                        ->latest('submitted_at')
                        ->take(5)
                        ->get() as $review)
                        <li class="hover:bg-gray-50">
                            <div class="px-4 py-4 sm:px-6">
                                <div class="flex justify-between items-center">
                                    <p class="text-sm font-medium text-indigo-600">
                                        Ticket #{{ $review->ticket->code_ticket }}
                                    </p>
                                    <div class="flex flex-shrink-0 ml-2">
                                        @for($i = 0; $i < 5; $i++)
                                            @if($i < $review->rating)
                                                <svg class="w-5 h-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                </svg>
                                            @else
                                                <svg class="w-5 h-5 text-gray-300" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
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
                        <li class="px-4 py-4 text-center text-gray-500 sm:px-6">
                            Aucun avis n'a encore été soumis.
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
