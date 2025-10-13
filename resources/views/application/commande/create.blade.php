@extends('layouts.app')

@section('title_page', isset($commande) ? 'Modifier Commande' : 'Commande de Médicaments')

@section('page_link')
    <a href="{{ route('commandes.index') }}">Commande</a>
@endsection

@section('page_name', isset($commande) ? 'Modifier Commande' : 'Nouvelle Commande')

@section('content')
<div class="container-fluid">
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-3 mt-3" role="alert">
            <i class="fa fa-exclamation-circle me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-3 mt-3" role="alert">
            <i class="fa fa-exclamation-triangle me-2"></i> Veuillez corriger les erreurs ci-dessous :
            <ul class="mb-0 mt-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form method="POST" action="{{ isset($commande) ? route('commandes.update', $commande) : route('commandes.store') }}">
        @csrf
        @if(isset($commande)) @method('PUT') @endif

        {{-- INFOS COMMANDE --}}
        <div class="block block-rounded">
            <div class="block-header bg-primary text-white">
                <h4 class="block-title">
                    {{ isset($commande) ? 'Commande #'.$commande->reference : 'Nouvelle Commande' }}
                </h4>
            </div>

            <div class="block-content">
                <div class="row mb-2">
                    <div class="col-md-4">
                        <label>Référence</label>
                        <input type="text" name="reference"
                               value="{{ isset($commande) ? $commande->reference : 'CMD-' . str_pad(App\Models\Commande::count()+1,4,'0',STR_PAD_LEFT) }}"
                               class="form-control" readonly>
                    </div>

                    <div class="col-md-4">
                        <label>Date</label>
                        <input type="date" name="date_commande"
                               value="{{ isset($commande) ? $commande->date_commande->format('Y-m-d') : date('Y-m-d') }}"
                               class="form-control">
                    </div>

                    <div class="col-md-4">
                        <label>Fournisseur</label>
                        <select name="fournisseur_id" class="form-control js-select2" required>
                            <option value="">-- Choisir --</option>
                            @foreach($fournisseurs as $f)
                                <option value="{{ $f->id }}" {{ isset($commande) && $f->id == $commande->fournisseur_id ? 'selected' : '' }}>
                                    {{ $f->nom }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- MEDICAMENTS --}}
        <div class="block block-rounded mt-3">
            <div class="block-content">
                <div class="mb-3">
                    <label for="selectMedicament">Médicament</label>
                    <select id="selectMedicament" class="form-control js-select2 mb-3 ">
                        <option value="">-- Choisir un médicament --</option>
                        @foreach($medicaments as $m)
                            <option value="{{ $m->id }}" data-prix="{{ $m->prix_achat }}">
                                {{ $m->nom }} (Stock: {{ $m->stock }} | {{ number_format($m->prix_achat, 0, ',', ' ') }} FCFA)
                            </option>
                        @endforeach
                </select>
                </div>
                <table class="table table-bordered" id="table-panier">
                    <thead>
                        <tr>
                            <th>Médicament</th>
                            <th width="20%">Unité</th>
                            <th width="15%">Qté</th>
                            <th width="18%">P.U (FCFA)</th>
                            <th width="18%">Total (FCFA)</th>
                            <th width="50"></th>
                        </tr>
                    </thead>

                    <tbody></tbody>

                    <tfoot>
                        <tr>
                            <th colspan="4" class="text-end">Total :</th>
                            <th id="total_general">0</th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
                <div class="d-flex justify-content-between p-3">
                    <a href="{{ route('commandes.index') }}" class="btn btn-secondary"> Annuler</a>
                    <button type="submit" class="btn btn-primary">
                        {{ isset($commande) ? 'Mettre à jour' : 'Enregistrer' }}
                    </button>
                </div>
     
            </div>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
