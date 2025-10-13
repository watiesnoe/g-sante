@extends('layouts.app')

@section('title_page', 'Laboratoire et imagerie')

@section('content')
    <div class="content">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h2 class="h3 fw-bold mb-0"><i class="fa fa-flask me-2"></i>Laboratoire et imagerie</h2>
            <form method="GET" class="d-flex gap-2">
                <select name="type" class="form-select form-select-sm no-select2" onchange="this.form.submit()">
                    <option value="">Labo + imagerie</option>
                    <option value="laboratoire" {{ request('type') === 'laboratoire' ? 'selected' : '' }}>Laboratoire</option>
                    <option value="imagerie" {{ request('type') === 'imagerie' ? 'selected' : '' }}>Imagerie</option>
                </select>
                @can('consultations.create')
                    <select name="mes" class="form-select form-select-sm no-select2" onchange="this.form.submit()">
                        <option value="">Tous les prescripteurs</option>
                        <option value="1" {{ request()->boolean('mes') ? 'selected' : '' }}>Mes prescriptions</option>
                    </select>
                @endcan
                <select name="statut" class="form-select form-select-sm no-select2" onchange="this.form.submit()">
                    <option value="en_cours" {{ $statut === 'en_cours' ? 'selected' : '' }}>À traiter</option>
                    @foreach(\App\Models\PrescriptionExamen::STATUTS as $code => $libelle)
                        <option value="{{ $code }}" {{ $statut === $code ? 'selected' : '' }}>{{ $libelle }}</option>
                    @endforeach
                </select>
            </form>
        </div>
        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

        <div class="block block-rounded"><div class="block-content block-content-full">
            <table class="table table-sm table-bordered table-striped table-vcenter">
                <thead><tr><th>Demandé le</th><th>Patient</th><th>Examen</th><th>Prescripteur</th><th>Statut</th><th>Résultat</th><th class="text-center">Actions</th></tr></thead>
                <tbody>
                    @forelse($examens as $e)
                        <tr>
                            <td class="text-nowrap">{{ $e->created_at->format('d/m H:i') }}</td>
                            <td>{{ mb_strtoupper($e->consultation->patient->nom ?? '') }} {{ $e->consultation->patient->prenom ?? '' }}</td>
                            <td>{{ $e->examen }} <span class="badge bg-light text-dark">{{ $e->type === 'imagerie' ? 'imagerie' : 'labo' }}</span></td>
                            <td>{{ $e->consultation->medecin->name ?? '-' }}</td>
                            <td>
                                <span class="badge bg-{{ \App\Models\PrescriptionExamen::COULEURS[$e->statut] ?? 'secondary' }}">{{ \App\Models\PrescriptionExamen::STATUTS[$e->statut] ?? $e->statut }}</span>
                                @if($e->valide_at)<div class="small text-muted">par {{ $e->validateur->name ?? '-' }}</div>@endif
                            </td>
                            <td>
                                @if($e->resultat)
                                    @if($e->resultat->valeur !== null)
                                        <span class="{{ $e->resultat->anormal ? 'text-danger fw-bold' : '' }}">{{ $e->resultat->valeur }} {{ $e->resultat->unite }}</span>
                                        @if($e->resultat->valeur_min !== null || $e->resultat->valeur_max !== null)<small class="text-muted">({{ $e->resultat->valeur_min + 0 }}–{{ $e->resultat->valeur_max + 0 }})</small>@endif
                                        @if($e->resultat->anormal)<span class="badge bg-danger">anormal</span>@endif
                                    @endif
                                    <div class="small">{{ \Illuminate\Support\Str::limit($e->resultat->resultat, 60) }}</div>
                                    @if($e->resultat->fichier)
                                        <a href="{{ route('reponses.fichier', $e->resultat) }}" target="_blank" class="small"><i class="fa fa-paperclip me-1"></i>Voir le fichier</a>
                                    @endif
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="d-flex flex-wrap gap-1 justify-content-center">
                                    @if($e->consultation)
                                        <a href="{{ route('prescriptions.pdf', $e->consultation->uuid) }}" target="_blank" class="btn btn-sm btn-outline-secondary" title="Bon d'examens de la consultation"><i class="fa fa-print"></i></a>
                                    @endif
                                    @can('examens.results')
                                        @foreach($suivants[$e->id] as $vers)
                                            @if($vers !== 'annule')
                                                <form method="POST" action="{{ route('laboratoire.etape', $e) }}">@csrf<input type="hidden" name="vers" value="{{ $vers }}"><button class="btn btn-sm btn-outline-primary">{{ \App\Models\PrescriptionExamen::STATUTS[$vers] }}</button></form>
                                            @endif
                                        @endforeach
                                        @if(!in_array($e->statut, ['saisi', 'valide', 'annule']))
                                            <a href="{{ route('reponse.create', $e->id) }}" class="btn btn-sm btn-primary">Saisir</a>
                                        @endif
                                        @if(in_array('annule', $suivants[$e->id]))
                                            <form method="POST" action="{{ route('laboratoire.etape', $e) }}" class="form-annuler-examen">@csrf<input type="hidden" name="vers" value="annule"><input type="hidden" name="motif"><button class="btn btn-sm btn-outline-dark">Annuler</button></form>
                                        @endif
                                    @endcan
                                    @if($e->statut === 'saisi' && auth()->user()->can('examens.valider'))
                                        <form method="POST" action="{{ route('laboratoire.valider', $e) }}">@csrf<button class="btn btn-sm btn-success">Valider</button></form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-muted">Aucun examen.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div></div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).on('submit', '.form-annuler-examen', function (e) {
            if (this.motif.value) return true;
            e.preventDefault();
            const motif = prompt('Motif de l\'annulation (obligatoire) :');
            if (motif && motif.trim()) { this.motif.value = motif.trim(); this.submit(); }
        });
    </script>
@endsection
