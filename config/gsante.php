<?php

return [
    /*
    | Rôle de cette installation (cahier des charges, section 9.2) :
    |  - local    : serveur d'un établissement, toute l'activité interne, sans dépendance à Internet ;
    |  - en_ligne : point d'accès Internet (espace patient, échanges avec les serveurs locaux).
    |    Il ne détient que les données utiles aux patients, jamais le dossier médical.
    */
    'mode' => env('GSANTE_MODE', 'local'),

    /*
    | Espace patient intégré (mode local uniquement) : cette installation sert aussi
    | l'espace patient, à l'adresse /patient, sans serveur en ligne séparé. Chaque
    | établissement l'active avec le module « Espace patient en ligne ».
    */
    'espace_patient' => (bool) env('GSANTE_ESPACE_PATIENT', false),

    // Nom de l'espace patient : écran d'accueil du téléphone, titre des pages, SMS
    'nom_espace_patient' => env('GSANTE_NOM_ESPACE_PATIENT', 'Kènèya Hub'),

    /*
    | Application Android (APK / Play Store) qui ouvre l'espace patient en plein écran
    | (Trusted Web Activity). Valeurs données par PWABuilder ou Bubblewrap ; servies
    | dans /.well-known/assetlinks.json. Plusieurs empreintes : séparées par des virgules.
    */
    'android' => [
        'paquet'    => env('ANDROID_PAQUET'),
        'empreintes' => array_filter(array_map('trim', explode(',', (string) env('ANDROID_EMPREINTES_SHA256', '')))),
    ],

    'synchro' => [
        // Écart toléré entre les horloges des deux serveurs (secondes)
        'tolerance_horodatage' => (int) env('GSANTE_SYNCHRO_TOLERANCE', 300),
        // Délai d'attente d'une requête de synchronisation (secondes)
        'delai' => (int) env('GSANTE_SYNCHRO_DELAI', 20),
        // Nombre maximal d'événements échangés par requête
        'lot' => 200,
    ],
];
