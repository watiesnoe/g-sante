<?php

/*
 * Passerelle SMS (EF-03.05, EF-14.05). Le choix de l'opérateur reste à valider
 * (cahier des charges, section 18) : par défaut, les SMS sont seulement écrits
 * dans le journal. Pour une passerelle HTTP générique, renseigner l'URL et le jeton.
 */
return [
    'pilote' => env('SMS_PILOTE', 'journal'), // journal | http

    'http' => [
        'url'        => env('SMS_HTTP_URL'),
        'jeton'      => env('SMS_HTTP_JETON'),
        'expediteur' => env('SMS_EXPEDITEUR', 'G-SANTE'),
    ],
];
