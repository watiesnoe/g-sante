@extends('layouts.app')

@section('title', 'Inventaire ' . $inventaire->reference)

@section('content')
<main id="main-container">
<div class="content">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 fw-bold mb-1">
                <i class="fa fa-clipboard-check text-primary me-2"></i>
                {{ $inventaire->reference }}
            </h2>
            <p class="text-muted mb-0">
                <i class="fa fa-calendar me-1"></i> {{ $inventaire->date_inventaire->format('d/m/Y') }}
                &nbsp;|&nbsp;
                <i class="fa fa-user me-1"></i> {{ $inventaire->user->name }}
                &nbsp;|&nbsp;
                @if($inventaire->statut === 'validé')
                    <span class="badge bg-success">Validé</span>
                @elseif($inventaire->statut === 'annulé')
                    <span class="badge bg-danger">Annulé</span>
                @else
                    <span class="badge bg-warning text-dark">Brouillon</span>
                @endif
            </p>
        </div>
        <div class="d-flex gap-2">
            @if($inventaire->statut === 'brouillon')
                <a href="{{ route('inventaires.edit', $inventaire->uuid) }}" class="btn btn-warning">
                    <i class="fa fa-pencil me-1"></i> Modifier
                </a>
                <button type="button" class="btn btn-success" id="btnValider">
                    <i class="fa fa-check-circle me-1"></i> Valider & Appliquer au stock
                </button>
            @endif
            <a href="{{ route('inventaires.index') }}" class="btn btn-secondary">
                <i class="fa fa-arrow-left me-1"></i> Retour
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fa fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($inventaire->observations)
        <div class="alert alert-info">
            <i class="fa fa-info-circle me-2"></i><strong>Note :</strong> {{ $inventaire->observations }}
        </div>
    @endif

    {{-- Statistiques --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="block block-rounded text-center border-start border-primary border-4 h-100">
                <div class="block-content py-3">
                    <div class="fs-1 fw-bold text-primary">{{ $stats['total'] }}</div>
                    <div class="small text-muted">Médicaments contrôlés</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="block block-rounded text-center border-start border-success border-4 h-100">
                <div class="block-content py-3">
                    <div class="fs-1 fw-bold text-success">{{ $stats['conformes'] }}</div>
                    <div class="small text-muted">Conformes (écart = 0)</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="block block-rounded text-center border-start border-info border-4 h-100">
                <div class="block-content py-3">
                    <div class="fs-1 fw-bold text-info">{{ $stats['excedents'] }}</div>
                    <div class="small text-muted">Excédents (+)</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="block block-rounded text-center border-start border-danger border-4 h-100">
                <div class="block-content py-3">
                    <div class="fs-1 fw-bold text-danger">{{ $stats['manquants'] }}</div>
                    <div class="small text-muted">Manquants (−)</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Barre de conformité --}}
    <div class="block block-rounded mb-4">
        <div class="block-content py-3">
            <div class="d-flex justify-content-between mb-1">
                <span class="fw-semibold">Taux de conformité</span>
                <span class="fw-bold {{ $stats['taux'] >= 80 ? 'text-success' : ($stats['taux'] >= 50 ? 'text-warning' : 'text-danger') }}">
                    {{ $stats['taux'] }} %
                </span>
            </div>
            <div class="progress" style="height: 12px; border-radius: 6px;">
                <div class="progress-bar {{ $stats['taux'] >= 80 ? 'bg-success' : ($stats['taux'] >= 50 ? 'bg-warning' : 'bg-danger') }}"
                     style="width: {{ $stats['taux'] }}%; transition: width 1s ease;">
                </div>
            </div>
        </div>
    </div>

    {{-- Tableau détail --}}
    <div class="block block-rounded">
        <div class="block-header block-header-default d-flex justify-content-between align-items-center">
            <h3 class="block-title">Détail des médicaments</h3>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-alt-secondary" id="btnTousLignes">Tous</button>
                <button type="button" class="btn btn-sm btn-alt-danger" id="btnManquants">Manquants</button>
                <button type="button" class="btn btn-sm btn-alt-info" id="btnExcedents">Excédents</button>
            </div>
        </div>
        <div class="block-content block-content-full p-0">
            <div class="table-responsive">
                <table class="table table-hover table-vcenter mb-0" id="lignesTable">
                    <thead class="table-light">
                        <tr>
                            <th>Médicament</th>
                            <th>Famille</th>
                            <th class="text-center">Stock Théorique</th>
                            <th class="text-center">Stock Réel</th>
                            <th class="text-center">Écart</th>
                            <th>Observations</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($inventaire->lignes as $ligne)
                    @php $ecart = $ligne->ecart; @endphp
                    <tr data-ecart="{{ $ecart }}">
                        <td class="fw-semibold">{{ $ligne->medicament->nom }}</td>
                        <td><span class="badge bg-secondary-subtle text-secondary">{{ $ligne->medicament->famille?->nom ?? '-' }}</span></td>
                        <td class="text-center">{{ $ligne->stock_theorique }}</td>
                        <td class="text-center fw-bold">{{ $ligne->stock_reel }}</td>
                        <td class="text-center">
                            <span class="badge fs-sm {{ $ecart === 0 ? 'bg-success' : ($ecart > 0 ? 'bg-info' : 'bg-danger') }}">
                                {{ $ecart >= 0 ? '+' : '' }}{{ $ecart }}
                            </span>
                        </td>
                        <td class="text-muted small">{{ $ligne->observations ?? '-' }}</td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</main>
@endsection

@section('scripts')
<script>
$(document).ready(function () {
    // Filtres rapides
    $('#btnTousLignes').on('click', function () { $('tr[data-ecart]').show(); });
    $('#btnManquants').on('click', function () {
        $('tr[data-ecart]').each(function () { $(this).toggle(parseInt($(this).data('ecart')) < 0); });
    });
    $('#btnExcedents').on('click', function () {
        $('tr[data-ecart]').each(function () { $(this).toggle(parseInt($(this).data('ecart')) > 0); });
    });

    // Valider
    @if($inventaire->statut === 'brouillon')
    $('#btnValider').on('click', function () {
        Swal.fire({
            title: 'Valider l\'inventaire ?',
            html: '<strong>Attention :</strong> Les stocks de tous les médicaments ayant un écart seront mis à jour. Cette action est <u>irréversible</u>.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Oui, valider et appliquer',
            cancelButtonText: 'Annuler',
        }).then(function (result) {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route('inventaires.valider', $inventaire->uuid) }}',
                    type: 'POST',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function (r) {
                        if (r.success) {
                            Swal.fire('Validé !', r.message, 'success').then(() => location.reload());
                        } else {
                            Swal.fire('Erreur', r.message, 'error');
                        }
                    },
                    error: function (xhr) {
                        Swal.fire('Erreur', xhr.responseJSON?.message || 'Erreur inattendue', 'error');
                    }
                });
            }
        });
    });
    @endif
});
</script>
@endsection
