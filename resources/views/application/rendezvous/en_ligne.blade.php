@extends('layouts.app')

@section('title_page', 'Demandes de rendez-vous en ligne')

@section('content')
    <div class="content">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
            <h2 class="h3 fw-bold mb-0"><i class="fa fa-globe me-2"></i>Demandes de rendez-vous en ligne</h2>
            <div class="d-flex gap-2">
                <form method="GET">
                    <select name="statut" class="form-select form-select-sm no-select2" onchange="this.form.submit()">
                        <option value="a_traiter" @selected($statut === 'a_traiter')>À traiter</option>
                        @foreach(\App\Models\DemandeEnLigne::STATUTS as $code => $libelle)
                            @continue(in_array($code, ['recue', 'conflit']))
                            <option value="{{ $code }}" @selected($statut === $code)>{{ $libelle }}</option>
                        @endforeach
                        <option value="toutes" @selected($statut === 'toutes')>Toutes</option>
                    </select>
                </form>
                @if($etablissement->connecteEnLigne())
                    <form method="POST" action="{{ route('en_ligne.synchroniser') }}">@csrf<button class="btn btn-sm btn-outline-primary"><i class="fa fa-sync me-1"></i>Synchroniser maintenant</button></form>
                @endif
            </div>
        </div>

        @if(!$etablissement->connecteEnLigne())
            @if(\App\Models\Etablissement::espacePatientDisponible())
                <div class="alert alert-info">L'espace patient n'est pas activé pour cet établissement : l'administrateur de la plateforme doit cocher le module « Espace patient en ligne ».</div>
            @else
                <div class="alert alert-info">L'établissement n'est pas relié à un serveur en ligne. Le réglage se fait dans Paramètres → Établissement → Serveur en ligne.</div>
            @endif
        @elseif($etablissement->espacePatientIntegre())
            <p class="small text-muted">
                Espace patient : <a href="{{ url('/patient') }}" target="_blank">{{ url('/patient') }}</a>
                @unless($etablissement->parametre('en_ligne.rendez_vous'))
                    — <span class="text-warning">vos créneaux ne sont pas encore publiés (règles de fonctionnement : « Publier les créneaux libres »)</span>
                @endunless
            </p>
        @elseif($journal)
            <p class="small {{ $journal->statut === 'ok' ? 'text-muted' : 'text-danger' }}">
                Dernière synchronisation : {{ $journal->debut->format('d/m/Y H:i') }} —
                {{ $journal->statut === 'ok' ? 'réussie' : 'échec (' . $journal->message . ') ; l\'activité interne continue normalement' }}.
            </p>
        @endif
        <p class="text-muted small">Une demande ne devient un rendez-vous qu'après votre confirmation. Le patient est prévenu par SMS de chaque décision.</p>

        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
        @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

        <div class="block block-rounded"><div class="block-content block-content-full">
            <table class="table table-bordered table-vcenter">
                <thead><tr><th>Créneau demandé</th><th>Patient</th><th>Médecin</th><th>Motif</th><th>Statut</th><th class="text-center">Actions</th></tr></thead>
                <tbody>
                    @forelse($demandes as $d)
                        <tr>
                            <td class="text-nowrap">{{ $d->creneau->format('d/m/Y H:i') }}<div class="small text-muted">reçue le {{ $d->created_at->format('d/m H:i') }}</div></td>
                            <td>{{ mb_strtoupper($d->patient_nom) }} {{ $d->patient_prenom }}<div class="small">{{ $d->patient_telephone }}</div>
                                @if($d->patient)<div class="small text-success">Dossier {{ $d->patient->numero_dossier }}</div>@endif</td>
                            <td>{{ $d->medecin->name ?? '— médecin introuvable' }}</td>
                            <td class="small">{{ $d->motif }}@if($d->remplace_uuid)<div><span class="badge bg-info">Changement d'horaire</span> le rendez-vous précédent est annulé</div>@endif</td>
                            <td>
                                <span class="badge bg-{{ \App\Models\DemandeEnLigne::COULEURS[$d->statut] ?? 'secondary' }}">{{ \App\Models\DemandeEnLigne::STATUTS[$d->statut] ?? $d->statut }}</span>
                                @if($d->creneau_propose)<div class="small">proposé : {{ $d->creneau_propose->format('d/m H:i') }}</div>@endif
                                @if($d->message)<div class="small text-muted">{{ $d->message }}</div>@endif
                            </td>
                            <td class="text-center">
                                @if(in_array($d->statut, ['recue', 'conflit']))
                                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="collapse" data-bs-target="#traiter-{{ $d->id }}">Traiter</button>
                                @endif
                            </td>
                        </tr>
                        @if(in_array($d->statut, ['recue', 'conflit']))
                            <tr class="collapse" id="traiter-{{ $d->id }}">
                                <td colspan="6" class="bg-body-light">
                                    <div class="row g-3">
                                        @if($d->statut === 'recue')
                                        <div class="col-lg-5">
                                            <form method="POST" action="{{ route('en_ligne.confirmer', $d) }}">
                                                @csrf
                                                <div class="fw-semibold mb-1">Confirmer — à quel dossier ?</div>
                                                @foreach($correspondances[$d->id] ?? [] as $p)
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="patient_id" value="{{ $p->id }}" id="p{{ $d->id }}-{{ $p->id }}">
                                                        <label class="form-check-label small" for="p{{ $d->id }}-{{ $p->id }}">{{ $p->numero_dossier }} — {{ mb_strtoupper($p->nom) }} {{ $p->prenom }}, {{ $p->telephone }}, {{ $p->age }} ans</label>
                                                    </div>
                                                @endforeach
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="patient_id" value="" id="p{{ $d->id }}-nouveau" @checked(($correspondances[$d->id] ?? collect())->isEmpty())>
                                                    <label class="form-check-label small" for="p{{ $d->id }}-nouveau">Nouveau dossier :</label>
                                                </div>
                                                <div class="d-flex gap-2 my-2">
                                                    <select name="genre" class="form-select form-select-sm no-select2" style="max-width:120px"><option value="">Sexe…</option><option value="F">Féminin</option><option value="M">Masculin</option></select>
                                                    <input type="number" name="age" class="form-control form-control-sm" min="0" max="130" placeholder="Âge" style="max-width:90px">
                                                </div>
                                                <button class="btn btn-sm btn-success">Confirmer le rendez-vous</button>
                                            </form>
                                        </div>
                                        @endif
                                        <div class="col-lg-4">
                                            <form method="POST" action="{{ route('en_ligne.proposer', $d) }}">
                                                @csrf
                                                <div class="fw-semibold mb-1">Proposer un autre créneau</div>
                                                <input type="datetime-local" name="creneau_propose" class="form-control form-control-sm mb-2" required>
                                                <input type="text" name="message" class="form-control form-control-sm mb-2" placeholder="Message au patient (facultatif)">
                                                <button class="btn btn-sm btn-info">Proposer</button>
                                            </form>
                                        </div>
                                        <div class="col-lg-3">
                                            <form method="POST" action="{{ route('en_ligne.refuser', $d) }}">
                                                @csrf
                                                <div class="fw-semibold mb-1">Refuser</div>
                                                <input type="text" name="message" class="form-control form-control-sm mb-2" placeholder="Motif communiqué au patient" required>
                                                <button class="btn btn-sm btn-outline-danger">Refuser</button>
                                            </form>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr><td colspan="6" class="text-muted">Aucune demande.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div></div>
    </div>
@endsection
