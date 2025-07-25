<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Paramètres des tickets
    |--------------------------------------------------------------------------
    |
    | Ce fichier contient les paramètres de configuration pour la génération
    | et la gestion des tickets.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Nombre de files par cycle
    |--------------------------------------------------------------------------
    |
    | Nombre maximum de files qui peuvent être gérées avant de réutiliser
    | les préfixes de code de ticket.
    |
    */
    'queues_per_cycle' => env('TICKETS_QUEUES_PER_CYCLE', 1000),

    /*
    |--------------------------------------------------------------------------
    | Format du code de ticket
    |--------------------------------------------------------------------------
    |
    | Format utilisé pour générer les codes de ticket.
    | %s sera remplacé par le préfixe (A, B, ..., Z, AA, AB, ...)
    | %03d sera remplacé par le numéro du ticket (001, 002, ..., 999)
    |
    */
    'ticket_code_format' => '%s-%03d',
];
