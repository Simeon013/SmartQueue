@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Modifier le Ticket #{{ $ticket->number }} - {{ $queue->name }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.queues.tickets.index', $queue) }}" class="btn btn-default">
                            <i class="fas fa-arrow-left"></i> Retour
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.queues.tickets.update', [$queue, $ticket]) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label for="name">Nom <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                id="name" name="name" value="{{ old('name', $ticket->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                id="email" name="email" value="{{ old('email', $ticket->email) }}">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="phone">Téléphone</label>
                            <input type="tel" class="form-control @error('phone') is-invalid @enderror"
                                id="phone" name="phone" value="{{ old('phone', $ticket->phone) }}">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="status">Statut <span class="text-danger">*</span></label>
                            <select class="form-control @error('status') is-invalid @enderror"
                                id="status" name="status" required>
                                <option value="waiting" {{ old('status', $ticket->status) === 'waiting' ? 'selected' : '' }}>
                                    En attente
                                </option>
                                <option value="called" {{ old('status', $ticket->status) === 'called' ? 'selected' : '' }}>
                                    Appelé
                                </option>
                                <option value="served" {{ old('status', $ticket->status) === 'served' ? 'selected' : '' }}>
                                    Servi
                                </option>
                                <option value="skipped" {{ old('status', $ticket->status) === 'skipped' ? 'selected' : '' }}>
                                    Passé
                                </option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="wants_notifications"
                                    name="wants_notifications" value="1" {{ old('wants_notifications', $ticket->wants_notifications) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="wants_notifications">
                                    Activer les notifications
                                </label>
                            </div>
                        </div>

                        <div class="form-group notification-channel" style="display: none;">
                            <label>Méthode de notification</label>
                            <div class="custom-control custom-radio">
                                <input type="radio" class="custom-control-input" id="notification_email"
                                    name="notification_channel" value="email" {{ old('notification_channel', $ticket->notification_channel) === 'email' ? 'checked' : '' }}>
                                <label class="custom-control-label" for="notification_email">Email</label>
                            </div>
                            <div class="custom-control custom-radio">
                                <input type="radio" class="custom-control-input" id="notification_sms"
                                    name="notification_channel" value="sms" {{ old('notification_channel', $ticket->notification_channel) === 'sms' ? 'checked' : '' }}>
                                <label class="custom-control-label" for="notification_sms">SMS</label>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Enregistrer les modifications
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const wantsNotifications = document.getElementById('wants_notifications');
    const notificationChannel = document.querySelector('.notification-channel');

    function toggleNotificationChannel() {
        notificationChannel.style.display = wantsNotifications.checked ? 'block' : 'none';
    }

    wantsNotifications.addEventListener('change', toggleNotificationChannel);
    toggleNotificationChannel();
});
</script>
@endpush
@endsection
