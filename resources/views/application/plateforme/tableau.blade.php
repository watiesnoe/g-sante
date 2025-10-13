@extends('layouts.admin')

@section('title_page', 'Administration')

@section('content')
    <div class="content">
        <div class="row">
        @include('application.plateforme._menu')
        <div class="col-xl-9 col-lg-8">
            <h2 class="h3 fw-bold mb-1"><i class="fa fa-chart-line me-2"></i>Tableau de bord</h2>
            <div class="text-muted small mb-3">Plateforme G-SANTÉ — établissements de santé abonnés</div>

            <div class="row g-3 mb-3">
                @foreach([
                    ['Établissements', $etablissements->count(), 'fa-hospital', ''],
                    ['Abonnements à jour', $abonnements->where('statut', 'actif')->count(), 'fa-check-circle', 'text-success'],
                    ['Échéances sous 30 j', $echeances->count(), 'fa-calendar-alt', 'text-warning'],
                    ['En retard', $retards->count(), 'fa-exclamation-triangle', 'text-danger'],
                    ['Espaces suspendus', $etablissements->where('actif', false)->count(), 'fa-pause-circle', 'text-muted'],
                    ['Propriétaires', $proprietaires, 'fa-user-tie', ''],
                ] as [$libelle, $valeur, $icone, $couleur])
                    <div class="col-6 col-md-4"><div class="block block-rounded mb-0"><div class="block-content py-3 d-flex align-items-center gap-3">
                        <i class="fa {{ $icone }} fa-2x {{ $couleur ?: 'text-primary' }} opacity-75"></i>
                        <div><div class="fs-3 fw-bold {{ $couleur }}">{{ $valeur }}</div><div class="small text-muted">{{ $libelle }}</div></div>
                    </div></div></div>
                @endforeach
            </div>
            @if($jamaisConnectes)<div class="alert alert-info py-2 small"><i class="fa fa-info-circle me-1"></i>{{ $jamaisConnectes }} propriétaire(s) ne se sont pas encore connectés (mot de passe provisoire non changé).</div>@endif

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="block block-rounded h-100 mb-0">
                        <div class="block-header block-header-default"><h3 class="block-title">Recettes des abonnements (XOF)</h3></div>
                        <div class="block-content pb-3">
                            @php $max = max(1, $mois->max()); @endphp
                            @foreach($mois as $cle => $montant)
                                <div class="d-flex align-items-center gap-2 mb-2 small">
                                    <span style="width:64px">{{ \Illuminate\Support\Carbon::parse($cle . '-01')->translatedFormat('M Y') }}</span>
                                    <div class="flex-grow-1 bg-body-light rounded" style="height:14px"><div class="bg-primary rounded" style="height:14px; width:{{ round($montant / $max * 100) }}%"></div></div>
                                    <span class="text-end fw-semibold" style="width:90px">{{ number_format($montant, 0, ',', ' ') }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="block block-rounded h-100 mb-0">
                        <div class="block-header block-header-default"><h3 class="block-title">Abonnés par formule</h3></div>
                        <div class="block-content pb-3">
                            @forelse($parFormule as $nom => $nombre)
                                <div class="d-flex justify-content-between border-bottom py-1 small"><span>{{ $nom }}</span><strong>{{ $nombre }}</strong></div>
                            @empty
                                <p class="small text-muted">Aucun abonnement. <a href="{{ route('plateforme.formules') }}">Créez vos formules</a>, puis abonnez les établissements.</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="block block-rounded h-100 mb-0">
                        <div class="block-header block-header-default"><h3 class="block-title text-danger">En retard de paiement</h3></div>
                        <div class="block-content pb-3">
                            @forelse($retards as $a)
                                <div class="d-flex justify-content-between border-bottom py-1 small">
                                    <span>{{ $a->etablissement->nom }} <span class="text-muted">· {{ $a->formule->nom }}</span></span>
                                    <span class="badge bg-{{ \App\Models\Abonnement::COULEURS[$a->statut] }}">{{ $a->statut === 'grace' ? 'grâce jusqu\'au ' . $a->finGrace()->format('d/m') : 'modules coupés' }}</span>
                                </div>
                            @empty
                                <p class="small text-muted mb-0">Aucun retard.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="block block-rounded h-100 mb-0">
                        <div class="block-header block-header-default"><h3 class="block-title text-warning">Échéances à venir (30 jours)</h3></div>
                        <div class="block-content pb-3">
                            @forelse($echeances as $a)
                                <div class="d-flex justify-content-between border-bottom py-1 small">
                                    <span>{{ $a->etablissement->nom }} <span class="text-muted">· {{ $a->formule->nom }}</span></span>
                                    <span>{{ $a->fin->format('d/m/Y') }} <span class="text-muted">({{ $a->joursRestants() }} j)</span></span>
                                </div>
                            @empty
                                <p class="small text-muted mb-0">Aucune échéance dans les 30 jours.</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="block block-rounded mb-0">
                        <div class="block-header block-header-default"><h3 class="block-title">Derniers paiements</h3></div>
                        <div class="block-content pb-3 table-responsive">
                            @if($paiementsRecents->isEmpty())
                                <p class="small text-muted">Aucun paiement enregistré.</p>
                            @else
                                <table class="table table-sm fs-sm mb-0">
                                    <thead><tr><th>Reçu</th><th>Établissement</th><th class="text-end">Montant</th><th>Mode</th><th>Payé le</th><th>Période</th><th></th></tr></thead>
                                    <tbody>
                                        @foreach($paiementsRecents as $p)
                                            <tr><td>{{ $p->numero }}</td><td>{{ $p->abonnement->etablissement->nom ?? '-' }}</td><td class="text-end">{{ number_format((float) $p->montant, 0, ',', ' ') }}</td>
                                                <td>{{ \App\Models\PaiementAbonnement::MODES[$p->mode] ?? $p->mode }}</td><td>{{ $p->paye_le->format('d/m/Y') }}</td>
                                                <td>{{ $p->periode_debut->format('d/m/Y') }} → {{ $p->periode_fin->format('d/m/Y') }}</td>
                                                <td><a href="{{ route('plateforme.recu', $p) }}" target="_blank" title="Reçu"><i class="fa fa-print"></i></a></td></tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </div>
@endsection
