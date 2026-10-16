<?php

return [
    /*
    | Rôle de cette installation (cahier des charges, section 9.2) :
    |  - local    : serveur d'un établissement, toute l'activité interne, sans dépendance à Internet ;
    |  - en_ligne : point d'accès Internet (espace patient, échanges avec les serveurs locaux).
    |    Il ne détient que les données utiles aux patients, jamais le dossier médical.
    */
    'mode' => env('GSANTE_MODE', 'local'),

    'synchro' => [
        // Écart toléré entre les horloges des deux serveurs (secondes)
        'tolerance_horodatage' => (int) env('GSANTE_SYNCHRO_TOLERANCE', 300),
        // Délai d'attente d'une requête de synchronisation (secondes)
        'delai' => (int) env('GSANTE_SYNCHRO_DELAI', 20),
        // Nombre maximal d'événements échangés par requête
        'lot' => 200,
    ],
];
