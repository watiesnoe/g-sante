@extends('layouts.app')
@section('titre', 'Commandes')

@section('content')
    <div class="container mt-4">

        <div class="card shadow-lg border-0">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h4 class="mb-0">📦 Liste des Commandes</h4>
                <a href="{{ route('commandes.create') }}" class="btn btn-light btn-sm">
                    ➕ Nouvelle Commande
                </a>
            </div>

            <div class="card-body">
                <table id="tableCommandes" class="table table-bordered table-striped align-middle mb-0">
                    <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Fournisseur</th>
                        <th>Date Commande</th>
                        <th>Statut</th>
                        <th>Total</th>
                        <th class="text-center">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>

            <div class="card-footer text-muted text-end">
                <small>Dernière mise à jour : {{ now()->format('d/m/Y H:i') }}</small>
            </div>
        </div>

    </div>
@endsection


@section('scripts')

    <script>
        $(document).ready(function () {
            let table = $('#tableCommandes').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route("commandes.index") }}',
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'fournisseur', name: 'fournisseur.nom' },
                    { data: 'date_commande', name: 'date_commande' },
                    { data: 'statut', name: 'statut' },
                    { data: 'total', name: 'total' },
                    { data: 'actions', orderable: false, searchable: false }
                ],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json'
                },
                pageLength: 10,
                order: [[0, 'desc']]
            });

            // 🔴 Suppression AJAX avec confirmation
            $(document).on('click', '.btnSupprimer', function () {
                if (confirm('Voulez-vous vraiment supprimer cette commande ?')) {
                    let id = $(this).data('id');
                    $.ajax({
                        url: `/commandes/${id}`,
                        type: 'DELETE',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function () {
                            table.ajax.reload(null, false);
                        },
                        error: function() {
                            alert('Erreur lors de la suppression.');
                        }
                    });
                }
            });
        });
    </script>
@endsection
