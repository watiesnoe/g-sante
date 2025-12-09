<!DOCTYPE html>
<html>
<head>
    <title>Détail Consultation #{{ $consultation->id }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 14px; }
        h2 { text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table, th, td { border: 1px solid #000; padding: 8px; }
        th { background-color: #f0f0f0; }
    </style>
</head>
<body>
<h2>Détail Consultation</h2>

<h3>Patient</h3>
<p>Nom : {{ $consultation->patient->nom }} {{ $consultation->patient->prenom }}</p>
<p>Téléphone : {{ $consultation->patient->telephone }}</p>
<p>Adresse : {{ $consultation->adresse_patient }}</p>
<p>Ticket : {{ $consultation->ticket_id }}</p>

<h3>Consultation</h3>
<p>Medecin : {{ $consultation->medecin->name }}</p>
<p>Date : {{ $consultation->date_consultation ?? now()->format('d/m/Y') }}</p>
<p>Motif : {{ $consultation->motif }}</p>
<p>Diagnostic : {{ $consultation->diagnostic }}</p>
<p>Notes / Antécédents : {{ $consultation->notes }}</p>

<h3>Constantes</h3>
<p>Poids : {{ $consultation->poids }} kg</p>
<p>Taille : {{ $consultation->taille }} cm</p>
<p>IMC : {{ $consultation->taille > 0 ? number_format($consultation->poids/(($consultation->taille/100)**2),2) : '-' }}</p>
<p>Tension : {{ $consultation->tension }}</p>
<p>Groupe sanguin : {{ $consultation->groupe_sanguin }}</p>

<h3>Symptômes</h3>
<ul>
    @foreach($consultation->symptomes as $symptome)
        <li>{{ $symptome->nom }}</li>
    @endforeach
</ul>

<h3>Maladie</h3>
<ul>
    @foreach($consultation->maladies as $maladie)
        <li>{{ $maladie->nom }}</li>
    @endforeach
</ul>

<h3>Ordonnances / Médicaments</h3>
<table>
    <thead>
    <tr>
        <th>Médicament</th>
        <th>Posologie</th>
        <th>Durée (jours)</th>
        <th>Quantité</th>
    </tr>
    </thead>
    <tbody>
    @foreach($consultation->ordonnances as $ordonnance)
        @foreach($ordonnance->medicaments as $med)
            <tr>
                <td>{{ $med->nom }}</td>
                <td>{{ $med->pivot->posologie }}</td>
                <td>{{ $med->pivot->duree_jours }}</td>
                <td>{{ $med->pivot->quantite }}</td>
            </tr>
        @endforeach
    @endforeach
    </tbody>
</table>

<h3>Examens</h3>
<ul>
    @foreach($consultation->examens as $ex)
        <li>{{ $ex->examen }}</li>
    @endforeach
</ul>

<h3>Rendez-vous</h3>
<ul>
    @foreach($consultation->rendezVous as $r)
        <li>{{ \Carbon\Carbon::parse($r->date_heure)->format('d/m/Y H:i') }} - {{ $r->motif }}</li>
    @endforeach
</ul>

<h3>Certificat</h3>
<p>{{ $consultation->certificat->contenu ?? '-' }}</p>

<h3>Hospitalisation</h3>
@if($consultation->hospitalisation)
    <p>Date entrée : {{ $consultation->hospitalisation->date_entree }}</p>
    <p>Salle : {{ $consultation->hospitalisation->salles_id }}</p>
    <p>Lit : {{ $consultation->hospitalisation->lit_id }}</p>
    <p>Observations : {{ $consultation->hospitalisation->observations }}</p>
@else
    <p>Aucune hospitalisation</p>
@endif

<script>
    window.onload = function() { window.print(); };
</script>
</body>
</html>
