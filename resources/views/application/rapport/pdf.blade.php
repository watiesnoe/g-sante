<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; }
        td { border: 1px solid #ccc; padding: 3px 5px; }
        h4 { font-size: 12px; color: #0665d0; }
        .text-muted { color: #777; }
        .row:after { content: ''; display: block; clear: both; }
        .col-lg-6 { width: 49%; float: left; margin-right: 1%; }
    </style>
</head>
<body>
    @include('layouts.pdf_header', ['docNumber' => 'RAPPORT'])
    <h3 style="text-align:center">Rapport d'activité du {{ $rapport['du']->format('d/m/Y') }} au {{ $rapport['au']->format('d/m/Y') }}{{ $service ? ' — ' . $service->nom : '' }}</h3>
    @include('application.rapport._contenu')
</body>
</html>
