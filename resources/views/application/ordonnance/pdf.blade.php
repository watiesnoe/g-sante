<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ordonnance</title>
    <style>
        *{margin:5px;padding:0;font-family:DejaVu Sans,sans-serif}
        table{border-collapse:collapse;width:100%}
        th,td{border:1px solid #000;padding:5px}
    </style>
</head>
<body>
<table width="100%">
    <tr>
        <td>
            <h4>Ministère de la santé</h4>
            <p>Direction Régionale de la Santé</p>
            <h4>Cabinet Médical</h4>
            <p>Tel: (0023) 73 12 33 01</p>
        </td>
        <td align="right"><p>Date: {{ date('d/m/Y') }}</p></td>
    </tr>
</table>

<h3 align="center" style="text-decoration:underline">ORDONNANCE</h3>
<p><b>Nom et Prénom:</b> {{ $patient->nom_patient }} {{ $patient->prenom_patient }}
    <b>Age:</b> {{ $patient->age_patient }} <b>Sexe:</b> {{ $patient->genre }}</p>

<table width="100%">
    <tr>
        <th>TRAITEMENT</th>
        <th>PRIX</th>
    </tr>
    <tr>
        <td>
            @foreach($medicaments as $med)
                <b>{{ $med->nom }}</b><hr>{{ $med->pivot->posologie }}<br>
            @endforeach
        </td>
        <td>
            @foreach($medicaments as $med)
                @if(empty($med->pivot->statut_vente) || $med->pivot->statut_vente === 'non_disponible')
                    <p>-</p><br>
                @else
                    <p>{{ ($med->prix_vente ?? 0) * ($med->pivot->quantite_prescite ?? 1) }} CFA</p><br>
                @endif
            @endforeach
        </td>
    </tr>
    <tr>
        <td align="right"><b>MONTANT TOTAL</b></td>
        <td><b>{{ $totale }} CFA</b></td>
    </tr>
</table>

<p style="text-align:right;margin-top:50px"><b>PRESCRIPTEUR:</b> {{ $patient->nom_medecin }} {{ $patient->prenom_medecin }}</p>
</body>
</html>
