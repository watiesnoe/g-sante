@extends('layouts.app')

@section('content')
<main id="main-container">
    <div class="content">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="block block-rounded">
                    <div class="block-header block-header-default bg-danger">
                        <h3 class="block-title text-white">Clôture de Caisse</h3>
                    </div>
                    <div class="block-content block-content-full">
                        <div class="row mb-4">
                            <div class="col-6 col-md-3 text-center">
                                <p class="text-muted mb-1">Fonds de départ</p>
                                <p class="fs-3 fw-semibold text-primary">{{ number_format($session->solde_initial, 0, ',', ' ') }} F</p>
                            </div>
                            <div class="col-6 col-md-3 text-center">
                                <p class="text-muted mb-1">Entrées</p>
                                <p class="fs-3 fw-semibold text-success">+{{ number_format($totalEntrees, 0, ',', ' ') }} F</p>
                            </div>
                            <div class="col-6 col-md-3 text-center mt-3 mt-md-0">
                                <p class="text-muted mb-1">Sorties</p>
                                <p class="fs-3 fw-semibold text-danger">-{{ number_format($totalSorties, 0, ',', ' ') }} F</p>
                            </div>
                            <div class="col-6 col-md-3 text-center mt-3 mt-md-0">
                                <p class="text-muted mb-1">Solde Théorique</p>
                                <p class="fs-3 fw-semibold text-info">{{ number_format($session->solde_theorique, 0, ',', ' ') }} F</p>
                            </div>
                        </div>

                        @if(count($totauxParMode) > 0)
                            <table class="table table-sm table-bordered mb-4">
                                <thead class="table-light">
                                    <tr><th>Mode de paiement</th><th class="text-end">Entrées</th><th class="text-end">Sorties</th><th class="text-end">Net</th></tr>
                                </thead>
                                <tbody>
                                    @foreach($totauxParMode as $mode => $t)
                                        <tr>
                                            <td>{{ \App\Models\CaisseMouvement::MODES[$mode] ?? ucfirst($mode) }}</td>
                                            <td class="text-end">{{ number_format($t['entree'], 0, ',', ' ') }} F</td>
                                            <td class="text-end">{{ number_format($t['sortie'], 0, ',', ' ') }} F</td>
                                            <td class="text-end fw-semibold">{{ number_format($t['entree'] - $t['sortie'], 0, ',', ' ') }} F</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif

                        <hr>

                        <form action="{{ route('caisse.storeClose') }}" method="POST">
                            @csrf
                            <div class="mb-4">
                                <label class="form-label" for="solde_reel">Montant Physique Compté (Solde Réel) en XOF</label>
                                <input type="text" class="form-control form-control-lg price-input @error('solde_reel') is-invalid @enderror" id="solde_reel" name="solde_reel" value="{{ old('solde_reel') }}" required placeholder="Combien d'argent avez-vous en caisse ?">
                                @error('solde_reel')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text mt-2 text-warning">Si le montant physique est différent du solde théorique ({{ number_format($session->solde_theorique, 0, ',', ' ') }} F), l'écart doit être justifié ci-dessous.</div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label" for="justification_ecart">Justification de l'écart <small class="text-muted">(obligatoire s'il y a un écart)</small></label>
                                <textarea class="form-control @error('justification_ecart') is-invalid @enderror" id="justification_ecart" name="justification_ecart" rows="2">{{ old('justification_ecart') }}</textarea>
                                @error('justification_ecart')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-4 text-center">
                                <a href="{{ route('caisse.my_session') }}" class="btn btn-secondary me-2">Annuler</a>
                                <button type="submit" class="btn btn-danger btn-lg" onclick="return confirm('Êtes-vous sûr de vouloir clôturer votre caisse ? Cette action est irréversible.')">
                                    <i class="fa fa-lock me-1"></i> Valider la Clôture
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection
