@extends('layouts.app')

@section('title', 'Inventaires Médicaments')

@section('content')
<main id="main-container">
    <div class="content">

        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="h3 fw-bold mb-1"><i class="fa fa-clipboard-list text-primary me-2"></i>Inventaires des Médicaments</h2>
                <p class="text-muted mb-0">Contrôle et gestion du stock physique de la pharmacie</p>
            </div>
            @can('stock.inventaire')
            <a href="{{ route('inventaires.create') }}" class="btn btn-primary">
                <i class="fa fa-plus me-1"></i> Nouvel Inventaire
            </a>
            @endcan
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fa fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="block block-rounded">
            <div class="block-content block-content-full">
                <table id="inventairesTable" class="table table-bordered table-striped table-vcenter w-100">
                    <thead class="table-light">
                        <tr>
                            <th>Référence</th>
                            <th>Date</th>
                            <th>Responsable</th>
                            <th class="text-center">Médicaments</th>
                            <th class="text-center">Conformité</th>
                            <th class="text-center">Statut</th>
                            <th class="text-center" style="width:130px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</main>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    var table = $('#inventairesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('inventaires.index') }}",
        columns: [
            { data: 'reference', name: 'reference' },
            { data: 'date_inventaire', name: 'date_inventaire' },
            { data: 'responsable', name: 'user.name' },
            { data: 'nb_medicaments', name: 'nb_medicaments', searchable: false, className: 'text-center' },
            { data: 'taux_conformite', name: 'taux_conformite', searchable: false, className: 'text-center' },
            { data: 'statut', name: 'statut', className: 'text-center' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-center' },
        ],
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/fr-FR.json' },
        order: [[1, 'desc']],
    });

    // Valider un inventaire
    $(document).on('click', '.btn-valider', function() {
        var uuid = $(this).data('id');
        Swal.fire({
            title: 'Valider cet inventaire ?',
            text: 'Les stocks seront mis à jour selon les quantités comptées. Cette action est irréversible.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Oui, valider',
            cancelButtonText: 'Annuler',
        }).then(function(result) {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/inventaires/' + uuid + '/valider',
                    type: 'POST',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(r) {
                        if (r.success) {
                            Swal.fire('Validé !', r.message, 'success');
                            table.ajax.reload();
                        } else {
                            Swal.fire('Erreur', r.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Erreur', xhr.responseJSON?.message || 'Erreur inattendue', 'error');
                    }
                });
            }
        });
    });

    // Supprimer
    $(document).on('click', '.btn-delete', function() {
        var url = $(this).data('url');
        Swal.fire({
            title: 'Supprimer cet inventaire ?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Supprimer',
            cancelButtonText: 'Annuler',
        }).then(function(result) {
            if (result.isConfirmed) {
                $.ajax({
                    url: url,
                    type: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(r) {
                        if (r.success) {
                            Swal.fire('Supprimé !', r.message, 'success');
                            table.ajax.reload();
                        }
                    }
                });
            }
        });
    });
});
</script>
@endsection
