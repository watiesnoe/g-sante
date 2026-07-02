@extends('layouts.app')

@section('title', 'Nouvel Inventaire')

@section('styles')
<style>
    /* ── Dropdown catégorie ─────────────────────────────────────── */
    .cat-selector-bar {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 20px;
        background: #f8fafc;
        border-bottom: 2px solid #e2e8f0;
        flex-wrap: wrap;
    }
    .cat-selector-label {
        font-weight: 600;
        font-size: 0.82rem;
        color: #475569;
        white-space: nowrap;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .cat-select-wrapper {
        position: relative;
        flex: 1;
        max-width: 340px;
        min-width: 200px;
    }
    .cat-select-wrapper .cat-icon {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #2c7fb8;
        pointer-events: none;
        font-size: 0.85rem;
    }
    .cat-select-wrapper .cat-arrow {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        pointer-events: none;
        font-size: 0.8rem;
        transition: transform .2s;
    }
    #catDropdown {
        width: 100%;
        appearance: none;
        -webkit-appearance: none;
        padding: 9px 36px 9px 34px;
        border: 1.5px solid #cbd5e1;
        border-radius: 10px;
        background: #fff;
        font-size: 0.85rem;
        font-weight: 500;
        color: #1e293b;
        cursor: pointer;
        transition: border-color .2s, box-shadow .2s;
        outline: none;
    }
    #catDropdown:focus {
        border-color: #2c7fb8;
        box-shadow: 0 0 0 3px rgba(44,127,184,.12);
    }
    #catDropdown option {
        padding: 8px 12px;
    }
    /* Nav pills compat (gardés mais cachés) */
    .cat-panel { display: none; }
    .cat-panel.active { display: block; }

    /* ── Info catégorie active ──────────────────────────────────── */
    .cat-panel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 20px;
        background: linear-gradient(90deg, #e8f4fe 0%, #f8fafc 100%);
        border-bottom: 1px solid #e2e8f0;
    }
    .cat-panel-title {
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 600;
        font-size: 0.88rem;
        color: #2c7fb8;
    }
    .cat-pill-count {
        background: #2c7fb8;
        color: #fff;
        border-radius: 12px;
        padding: 2px 10px;
        font-size: 0.75rem;
        font-weight: 700;
    }

    /* ── Barre de progression ───────────────────────────────────── */
    .progress-bar-fill { transition: width .4s ease; }

    /* ── Résumé sticky ──────────────────────────────────────────── */
    .summary-sticky {
        position: sticky;
        bottom: 0;
        background: #fff;
        border-top: 2px solid #e2e8f0;
        padding: 12px 20px;
        z-index: 100;
        box-shadow: 0 -4px 12px rgba(0,0,0,.06);
    }
    .summary-stat { min-width: 90px; text-align: center; }
    .summary-stat .num { font-size: 1.5rem; font-weight: 700; line-height: 1; }
    .summary-stat .lbl { font-size: 0.72rem; color: #64748b; text-transform: uppercase; letter-spacing: .5px; }

    .stock-reel-input { min-width: 100px; }

    /* ── Recherche globale ──────────────────────────────────────── */
    .global-search-wrapper {
        position: relative;
        flex: 1;
        max-width: 300px;
        min-width: 180px;
    }
    .global-search-wrapper .search-icon {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 0.82rem;
        pointer-events: none;
        transition: color .2s;
    }
    .global-search-wrapper:focus-within .search-icon {
        color: #2c7fb8;
    }
    .global-search-wrapper .clear-search {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 0.78rem;
        cursor: pointer;
        display: none;
        border: none;
        background: none;
        padding: 2px 4px;
        border-radius: 4px;
        transition: color .15s;
    }
    .global-search-wrapper .clear-search:hover { color: #ef4444; }
    #globalSearch {
        width: 100%;
        padding: 9px 32px 9px 34px;
        border: 1.5px solid #cbd5e1;
        border-radius: 10px;
        background: #fff;
        font-size: 0.85rem;
        color: #1e293b;
        outline: none;
        transition: border-color .2s, box-shadow .2s;
    }
    #globalSearch:focus {
        border-color: #2c7fb8;
        box-shadow: 0 0 0 3px rgba(44,127,184,.12);
    }
    #globalSearch::placeholder { color: #b0bec5; }

    /* badge résultat recherche */
    #searchResultBadge {
        font-size: 0.78rem;
        white-space: nowrap;
    }
