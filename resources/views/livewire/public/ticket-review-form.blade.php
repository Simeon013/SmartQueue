@props(['ticket', 'showReviewForm', 'rating', 'comment', 'reviewSubmitted'])

@if($showReviewForm)
    <div class="mt-8 bg-blue-50 p-6 rounded-lg border border-blue-200">
        <h3 class="text-lg font-medium text-blue-800 mb-4">
            <i class="fas fa-star mr-2"></i>
            Donnez votre avis sur le service
        </h3>
        
        @if($reviewSubmitted)
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">
                    <i class="fas fa-check-circle mr-2"></i>
                    Merci pour votre avis ! Votre évaluation a été enregistrée avec succès.
                </span>
            </div>
        @else
            <form wire:submit.prevent="submitReview">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Note
                    </label>
                    <div class="flex items-center">
                        @for($i = 1; $i <= 5; $i++)
                            <button 
                                type="button"
                                wire:click="$set('rating', {{ $i }})"
                                class="text-2xl focus:outline-none {{ $i <= $rating ? 'text-yellow-400' : 'text-gray-300' }}"
                            >
                                ★
                            </button>
                        @endfor
                        @error('rating')
                            <span class="ml-2 text-sm text-red-600">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="comment" class="block text-sm font-medium text-gray-700 mb-2">
                        Commentaire (optionnel)
                    </label>
                    <textarea
                        id="comment"
                        wire:model="comment"
                        rows="3"
                        class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                        placeholder="Votre expérience avec notre service..."
                    ></textarea>
                    @error('comment')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="flex justify-end">
                    <button
                        type="submit"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        <i class="fas fa-paper-plane mr-2"></i>
                        Envoyer l'avis
                    </button>
                </div>
            </form>
        @endif
    </div>
@endif
