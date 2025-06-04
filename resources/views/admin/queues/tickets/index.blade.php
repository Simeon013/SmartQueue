@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Tickets - {{ $queue->name }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.queues.tickets.create', $queue) }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Nouveau Ticket
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Numéro</th>
                                    <th>Nom</th>
                                    <th>Position</th>
                                    <th>Statut</th>
                                    <th>Contact</th>
                                    <th>Créé le</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($tickets as $ticket)
                                    <tr>
                                        <td>{{ $ticket->number }}</td>
                                        <td>{{ $ticket->name }}</td>
                                        <td>
                                            @if($ticket->status === 'waiting')
                                                {{ $ticket->position }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ $ticket->status === 'waiting' ? 'warning' :
                                                ($ticket->status === 'called' ? 'info' :
                                                ($ticket->status === 'served' ? 'success' : 'secondary')) }}">
                                                {{ $ticket->status }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($ticket->email)
                                                <i class="fas fa-envelope"></i> {{ $ticket->email }}<br>
                                            @endif
                                            @if($ticket->phone)
                                                <i class="fas fa-phone"></i> {{ $ticket->phone }}
                                            @endif
                                        </td>
                                        <td>{{ $ticket->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-sm btn-info dropdown-toggle" data-toggle="dropdown">
                                                    Actions
                                                </button>
                                                <div class="dropdown-menu">
                                                    <a class="dropdown-item" href="{{ route('admin.queues.tickets.edit', [$queue, $ticket]) }}">
                                                        <i class="fas fa-edit"></i> Modifier
                                                    </a>
                                                    @if($ticket->status === 'waiting')
                                                        <form action="{{ route('admin.queues.tickets.update-status', [$queue, $ticket]) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            @method('PATCH')
                                                            <input type="hidden" name="status" value="called">
                                                            <button type="submit" class="dropdown-item">
                                                                <i class="fas fa-bell"></i> Appeler
                                                            </button>
                                                        </form>
                                                    @endif
                                                    @if($ticket->status === 'called')
                                                        <form action="{{ route('admin.queues.tickets.update-status', [$queue, $ticket]) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            @method('PATCH')
                                                            <input type="hidden" name="status" value="served">
                                                            <button type="submit" class="dropdown-item">
                                                                <i class="fas fa-check"></i> Servi
                                                            </button>
                                                        </form>
                                                        <form action="{{ route('admin.queues.tickets.update-status', [$queue, $ticket]) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            @method('PATCH')
                                                            <input type="hidden" name="status" value="skipped">
                                                            <button type="submit" class="dropdown-item">
                                                                <i class="fas fa-forward"></i> Passé
                                                            </button>
                                                        </form>
                                                    @endif
                                                    <div class="dropdown-divider"></div>
                                                    <form action="{{ route('admin.queues.tickets.destroy', [$queue, $ticket]) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce ticket ?')">
                                                            <i class="fas fa-trash"></i> Supprimer
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">Aucun ticket trouvé</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $tickets->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
