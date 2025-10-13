<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>{{ $document->titre }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; margin: 20px 30px; }
        h2 { text-align: center; text-transform: uppercase; letter-spacing: 1px; margin: 10px 0 25px; }
        .contenu { line-height: 1.7; white-space: pre-wrap; }
        .pied { margin-top: 50px; text-align: right; }
    </style>
</head>
<body>
    @include('layouts.pdf_header', ['docNumber' => $document->patient->numero_dossier])
    <h2>{{ $document->titre }}</h2>
    @if($document->destinataire)<p><strong>À l'attention de :</strong> {{ $document->destinataire }}</p>@endif
    <div class="contenu">{{ $document->contenu }}</div>
    <div class="pied">
        <p>Signature et cachet</p>
        <p><strong>{{ $document->user->name ?? '' }}</strong></p>
    </div>
</body>
</html>
