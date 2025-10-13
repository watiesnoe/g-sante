@extends('layouts.app')

@section('titre', 'Atlas des Pathologies Infectieuses')

@section('content')
<div class="container mt-4 mb-5">

    {{-- ── Barre de sélection groupée ── --}}
    <div id="bulkBar" class="d-none alert alert-warning d-flex align-items-center justify-content-between mb-3 rounded-3 shadow-sm px-4 py-3" style="border-left: 4px solid #f59e0b;">
        <div class="d-flex align-items-center gap-2">
            <i class="fas fa-check-square text-warning fs-5"></i>
            <span id="bulkCount" class="fw-semibold">0 pathologie(s) sélectionnée(s)</span>
        </div>
        <div class="d-flex gap-2">
            <button id="btnDeselectAll" class="btn btn-sm btn-outline-secondary rounded-pill">
                <i class="fas fa-times me-1"></i> Désélectionner tout
            </button>
            <button id="btnDeleteBulk" class="btn btn-sm btn-danger rounded-pill">
                <i class="fas fa-trash-alt me-1"></i> Supprimer la sélection
            </button>
        </div>
    </div>

    {{-- ── En-tête ── --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h2 class="h3 fw-bold mb-0 text-primary"><i class="fas fa-atlas me-2"></i>Atlas des Pathologies</h2>
            <p class="text-muted small mb-0">Base de connaissances des maladies suivies dans l'établissement.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap align-items-center">
            <button id="btnSelectAll" class="btn btn-outline-secondary btn-sm" title="Sélectionner toutes les pathologies">
                <i class="fas fa-check-double me-1"></i> Tout sélectionner
            </button>
            <div class="vr"></div>
            <a href="{{ route('infectiologie.pathologies.export', 'xlsx') }}" class="btn btn-outline-success" title="Exporter en Excel">
                <i class="fas fa-file-excel me-1"></i> Excel
            </a>
            <a href="{{ route('infectiologie.pathologies.export', 'pdf') }}" class="btn btn-outline-danger" title="Exporter en PDF">
                <i class="fas fa-file-pdf me-1"></i> PDF
            </a>
            <a href="{{ route('export.model', 'maladies') }}?template=1" class="btn btn-outline-secondary" title="Télécharger le modèle CSV">
                <i class="fas fa-download me-1"></i> Modèle CSV
            </a>
            <button type="button"
                    class="btn btn-outline-primary btn-open-import-modal"
                    data-module="maladies"
                    data-label="les Pathologies">
                <i class="fas fa-file-import me-1"></i> Importer
            </button>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAddPathologie">
                <i class="fas fa-plus me-1"></i> Nouvelle Pathologie
            </button>
        </div>
    </div>

    {{-- ── Barre de recherche ── --}}
    <form method="GET" action="{{ route('infectiologie.pathologies') }}" class="row mb-4">
        <div class="col-md-6 col-lg-4">
            <div class="input-group shadow-sm rounded-pill overflow-hidden border border-primary border-opacity-25">
                <span class="input-group-text bg-white border-0 text-primary">
                    <i class="fas fa-search"></i>
                </span>
                <input type="text" name="search" value="{{ $search ?? '' }}" id="searchPathology" class="form-control border-0 ps-0 focus-none" placeholder="Rechercher une pathologie, un symptôme...">
                @if(!empty($search))
                <a href="{{ route('infectiologie.pathologies') }}" class="input-group-text bg-white border-0 text-danger" title="Effacer la recherche">
                    <i class="fas fa-times"></i>
                </a>
                @endif
                <button type="submit" class="btn btn-primary px-4 fw-bold">Chercher</button>
            </div>
        </div>
    </form>

    {{-- ── Grille de cartes ── --}}
    <div class="row g-4" id="pathologiesGrid">
        @forelse($pathologies as $p)
        <div class="col-md-4 pathology-col" data-id="{{ $p->id }}">
            <div class="card border-0 shadow-sm h-100 pathology-card rounded-3 overflow-hidden position-relative">

                {{-- Checkbox --}}
                <div class="position-absolute top-0 start-0 m-2" style="z-index:10;">
                    <input type="checkbox"
                           class="form-check-input pathology-checkbox shadow-sm"
                           data-id="{{ $p->id }}"
                           id="chk_{{ $p->id }}"
                           style="width:1.2rem;height:1.2rem;cursor:pointer;accent-color:#0665D0;">
                </div>

                {{-- Bouton supprimer individuel --}}
                <div class="position-absolute top-0 end-0 m-2" style="z-index:10;">
                    <button class="btn btn-sm btn-danger rounded-circle btn-delete-single shadow-sm"
                            data-id="{{ $p->id }}"
                            data-nom="{{ $p->nom }}"
                            title="Supprimer cette pathologie"
                            style="width:32px;height:32px;padding:0;line-height:32px;">
                        <i class="fas fa-trash-alt" style="font-size:.75rem;"></i>
                    </button>
                </div>

                <div class="card-body p-4 pt-5">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        @php
                            $nomLower = strtolower($p->nom);
                            $icon = 'fa-microbe';
                            $colorClass = 'primary';
                            
                            if (str_contains($nomLower, 'cérébral') || str_contains($nomLower, 'méningite') || str_contains($nomLower, 'neuro')) {
                                $icon = 'fa-brain';
                                $colorClass = 'danger';
                            } elseif (str_contains($nomLower, 'respiratoire') || str_contains($nomLower, 'pneumo') || str_contains($nomLower, 'toux') || str_contains($nomLower, 'bronchite')) {
                                $icon = 'fa-lungs';
                                $colorClass = 'info';
                            } elseif (str_contains($nomLower, 'cœur') || str_contains($nomLower, 'cardio') || str_contains($nomLower, 'endocardite')) {
                                $icon = 'fa-heartbeat';
                                $colorClass = 'danger';
                            } elseif (str_contains($nomLower, 'os') || str_contains($nomLower, 'arthrite') || str_contains($nomLower, 'ostéomyélite')) {
                                $icon = 'fa-bone';
                                $colorClass = 'warning';
                            } elseif (str_contains($nomLower, 'dent') || str_contains($nomLower, 'carie')) {
                                $icon = 'fa-tooth';
                                $colorClass = 'secondary';
                            } elseif (str_contains($nomLower, 'virus') || str_contains($nomLower, 'paludisme') || str_contains($nomLower, 'vih')) {
                                $icon = 'fa-virus';
                                $colorClass = 'success';
                            } elseif (str_contains($nomLower, 'gastro') || str_contains($nomLower, 'choléra') || str_contains($nomLower, 'diarrhée')) {
                                $icon = 'fa-stomach'; // ou fa-bacteria si stomach n'est pas dispo
                                $colorClass = 'warning';
                            }
                        @endphp
                        <div class="icon-shape bg-soft-{{ $colorClass }} text-{{ $colorClass }}">
                            <i class="fas {{ $icon }}"></i>
                        </div>
                        <span class="badge rounded-pill bg-light text-dark border"><i class="fas fa-users me-1 text-muted"></i>{{ $p->consultations_count }} cas</span>
                    </div>

                    <h5 class="fw-bold text-dark mb-2">{{ $p->nom }}</h5>
                    <p class="small text-muted mb-3 line-clamp-2">{{ $p->description ?: 'Aucune description disponible pour cette pathologie.' }}</p>

                    <div class="mb-3">
                        <h6 class="text-uppercase small fw-bold text-secondary mb-2" style="font-size: 0.65rem;">
                            <i class="fas fa-thermometer-half me-1"></i> Symptômes Clés
                        </h6>
                        <div class="d-flex flex-wrap gap-1">
                            @forelse($p->symptomes->take(3) as $s)
                                <span class="badge bg-soft-secondary text-secondary small">{{ $s->nom }}</span>
                            @empty
                                <span class="text-muted small italic">Aucun signe lié</span>
                            @endforelse
                        </div>
                    </div>

                    <div class="pt-3 border-top mt-auto">
                        @if($p->protocole)
                            <a href="{{ route('infectiologie.protocoles.show', $p->protocole->uuid) }}" class="btn btn-sm btn-outline-primary w-100 rounded-pill">
                                <i class="fas fa-book-medical me-1"></i> Voir le Protocole Expert
                            </a>
                        @else
                            <a href="{{ route('infectiologie.protocoles') }}" class="btn btn-sm btn-outline-warning w-100 rounded-pill">
                                <i class="fas fa-plus-circle me-1"></i> Créer un Protocole
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12 text-center py-5">
            <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
            <p class="text-muted">Aucune pathologie enregistrée.</p>
        </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="mt-5 d-flex justify-content-center">
        {{ $pathologies->links('pagination::bootstrap-5') }}
    </div>
</div>

{{-- Modal Ajout Pathologie --}}
<div class="modal fade" id="modalAddPathologie" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ajouter une Pathologie</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Nom de la maladie</label>
                    <input type="text" class="form-control" placeholder="Ex: Zika, Ebola...">
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                <button type="button" class="btn btn-primary disabled">Enregistrer (Module admin)</button>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-soft-primary  { background-color: rgba(6, 101, 208, 0.1); }
    .bg-soft-secondary{ background-color: rgba(108, 117, 125, 0.1); }
    .bg-soft-success  { background-color: rgba(25, 135, 84, 0.1); }
    .bg-soft-danger   { background-color: rgba(220, 53, 69, 0.1); }
    .bg-soft-warning  { background-color: rgba(255, 193, 7, 0.1); }
    .bg-soft-info     { background-color: rgba(13, 202, 240, 0.1); }
    
    .icon-shape {
        width: 45px; height: 45px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center; font-size: 1.25rem;
    }
    .pathology-card {
        transition: all .3s cubic-bezier(0.4, 0, 0.2, 1);
        border: 2px solid transparent !important;
        background: linear-gradient(180deg, #ffffff 0%, #f8fbfd 100%);
    }
    .pathology-card:hover {
        transform: translateY(-8px);
        border-color: rgba(6, 101, 208, 0.3) !important;
        box-shadow: 0 15px 30px rgba(6, 101, 208, 0.1) !important;
    }
    .pathology-card.selected {
        border-color: #0665D0 !important;
        background: rgba(6,101,208,.04);
        box-shadow: 0 0 0 3px rgba(6,101,208,.15) !important;
    }
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .pathology-col.removing {
        transition: opacity .35s, transform .35s;
        opacity: 0;
        transform: scale(.9);
    }
    .focus-none:focus {
        box-shadow: none;
        border-color: #dee2e6;
    }
</style>

@include('layouts.partials.import_modal')

@section('scripts')
<script>
$(function () {
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    function getCheckedIds() {
        return $('.pathology-checkbox:checked').map(function(){ return $(this).data('id'); }).get();
    }

    function updateBulkBar() {
        var ids = getCheckedIds();
        if (ids.length > 0) {
            $('#bulkCount').text(ids.length + ' pathologie(s) sélectionnée(s)');
            $('#bulkBar').removeClass('d-none').addClass('d-flex');
        } else {
            $('#bulkBar').addClass('d-none').removeClass('d-flex');
        }
    }

    function removeCards(ids) {
        ids.forEach(function(id) {
            var $col = $('.pathology-col[data-id="' + id + '"]');
            // Décocher immédiatement pour que updateBulkBar() retombe à 0
            $col.find('.pathology-checkbox').prop('checked', false);
            $col.find('.pathology-card').removeClass('selected');
            $col.addClass('removing');
            setTimeout(function() { $col.remove(); }, 360);
        });
        // Mettre à jour la barre APRÈS avoir tout décoché
        updateBulkBar();
    }

    /* Checkbox → surbrillance */
    $(document).on('change', '.pathology-checkbox', function () {
        var $card = $(this).closest('.pathology-col').find('.pathology-card');
        $card.toggleClass('selected', $(this).is(':checked'));
        updateBulkBar();
    });

    /* Tout sélectionner / désélectionner */
    $('#btnSelectAll').on('click', function () {
        var allChecked = ($('.pathology-checkbox:not(:checked)').length === 0);
        if (allChecked) {
            $('.pathology-checkbox').prop('checked', false).trigger('change');
            $(this).html('<i class="fas fa-check-double me-1"></i> Tout sélectionner');
        } else {
            $('.pathology-checkbox').prop('checked', true).trigger('change');
            $(this).html('<i class="fas fa-times me-1"></i> Tout désélectionner');
        }
    });

    /* Désélectionner tout (barre) */
    $('#btnDeselectAll').on('click', function () {
        $('.pathology-checkbox').prop('checked', false).trigger('change');
        $('#btnSelectAll').html('<i class="fas fa-check-double me-1"></i> Tout sélectionner');
    });

    /* ── Suppression individuelle ── */
    $(document).on('click', '.btn-delete-single', function () {
        var id  = $(this).data('id');
        var nom = $(this).data('nom');

        Swal.fire({
            icon: 'warning',
            title: 'Supprimer cette pathologie ?',
            html: '<strong>' + nom + '</strong> sera définitivement supprimée.',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-trash-alt me-1"></i> Supprimer',
            cancelButtonText: 'Annuler',
            reverseButtons: true,
        }).then(function (result) {
            if (!result.isConfirmed) return;
            $.ajax({
                url: '/maladies/' + id,
                type: 'DELETE',
                success: function (data) {
                    removeCards([id]);
                    Swal.fire({ icon: 'success', title: 'Supprimée !', text: data.message, timer: 2000, showConfirmButton: false });
                },
                error: function (xhr) {
                    Swal.fire('Erreur', xhr.responseJSON?.message || 'Impossible de supprimer.', 'error');
                }
            });
        });
    });

    /* ── Suppression groupée ── */
    $('#btnDeleteBulk').on('click', function () {
        var ids = getCheckedIds();
        if (!ids.length) return;

        Swal.fire({
            icon: 'warning',
            title: 'Supprimer ' + ids.length + ' pathologie(s) ?',
            text: 'Cette action est irréversible.',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-trash-alt me-1"></i> Supprimer tout',
            cancelButtonText: 'Annuler',
            reverseButtons: true,
        }).then(function (result) {
            if (!result.isConfirmed) return;
            $.ajax({
                url: '{{ route("maladies.destroyBulk") }}',
                type: 'DELETE',
                data: JSON.stringify({ ids: ids }),
                contentType: 'application/json',
                success: function (data) {
                    removeCards(ids);
                    // removeCards() décoche et appelle updateBulkBar() — pas besoin de le refaire
                    $('#btnSelectAll').html('<i class="fas fa-check-double me-1"></i> Tout sélectionner');
                    Swal.fire({ icon: 'success', title: 'Suppression réussie !', text: data.message, timer: 2500, showConfirmButton: false });
                },
                error: function (xhr) {
                    Swal.fire('Erreur', xhr.responseJSON?.message || 'Impossible de supprimer la sélection.', 'error');
                }
            });
        });
    });
});
</script>
@endsection
@endsection