$(function(){

    // Échappe le HTML pour éviter toute injection via des noms de médicament/unité (XSS)
    function escapeHtml(str) {
        return String(str ?? '').replace(/[&<>"']/g, function (s) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[s];
        });
    }

    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    $('.js-select2').select2({ width:'100%' });

    let panier = {};

    // INIT PANIER
    @if(isset($commande))
        @foreach($commande->lignes as $l)
        panier[{{ $l->medicament_id }}] = {
            id: {{ $l->medicament_id }},
            nom: "{{ $l->medicament->nom }}",
            quantite: {{ $l->quantite }},
            unite_id: {{ $l->unite_id ?? 'null' }},
            prix_unitaire: {{ $l->prix_unitaire }},
            unites: {!! json_encode($l->medicament ? $l->medicament->unites->map(fn($u) => ['id'=>$u->id, 'nom'=>$u->nom, 'prix_achat'=>$u->prix_achat, 'is_default'=>$u->is_default]) : []) !!}
        };
        @endforeach
    @else
        panier = {!! json_encode(session('commande_panier', [])) !!};
    @endif

    renderTable();

    // AJOUT MEDICAMENT
    $('#selectMedicament').change(function(){
        let id = $(this).val();
        if(!id) return;

        $.post("{{ route('commandes.panier.ajouter') }}", { medicament_id: id }, function(data){
            panier = data;
            renderTable();
            $('#selectMedicament').val(null).trigger('change');
        });
    });

    // SUPPRIMER
    $('#table-panier').on('click','.remove',function(){
        let id = $(this).data('id');
        $.post("{{ route('commandes.panier.supprimer') }}", { medicament_id: id }, function(data){
            panier = data;
            renderTable();
        });
    });

    // CHANGEMENT D'UNITE
    $('#table-panier').on('change','.select-unite',function(){
        let tr = $(this).closest('tr');
        let id = tr.data('id');
        let selectedOption = $(this).find('option:selected');
        let prix = parseFloat(selectedOption.data('prix')) || 0;
        let uniteId = $(this).val();

        if (prix > 0) {
            tr.find('.prix').val(Math.round(prix));
        }

        let qte = parseInt(tr.find('.quantite').val()) || 1;
        let newPrix = parseFloat(tr.find('.prix').val()) || 0;

        $.post("{{ route('commandes.panier.modifier') }}", { 
            medicament_id: id, 
            quantite: qte, 
            prix_unitaire: newPrix,
            unite_id: uniteId
        }, function(data){
            panier = data;
            let total = qte * newPrix;
            tr.find('.total').text(Math.round(total).toLocaleString('fr-FR'));
            calculTotal();
        });
    });

    // MODIFIER (SANS RELOAD)
    $('#table-panier').on('input','.quantite, .prix',function(){
        let tr = $(this).closest('tr');
        let id = tr.data('id');
        let qte = parseInt(tr.find('.quantite').val()) || 1;
        let prix = parseFloat(tr.find('.prix').val()) || 0;
        let uniteId = tr.find('.select-unite').val();

        $.post("{{ route('commandes.panier.modifier') }}", { 
            medicament_id: id, 
            quantite: qte, 
            prix_unitaire: prix,
            unite_id: uniteId
        }, function(data){
            panier = data;
            // update ligne
            let total = qte * prix;
            tr.find('.total').text(Math.round(total).toLocaleString('fr-FR'));
            calculTotal();
        });
    });

    function renderTable(){
        let tbody = $('#table-panier tbody');
        tbody.empty();

        Object.values(panier).forEach(item=>{
            let pUnit = parseFloat(item.prix_unitaire) || 0;
            let unitesOptions = '';
            
            if (item.unites && Array.isArray(item.unites) && item.unites.length > 0) {
                item.unites.forEach(u => {
                    let selected = (item.unite_id == u.id || (!item.unite_id && u.is_default)) ? 'selected' : '';
                    unitesOptions += `<option value="${u.id}" data-prix="${u.prix_achat}" ${selected}>${escapeHtml(u.nom)}</option>`;
                });
            } else {
                unitesOptions = `<option value="">Standard</option>`;
            }

            tbody.append(`
                <tr data-id="${item.id}">
                    <td>
                        <input type="hidden" name="medicament_id[]" value="${item.id}">
                        <div class="fw-bold">${escapeHtml(item.nom)}</div>
                    </td>

                    <td>
                        <select name="unite_id[]" class="form-select form-select-sm select-unite">
                            ${unitesOptions}
                        </select>
                    </td>

                    <td>
                        <input type="number" name="quantite[]" class="form-control quantite"
                                value="${item.quantite}" min="1">
                    </td>

                    <td>
                        <input type="number" name="prix_unitaire[]" class="form-control prix"
                                value="${Math.round(pUnit)}" step="1">
                    </td>

                    <td class="total text-end fw-bold">${Math.round(item.quantite * pUnit).toLocaleString('fr-FR')}</td>

                    <td class="text-center">
                        <button type="button" class="btn btn-danger btn-sm remove" data-id="${item.id}">
                            <i class="fa fa-times"></i>
                        </button>
                    </td>
                </tr>
            `);
        });

        calculTotal();
    }

    function calculTotal(){
        let total = 0;
        Object.values(panier).forEach(i=>{
            total += i.quantite * (parseFloat(i.prix_unitaire) || 0);
        });

        $('#total_general').text(Math.round(total).toLocaleString('fr-FR') + ' FCFA');
    }

});
</script>
@endsection