@extends('layouts.app')
@section('titre', 'Liste des Fournisseurs')
@section('content')
    <div class="container mt-4">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">📋 Liste des Fournisseurs</h5>
                <a href="{{ route('fournisseurs.create') }}" class="btn btn-light btn-sm">➕ Ajouter Fournisseur</a>
            </div>
            <div class="card-body">
                <table class="table table-striped table-hover table-bordered" id="fournisseurs-table">
                    <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Contact</th>
                        <th>Adresse</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(function() {
            $('#fournisseurs-table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true, // pour un affichage mobile-friendly
                autoWidth: false,
                lengthMenu: [ [10, 25, 50, -1], [10, 25, 50, "Tous"] ],
                language: {
                    // url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json'
                },
                ajax: '{{ route('fournisseurs.index') }}',
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'nom', name: 'nom' },
                    { data: 'contact', name: 'contact' },
                    { data: 'adresse', name: 'adresse' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ]
            });
        });
    </script>
@endsection