</style>
@endsection

@section('content')
<main id="main-container">
<div class="content">

    {{-- Entête --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 fw-bold mb-1">
                <i class="fa fa-clipboard-list text-primary me-2"></i>Nouvel Inventaire
            </h2>
            <p class="text-muted mb-0">Saisie des quantités réelles comptées en pharmacie</p>
        </div>
        <a href="{{ route('inventaires.index') }}" class="btn btn-secondary">
            <i class="fa fa-arrow-left me-1"></i> Retour
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <form action="{{ route('inventaires.store') }}" method="POST" id="inventaireForm">
        @csrf

        {{-- Informations générales --}}
        <div class="block block-rounded mb-4">
            <div class="block-header block-header-default">
                <h3 class="block-title"><i class="fa fa-info-circle me-1"></i> Informations générales</h3>
            </div>
            <div class="block-content">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold" for="date_inventaire">
                            Date de l'inventaire <span class="text-danger">*</span>
                        </label>
                        <input type="date" id="date_inventaire" name="date_inventaire"
                               class="form-control @error('date_inventaire') is-invalid @enderror"
                               value="{{ old('date_inventaire', date('Y-m-d')) }}" required>
                        @error('date_inventaire')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-8">
                        <label class="form-label fw-semibold" for="observations">Observations</label>
                        <input type="text" id="observations" name="observations"
                               class="form-control"
                               placeholder="Ex : Inventaire de fin de mois, vérification annuelle..."
                               value="{{ old('observations') }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- Barre de progression --}}
        @php
            $totalMeds = $familles->sum(fn($f) => $f->medicaments->count()) + $sansFamille->count();
        @endphp
        <div class="block block-rounded mb-3">
            <div class="block-content py-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="fw-semibold text-muted small">
                        <i class="fa fa-pills me-1"></i>
                        Progression — <span id="progressCount">0</span> / {{ $totalMeds }} modifiés
                    </span>
                    <span class="fw-bold text-primary" id="progressPct">0 %</span>
                </div>
                <div class="progress" style="height: 8px; border-radius: 4px;">
                    <div class="progress-bar bg-primary progress-bar-fill" id="progressBar"
                         role="progressbar" style="width: 0%"></div>
                </div>
            </div>
        </div>

        {{-- Bloc principal --}}
        <div class="block block-rounded">
            {{-- En-tête du bloc --}}
            <div class="block-header block-header-default d-flex justify-content-between align-items-center">
                <h3 class="block-title">
                    <i class="fa fa-layer-group me-1"></i>
                    Saisie par catégorie
                </h3>
                <button type="button" id="btnCopierStock" class="btn btn-sm btn-alt-info">
                    <i class="fa fa-copy me-1"></i> Copier théorique (tout)
                </button>
            </div>

            {{-- Barre de contrôles : recherche + dropdown catégorie --}}
            <div class="cat-selector-bar">

                {{-- 🔍 Recherche globale --}}
                <div class="global-search-wrapper">
                    <i class="fa fa-search search-icon"></i>
                    <input type="text" id="globalSearch" placeholder="Rechercher un médicament…" autocomplete="off">
                    <button type="button" class="clear-search" id="clearSearch" title="Effacer">
                        <i class="fa fa-times"></i>
                    </button>
                </div>

                <span id="searchResultBadge" class="badge bg-primary" style="display:none;"></span>

                {{-- Séparateur --}}
                <div style="width:1px;height:28px;background:#e2e8f0;"></div>

                {{-- 🏷 Dropdown catégorie --}}
                <span class="cat-selector-label">
                    <i class="fa fa-filter text-primary"></i>
                    Catégorie :
                </span>
                <div class="cat-select-wrapper">
                    <i class="fa fa-tag cat-icon"></i>
                    <select id="catDropdown">
                        @foreach($familles as $fi => $famille)
                        <option value="panel-{{ $famille->id }}"
                                data-count="{{ $famille->medicaments->count() }}"
                                data-label="{{ $famille->nom }}"
                                {{ $fi === 0 ? 'selected' : '' }}>
                            {{ $famille->nom }} ({{ $famille->medicaments->count() }})
                        </option>
                        @endforeach
                        @if($sansFamille->count())
                        <option value="panel-sans-famille"
                                data-count="{{ $sansFamille->count() }}"
                                data-label="Sans catégorie"
                                {{ $familles->isEmpty() ? 'selected' : '' }}>
                            Sans catégorie ({{ $sansFamille->count() }})
                        </option>
                        @endif
                    </select>
                    <i class="fa fa-chevron-down cat-arrow"></i>
                </div>
                <button type="button" class="btn btn-sm btn-alt-secondary" id="btnCopierCat">
                    <i class="fa fa-copy me-1"></i> Copier théorique (catégorie)
                </button>
            </div>

            {{-- Panneaux par famille --}}
            <div class="block-content block-content-full p-0" id="catPanelsWrapper">

                @foreach($familles as $fi => $famille)
                <div class="cat-panel {{ $fi === 0 ? 'active' : '' }}" id="panel-{{ $famille->id }}"
                     data-label="{{ $famille->nom }}" data-count="{{ $famille->medicaments->count() }}">

                    <div class="cat-panel-header">
                        <div class="cat-panel-title">
                            <i class="fa fa-tag"></i>
                            {{ $famille->nom }}
                            <span class="cat-pill-count">{{ $famille->medicaments->count() }}</span>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover table-vcenter mb-0 w-100 cat-datatable"
                               id="table-{{ $famille->id }}">
                            <thead class="table-light">
                                <tr>
                                    <th>Médicament</th>
                                    <th>Unité</th>
                                    <th class="text-center">Stock théorique</th>
                                    <th class="text-center" style="min-width:130px;">Stock réel <span class="text-danger">*</span></th>
                                    <th class="text-center">Écart</th>
                                    <th style="min-width:170px;">Observations</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach($famille->medicaments as $med)
                            <tr data-med-id="{{ $med->id }}"
                                data-theorique="{{ $med->stock }}"
                                data-stock-reel="{{ old('stock_reel.' . $med->id, $med->stock) }}"
                                data-obs="{{ old('obs_ligne.' . $med->id, '') }}">
                                <td class="fw-semibold">{{ $med->nom }}</td>
                                <td class="text-muted small">{{ $med->unite?->nom ?? '-' }}</td>
                                <td class="text-center fw-semibold">{{ $med->stock }}</td>
                                <td class="text-center">
                                    <input type="number"
                                           class="form-control text-center stock-reel-input"
                                           min="0"
                                           value="{{ old('stock_reel.' . $med->id, $med->stock) }}"
                                           data-med-id="{{ $med->id }}"
                                           data-theorique="{{ $med->stock }}"
                                           required>
                                </td>
                                <td class="text-center ecart-cell">
                                    @php $e = old('stock_reel.' . $med->id, $med->stock) - $med->stock; @endphp
                                    <span class="badge {{ $e == 0 ? 'bg-success' : ($e > 0 ? 'bg-info' : 'bg-danger') }}">
                                        {{ $e >= 0 ? '+' : '' }}{{ $e }}
                                    </span>
                                </td>
                                <td>
                                    <input type="text"
                                           class="form-control obs-ligne-input"
                                           placeholder="Remarque..."
                                           data-med-id="{{ $med->id }}"
                                           value="{{ old('obs_ligne.' . $med->id) }}">
                                </td>
                            </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endforeach

                {{-- Sans famille --}}
                @if($sansFamille->count())
                <div class="cat-panel {{ $familles->isEmpty() ? 'active' : '' }}"
                     id="panel-sans-famille"
                     data-label="Sans catégorie" data-count="{{ $sansFamille->count() }}">

                    <div class="cat-panel-header">
                        <div class="cat-panel-title">
                            <i class="fa fa-question-circle"></i>
                            Sans catégorie
                            <span class="cat-pill-count">{{ $sansFamille->count() }}</span>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover table-vcenter mb-0 w-100 cat-datatable"
                               id="table-sans-famille">
                            <thead class="table-light">
                                <tr>
                                    <th>Médicament</th>
                                    <th>Unité</th>
                                    <th class="text-center">Stock théorique</th>
                                    <th class="text-center" style="min-width:130px;">Stock réel <span class="text-danger">*</span></th>
                                    <th class="text-center">Écart</th>
                                    <th style="min-width:170px;">Observations</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach($sansFamille as $med)
                            <tr data-med-id="{{ $med->id }}"
                                data-theorique="{{ $med->stock }}"
                                data-stock-reel="{{ old('stock_reel.' . $med->id, $med->stock) }}"
                                data-obs="{{ old('obs_ligne.' . $med->id, '') }}">
                                <td class="fw-semibold">{{ $med->nom }}</td>
                                <td class="text-muted small">{{ $med->unite?->nom ?? '-' }}</td>
                                <td class="text-center fw-semibold">{{ $med->stock }}</td>
                                <td class="text-center">
                                    <input type="number"
                                           class="form-control text-center stock-reel-input"
                                           min="0"
                                           value="{{ old('stock_reel.' . $med->id, $med->stock) }}"
                                           data-med-id="{{ $med->id }}"
                                           data-theorique="{{ $med->stock }}"
                                           required>
                                </td>
                                <td class="text-center ecart-cell">
                                    @php $e = old('stock_reel.' . $med->id, $med->stock) - $med->stock; @endphp
                                    <span class="badge {{ $e == 0 ? 'bg-success' : ($e > 0 ? 'bg-info' : 'bg-danger') }}">
                                        {{ $e >= 0 ? '+' : '' }}{{ $e }}
                                    </span>
                                </td>
                                <td>
                                    <input type="text"
                                           class="form-control obs-ligne-input"
                                           placeholder="Remarque..."
                                           data-med-id="{{ $med->id }}"
                                           value="{{ old('obs_ligne.' . $med->id) }}">
                                </td>
                            </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

            </div>{{-- /catPanelsWrapper --}}
        </div>{{-- /block --}}

        {{-- Résumé sticky --}}
        <div class="summary-sticky">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex gap-4">
                    <div class="summary-stat">
                        <div class="num text-success" id="summaryConformes">0</div>
                        <div class="lbl">Conformes</div>
                    </div>
                    <div class="summary-stat">
                        <div class="num text-info" id="summaryExcedents">0</div>
                        <div class="lbl">Excédents</div>
                    </div>
                    <div class="summary-stat">
                        <div class="num text-danger" id="summaryManquants">0</div>
                        <div class="lbl">Manquants</div>
                    </div>
                    <div class="summary-stat">
                        <div class="num text-primary" id="summaryTotal">{{ $totalMeds }}</div>
                        <div class="lbl">Total</div>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('inventaires.index') }}" class="btn btn-secondary">
                        <i class="fa fa-times me-1"></i> Annuler
                    </a>
                    <button type="submit" class="btn btn-primary" id="btnSave">
                        <i class="fa fa-save me-1"></i> Enregistrer en brouillon
                    </button>
                </div>
            </div>
        </div>

        <div id="hiddenFields"></div>
    </form>
