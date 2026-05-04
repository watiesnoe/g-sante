@extends('layouts.app')

@section('content')
<main id="main-container">
    <div class="content">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('info'))
            <div class="alert alert-info">{{ session('info') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h3 fw-bold mb-0">Ma Caisse (Session en cours)</h2>
            <a href="{{ route('caisse.close') }}" class="btn btn-danger">
                <i class="fa fa-lock me-1"></i> Clôturer ma Caisse
            </a>
        </div>

        <div class="row">
            <div class="col-6 col-md-3 col-lg-3 col-xl-3">
                <a class="block block-rounded block-link-pop border-start border-primary border-4" href="javascript:void(0)">
                    <div class="block-content block-content-full">
                        <div class="fs-sm fw-semibold text-uppercase text-muted">Fonds Initial</div>
                        <div class="fs-2 fw-normal text-dark">{{ number_format($session->solde_initial, 0, ',', ' ') }} <small class="fs-6">XOF</small></div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-3 col-lg-3 col-xl-3">
                <a class="block block-rounded block-link-pop border-start border-success border-4" href="javascript:void(0)">
                    <div class="block-content block-content-full">
                        <div class="fs-sm fw-semibold text-uppercase text-muted">Entrées</div>
                        <div class="fs-2 fw-normal text-dark">+{{ number_format($totalEntrees, 0, ',', ' ') }} <small class="fs-6">XOF</small></div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-3 col-lg-3 col-xl-3">
                <a class="block block-rounded block-link-pop border-start border-danger border-4" href="javascript:void(0)">
                    <div class="block-content block-content-full">
                        <div class="fs-sm fw-semibold text-uppercase text-muted">Sorties</div>
                        <div class="fs-2 fw-normal text-dark">-{{ number_format($totalSorties, 0, ',', ' ') }} <small class="fs-6">XOF</small></div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-3 col-lg-3 col-xl-3">
                <a class="block block-rounded block-link-pop border-start border-info border-4" href="javascript:void(0)">
                    <div class="block-content block-content-full">
                        <div class="fs-sm fw-semibold text-uppercase text-muted">Solde Théorique</div>
                        <div class="fs-2 fw-normal text-dark">{{ number_format($session->solde_theorique, 0, ',', ' ') }} <small class="fs-6">XOF</small></div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Mouvements -->
        <div class="block block-rounded">
            <div class="block-header block-header-default">
                <h3 class="block-title">Transactions de la session</h3>
            </div>
            <div class="block-content block-content-full">
                <table class="table table-bordered table-striped table-vcenter">
                    <thead>
                        <tr>
                            <th>Date / Heure</th>
                            <th>Type</th>
                            <th>Motif</th>
                            <th class="text-end">Montant</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($mouvements as $mvt)
                        <tr>
                            <td>{{ $mvt->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                @if($mvt->type == 'entree')
                                    <span class="badge bg-success">Entrée</span>
                                @else
                                    <span class="badge bg-danger">Sortie</span>
                                @endif
                            </td>
                            <td>{{ $mvt->motif }}</td>
                            <td class="text-end fw-semibold">
                                @if($mvt->type == 'entree')
                                    <span class="text-success">+{{ number_format($mvt->montant, 0, ',', ' ') }} F</span>
                                @else
                                    <span class="text-danger">-{{ number_format($mvt->montant, 0, ',', ' ') }} F</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center">Aucune transaction pour le moment dans cette session.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('.table').DataTable({
            "language": {
                "url": "https://cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json"
            },
            "order": [[0, "desc"]]
        });
    });
</script>
@endsection
