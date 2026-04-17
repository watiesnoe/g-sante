@extends('layouts.app')

@section('titre', 'Aide à la Prescription Médicale')

@section('content')
<div class="container mt-4 mb-5">
    <div class="text-center mb-5">
        <h2 class="h3 fw-bold mb-2 text-primary"><i class="fas fa-hand-holding-medical me-2"></i>Assistant de Prescription Expert</h2>
        <p class="text-muted">Recherchez une pathologie ou des symptômes pour accéder aux protocoles de traitement officiels.</p>
    </div>

    <div class="row justify-content-center mb-5">
        <div class="col-lg-8">
            <div class="card border-0 shadow-lg rounded-pill overflow-hidden">
                <div class="card-body p-2">
                    <div class="input-group input-group-lg border-0">
                        <span class="input-group-text bg-white border-0 ps-4"><i class="fas fa-search text-primary"></i></span>
                        <input type="text" id="medicalSearch" class="form-control border-0 ps-2" placeholder="Rechercher une maladie, un symptôme ou un antibiotique..." style="box-shadow: none;">
                    </div>
                </div>
            </div>
            <div id="searchResults" class="mt-3 list-group shadow-sm" style="display:none; position: absolute; width: 100%; z-index: 1000; left: 0;">
                <!-- Resultats injectés par JS -->
            </div>
        </div>
    </div>

    <!-- Quick Access Categories -->
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 category-card" data-category="Infections Respiratoires">
                <div class="card-body text-center p-4">
                    <div class="icon-circle bg-soft-info text-info mb-3 mx-auto">
                        <i class="fas fa-lungs"></i>
                    </div>
                    <h5 class="fw-bold">Infections Respiratoires</h5>
                    <p class="small text-muted mb-0">Grippe, Pneumonies, Bronchites...</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 category-card" data-category="Fièvres & Parasitoses">
                <div class="card-body text-center p-4">
                    <div class="icon-circle bg-soft-warning text-warning mb-3 mx-auto">
                        <i class="fas fa-thermometer-half"></i>
                    </div>
                    <h5 class="fw-bold">Fièvres & Parasitoses</h5>
                    <p class="small text-muted mb-0">Paludisme, Dengue, Thyphoïde...</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 category-card" data-category="Urogénital & Digestive">
                <div class="card-body text-center p-4">
                    <div class="icon-circle bg-soft-success text-success mb-3 mx-auto">
                        <i class="fas fa-bacteria"></i>
                    </div>
                    <h5 class="fw-bold">Urogénital & Digestive</h5>
                    <p class="small text-muted mb-0">Infections urinaires, Choléra...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Protocol Display Area -->
    <div id="protocolDisplay" style="display:none;">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-0" id="protocolContent">
                <!-- Contenu du protocole chargé via AJAX -->
            </div>
        </div>
    </div>
</div>

<style>
    .bg-soft-info { background-color: rgba(13, 202, 240, 0.1); }
    .bg-soft-warning { background-color: rgba(255, 193, 7, 0.1); }
    .bg-soft-success { background-color: rgba(25, 135, 84, 0.1); }
    .icon-circle {
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        font-size: 1.5rem;
    }
    .category-card { cursor: pointer; transition: transform 0.2s; }
    .category-card:hover { transform: translateY(-5px); }
</style>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    $('#medicalSearch').on('input', function() {
        let query = $(this).val();
        if (query.length < 2) {
            $('#searchResults').hide();
            return;
        }

        // Simuler une recherche ou appeler une API
        // Ici on va chercher parmi les maladies existantes
        $.ajax({
            url: '{{ route("infectiologie.pathologies.api") }}', // On pourrait créer une route dédiée à la recherche
            type: 'GET',
            success: function(response) {
                let html = '';
                // Filtrage local pour la démo
                let results = response.filter(m => m.nom.toLowerCase().includes(query.toLowerCase()));
                
                results.forEach(m => {
                    html += `<a href="javascript:void(0)" class="list-group-item list-group-item-action show-protocol" data-id="${m.id}">
                        <i class="fas fa-file-medical me-2 text-primary"></i> ${m.nom}
                    </a>`;
                });

                if (html) {
                    $('#searchResults').html(html).show();
                } else {
                    $('#searchResults').hide();
                }
            }
        });
    });

    $(document).on('click', '.show-protocol', function() {
        let id = $(this).data('id');
        $('#searchResults').hide();
        $('#medicalSearch').val($(this).text().trim());

        $.ajax({
            url: '/api/protocoles/' + id,
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    let p = response.protocole;
                    let html = `
                        <div class="p-4 border-bottom bg-light">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h4 class="fw-bold text-primary mb-1">${p.titre}</h4>
                                    <p class="mb-0 text-muted">Protocole Expert - Mise à jour: 2026</p>
                                </div>
                                <a href="/infectiologie/protocoles/${p.id}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill">
                                    <i class="fas fa-external-link-alt me-1"></i> Mode Plein Écran
                                </a>
                            </div>
                        </div>
                        <div class="p-4">
                            <div class="row g-4">
                                <div class="col-md-6 border-end">
                                    <h6 class="text-uppercase small fw-bold text-secondary mb-3">Diagnostic & Signes</h6>
                                    <p class="small">${p.signes || 'Consulter le médecin'}</p>
                                    <hr>
                                    <h6 class="text-uppercase small fw-bold text-secondary mb-3">Germes Probables</h6>
                                    <p class="small text-muted italic">${p.germes_adulte || 'Variable'}</p>
                                </div>
                                <div class="col-md-6 ps-md-4">
                                    <h6 class="text-uppercase small fw-bold text-success mb-3">Traitement de Première Intention</h6>
                                    <div class="bg-soft-success p-3 rounded-3 mb-3 border border-success-subtle">
                                        <div class="fw-bold text-success mb-1">${p.traitement_principal}</div>
                                        <div class="small fw-semibold mt-2">Posologie:</div>
                                        <div class="small text-dark">${p.posologie_principale}</div>
                                    </div>
                                    
                                    <h6 class="text-uppercase small fw-bold text-warning mb-3">Alternative Thérapeutique</h6>
                                    <div class="small opacity-75">${p.traitement_alternatif || 'N/A'}</div>
                                </div>
                            </div>
                        </div>
                    `;
                    $('#protocolContent').html(html);
                    $('#protocolDisplay').fadeIn();
                }
            }
        });
    });
});
</script>
@endsection
