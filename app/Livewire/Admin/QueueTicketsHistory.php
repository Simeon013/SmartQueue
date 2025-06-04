<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Queue;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('layouts.admin')]
class QueueTicketsHistory extends Component
{
    use WithPagination;

    public $queue;
    public $status = '';
    public $date;

    public function mount(Queue $queue)
    {
        $this->queue = $queue;
        $this->date = now()->format('Y-m-d');
    }

    public function render()
    {
        $query = $this->queue->tickets()
            ->whereIn('status', ['served', 'skipped'])
            ->when($this->status, function ($query) {
                return $query->where('status', $this->status);
            })
            ->when($this->date, function ($query) {
                return $query->whereDate('updated_at', $this->date);
            })
            ->orderBy('updated_at', 'desc');

        return view('admin.queues.tickets.history', [
            'tickets' => $query->paginate(10)
        ]);
    }
}