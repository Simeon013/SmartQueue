<?php

namespace App\View\Components\Livewire\Public;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class TicketStatusHeader extends Component
{
    /**
     * The ticket instance.
     *
     * @var mixed
     */
    public $ticket;

    /**
     * The ticket status.
     *
     * @var string
     */
    public $ticketStatus;

    /**
     * The status information.
     *
     * @var array
     */
    public $statusInfo;

    /**
     * Create a new component instance.
     *
     * @param  mixed  $ticket
     * @param  string  $ticketStatus
     * @param  array  $statusInfo
     * @return void
     */
    public function __construct($ticket, $ticketStatus, $statusInfo)
    {
        $this->ticket = $ticket;
        $this->ticketStatus = $ticketStatus;
        $this->statusInfo = $statusInfo;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return function (array $data) {
            return 'livewire.public.ticket-status-header';
        };
    }
}