</div>
</main>
@endsection

@section('scripts')
<script>
$(document).ready(function () {

    var stockData   = {};
    var obsData     = {};
    var dtInstances = {};
    var totalMeds   = {{ $totalMeds }};

    /* ── Init DataTables ─────────────────────────────────────────── */
    $('.cat-datatable').each(function () {
        var panelId = $(this).closest('.cat-panel').attr('id');

        var dt = $(this).DataTable({
            pageLength: 25,
            lengthMenu: [10, 25, 50, 100],
            ordering  : true,
            order     : [[0, 'asc']],
            columnDefs: [{ orderable: false, searchable: false, targets: [3, 4, 5] }],
            language  : { url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/fr-FR.json' },
            drawCallback: function () {
                restoreValues(this.api());
                updateSummary();
            }
        });

        dtInstances[panelId] = dt;

        dt.rows().nodes().each(function (row) {
            var $row  = $(row);
            var medId = $row.data('med-id');
            stockData[medId] = $row.data('stock-reel');
            obsData[medId]   = $row.data('obs') || '';
        });
    });

    /* ── Restaurer les valeurs après redessinage ─────────────────── */
    function restoreValues(dt) {
        dt.rows().nodes().each(function (row) {
            var $row  = $(row);
            var medId = $row.data('med-id');
            if (stockData[medId] !== undefined) $row.find('.stock-reel-input').val(stockData[medId]);
            if (obsData[medId]   !== undefined) $row.find('.obs-ligne-input').val(obsData[medId]);
            updateEcartRow($row);
        });
    }

    /* ── Écart visuel ────────────────────────────────────────────── */
    function updateEcartRow($row) {
        var $input    = $row.find('.stock-reel-input');
        var theorique = parseInt($input.data('theorique')) || 0;
        var reel      = parseInt($input.val()) || 0;
        var ecart     = reel - theorique;
        $row.find('.ecart-cell .badge')
            .removeClass('bg-success bg-info bg-danger')
            .addClass(ecart === 0 ? 'bg-success' : (ecart > 0 ? 'bg-info' : 'bg-danger'))
            .text((ecart >= 0 ? '+' : '') + ecart);
    }

    /* ── Résumé + progression ────────────────────────────────────── */
    function updateSummary() {
        var conformes = 0, excedents = 0, manquants = 0, modifies = 0;

        $.each(dtInstances, function (_, dt) {
            dt.rows().nodes().each(function (row) {
                var $row      = $(row);
                var medId     = $row.data('med-id');
                var theorique = parseInt($row.data('theorique')) || 0;
                var reel      = parseInt(stockData[medId] !== undefined ? stockData[medId] : theorique);
                var ecart     = reel - theorique;

                if (ecart === 0)     conformes++;
                else if (ecart > 0)  excedents++;
                else                 manquants++;

                if (String(stockData[medId]) !== String(theorique)) modifies++;
            });
        });

        $('#summaryConformes').text(conformes);
        $('#summaryExcedents').text(excedents);
        $('#summaryManquants').text(manquants);

        var pct = totalMeds > 0 ? Math.round((modifies / totalMeds) * 100) : 0;
        $('#progressCount').text(modifies);
        $('#progressPct').text(pct + ' %');
        $('#progressBar').css('width', pct + '%');
    }

    /* ── Afficher un panneau ─────────────────────────────────────── */
    function showPanel(panelId) {
        $('.cat-panel').removeClass('active');
        $('#' + panelId).addClass('active');
        // Sync le dropdown sur le panneau affiché
        $('#catDropdown').val(panelId);
        if (dtInstances[panelId]) {
            dtInstances[panelId].columns.adjust().draw(false);
        }
    }

    $('#catDropdown').on('change', function () {
        // Effacer la recherche si on change manuellement de catégorie
        if ($('#globalSearch').val() !== '') {
            clearGlobalSearch();
        }
        showPanel($(this).val());
    });

    /* ── Recherche globale ───────────────────────────────────────── */
    var searchTimer = null;

    function clearGlobalSearch() {
        $('#globalSearch').val('');
        $('#clearSearch').hide();
        $('#searchResultBadge').hide();
        // Réinitialiser tous les filtres DT
        $.each(dtInstances, function (_, dt) { dt.search('').draw(false); });
        // Réafficher le panneau courant du dropdown
        showPanel($('#catDropdown').val());
    }

    $('#globalSearch').on('input', function () {
        clearTimeout(searchTimer);
        var term = $.trim($(this).val());

        if (term === '') {
            clearGlobalSearch();
            return;
        }

        $('#clearSearch').show();

        // Délai 250 ms pour éviter de filtrer à chaque frappe
        searchTimer = setTimeout(function () {
            var totalFound  = 0;
            var firstPanel  = null;

            $.each(dtInstances, function (panelId, dt) {
                dt.search(term).draw(false);
                var count = dt.rows({ search: 'applied' }).count();
                totalFound += count;
                if (count > 0 && firstPanel === null) {
                    firstPanel = panelId;
                }
            });

            // Basculer vers la première catégorie avec des résultats
            if (firstPanel) {
                $('.cat-panel').removeClass('active');
                $('#' + firstPanel).addClass('active');
                $('#catDropdown').val(firstPanel);
                if (dtInstances[firstPanel]) dtInstances[firstPanel].columns.adjust();
            }

            // Badge résultat
            var $badge = $('#searchResultBadge');
            if (totalFound > 0) {
                $badge.text(totalFound + ' résultat' + (totalFound > 1 ? 's' : '')).show()
                      .removeClass('bg-danger').addClass('bg-primary');
            } else {
                $badge.text('Aucun résultat').show()
                      .removeClass('bg-primary').addClass('bg-danger');
            }
        }, 250);
    });

    $('#clearSearch').on('click', function () {
        clearGlobalSearch();
    });

    /* ── Saisie ──────────────────────────────────────────────────── */
    $(document).on('input', '.stock-reel-input', function () {
        stockData[$(this).data('med-id')] = $(this).val();
        updateEcartRow($(this).closest('tr'));
        updateSummary();
    });

    $(document).on('input', '.obs-ligne-input', function () {
        obsData[$(this).data('med-id')] = $(this).val();
    });

    /* ── Copier tout ─────────────────────────────────────────────── */
    $('#btnCopierStock').on('click', function () {
        $.each(dtInstances, function (_, dt) {
            dt.rows().nodes().each(function (row) {
                var $row  = $(row);
                var medId = $row.data('med-id');
                var th    = $row.data('theorique');
                stockData[medId] = th;
                $row.find('.stock-reel-input').val(th);
                updateEcartRow($row);
            });
        });
        updateSummary();
    });

    /* ── Copier catégorie active ─────────────────────────────────── */
    $('#btnCopierCat').on('click', function () {
        var panelId = $('#catDropdown').val();
        var dt = dtInstances[panelId];
        if (!dt) return;
        dt.rows().nodes().each(function (row) {
            var $row  = $(row);
            var medId = $row.data('med-id');
            var th    = $row.data('theorique');
            stockData[medId] = th;
            $row.find('.stock-reel-input').val(th);
            updateEcartRow($row);
        });
        updateSummary();
    });

    /* ── Submit ──────────────────────────────────────────────────── */
    $('#inventaireForm').on('submit', function () {
        var $hidden = $('#hiddenFields').empty();
        $.each(stockData, function (medId, val) {
            $('<input>').attr({ type:'hidden', name:'stock_reel['+medId+']', value: val !== '' && val !== null ? val : 0 }).appendTo($hidden);
        });
        $.each(obsData, function (medId, obs) {
            if (obs && obs !== '') {
                $('<input>').attr({ type:'hidden', name:'obs_ligne['+medId+']', value: obs }).appendTo($hidden);
            }
        });
        return true;
    });

    /* ── Init ────────────────────────────────────────────────────── */
    updateSummary();
});
</script>
@endsection
