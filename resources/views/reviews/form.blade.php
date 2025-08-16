@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
            <h2 class="text-2xl font-semibold text-gray-800 mb-6">Donnez votre avis</h2>
            
            <form action="{{ route('reviews.submit', $review->token) }}" method="POST">
                @csrf
                
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="rating">
                        Notez votre expérience (1 à 5 étoiles)
                    </label>
                    <div class="flex items-center">
                        @for($i = 5; $i >= 1; $i--)
                            <input type="radio" id="star{{ $i }}" name="rating" value="{{ $i }}" class="hidden" required>
                            <label for="star{{ $i }}" class="text-3xl cursor-pointer text-gray-300 hover:text-yellow-400">
                                ★
                            </label>
                        @endfor
                    </div>
                    @error('rating')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="comment" class="block text-gray-700 text-sm font-bold mb-2">
                        Votre commentaire (optionnel)
                    </label>
                    <textarea name="comment" id="comment" rows="4" 
                        class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 mt-1 block w-full sm:text-sm border border-gray-300 rounded-md p-2"
                        placeholder="Dites-nous ce que vous avez pensé de notre service..."></textarea>
                    @error('comment')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-end">
                    <button type="submit" 
                            class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Envoyer l'avis
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Script pour gérer la sélection des étoiles
    document.querySelectorAll('input[type="radio"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const rating = this.value;
            // Mettre à jour l'affichage des étoiles
            document.querySelectorAll('label[for^="star"]').forEach((label, index) => {
                if (index < 5 - rating) {
                    label.classList.remove('text-yellow-400');
                    label.classList.add('text-gray-300');
                } else {
                    label.classList.remove('text-gray-300');
                    label.classList.add('text-yellow-400');
                }
            });
        });
    });
</script>
@endpush
@endsection
