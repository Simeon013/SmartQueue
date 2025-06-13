@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Prendre un ticket</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('tickets.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="establishment_id" class="form-label">Établissement</label>
                            <select class="form-select @error('establishment_id') is-invalid @enderror" id="establishment_id" name="establishment_id" required>
                                <option value="">Sélectionnez un établissement</option>
                                @foreach($establishments as $establishment)
                                    <option value="{{ $establishment->id }}" {{ old('establishment_id') == $establishment->id ? 'selected' : '' }}>
                                        {{ $establishment->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('establishment_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="queue_id" class="form-label">File d'attente</label>
                            <select class="form-select @error('queue_id') is-invalid @enderror" id="queue_id" name="queue_id" required>
                                <option value="">Sélectionnez une file d'attente</option>
                                @foreach($queues as $queue)
                                    <option value="{{ $queue->id }}" {{ old('queue_id') == $queue->id ? 'selected' : '' }}>
                                        {{ $queue->name }} (Temps d'attente estimé: {{ $queue->estimated_wait_time }} minutes)
                                    </option>
                                @endforeach
                            </select>
                            @error('queue_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('home') }}" class="btn btn-secondary">Annuler</a>
                            <button type="submit" class="btn btn-primary">Prendre un ticket</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('establishment_id').addEventListener('change', function() {
    const establishmentId = this.value;
    const queueSelect = document.getElementById('queue_id');

    // Réinitialiser la liste des files d'attente
    queueSelect.innerHTML = '<option value="">Sélectionnez une file d\'attente</option>';

    if (establishmentId) {
        // Charger les files d'attente de l'établissement sélectionné
        fetch(`/api/establishments/${establishmentId}/queues`)
            .then(response => response.json())
            .then(queues => {
                queues.forEach(queue => {
                    const option = document.createElement('option');
                    option.value = queue.id;
                    option.textContent = `${queue.name} (Temps d'attente estimé: ${queue.estimated_wait_time} minutes)`;
                    queueSelect.appendChild(option);
                });
            });
    }
});
</script>
@endpush
@endsection
