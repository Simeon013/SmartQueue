<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $queue->name }} - Tableau de bord
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6" x-data="queueDashboard()">
                    <!-- Actions principales -->
                    <div class="mb-8">
                        <button @click="callNext" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition duration-200">
                            Appeler le suivant
                        </button>
                    </div>

                    <!-- Ticket actuel -->
                    <div class="mb-8" x-show="currentTicket">
                        <h3 class="text-lg font-semibold mb-4">Ticket actuel</h3>
                        <div class="bg-gray-50 p-6 rounded-lg">
                            <div class="text-4xl font-bold text-gray-900 mb-4" x-text="currentTicket.number"></div>
                            <div class="flex space-x-4">
                                <button @click="markPresent" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded transition duration-200">
                                    Présent
                                </button>
                                <button @click="skip" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded transition duration-200">
                                    Passer
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Liste des tickets en attente -->
                    <div>
                        <h3 class="text-lg font-semibold mb-4">Tickets en attente</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <template x-for="ticket in waitingTickets" :key="ticket.id">
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <div class="text-2xl font-bold text-gray-900" x-text="ticket.number"></div>
                                    <div class="text-sm text-gray-500" x-text="formatTime(ticket.created_at)"></div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function queueDashboard() {
            return {
                currentTicket: null,
                waitingTickets: [],
                queueId: {{ $queue->id }},

                init() {
                    this.loadTickets();
                    setInterval(() => this.loadTickets(), 5000);
                },

                loadTickets() {
                    fetch(`/agent/queues/${this.queueId}/tickets`)
                        .then(response => response.json())
                        .then(data => {
                            this.currentTicket = data.currentTicket;
                            this.waitingTickets = data.waitingTickets;
                        });
                },

                callNext() {
                    fetch(`/agent/queues/${this.queueId}/next`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Content-Type': 'application/json',
                        },
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.ticket) {
                            this.currentTicket = data.ticket;
                            this.loadTickets();
                        }
                    });
                },

                markPresent() {
                    if (!this.currentTicket) return;

                    fetch(`/agent/queues/${this.queueId}/tickets/${this.currentTicket.id}/present`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Content-Type': 'application/json',
                        },
                    })
                    .then(() => {
                        this.currentTicket = null;
                        this.loadTickets();
                    });
                },

                skip() {
                    if (!this.currentTicket) return;

                    fetch(`/agent/queues/${this.queueId}/tickets/${this.currentTicket.id}/skip`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Content-Type': 'application/json',
                        },
                    })
                    .then(() => {
                        this.currentTicket = null;
                        this.loadTickets();
                    });
                },

                formatTime(timestamp) {
                    return new Date(timestamp).toLocaleTimeString();
                }
            }
        }
    </script>
</x-app-layout>
