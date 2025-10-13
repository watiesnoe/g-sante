@extends('layouts.app')

@section('titre','Médicaments')



@section('content')
    <div class="container mt-4">
        <div class="card shadow-lg">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Liste des Médicaments</h5>
                <a href="{{ route('medicaments.create') }}" class="btn btn-light btn-sm">➕ Ajouter Médicament</a>
            </div>
            <div class="card-body">
                <table class="table table-bordered" id="medicamentsTable">
                    <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Unité</th>
                        <th>Famille</th>
                        <th>Stock</th>
                        <th>Stock Min</th>
                        <th>Prix Achat</th>
                        <th>Prix Vente</th>
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
            $('#medicamentsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route("medicaments.index") }}',
                columns: [
                    { data: 'nom', name: 'nom' },

                    { data: 'unite', name: 'unite' },
                    { data: 'famille', name: 'famille' },
                    { data: 'stock', name: 'stock' },
                    { data: 'stock_min', name: 'stock_min' },
                    { data: 'prix_achat', name: 'prix_achat' },
                    { data: 'prix_vente', name: 'prix_vente' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false },
                ],
                rowCallback: function(row, data) {
                    if (data.stock <= data.stock_min) {
                        $(row).addClass('table-danger');
                    }
                }
            });

            // Confirmation avant suppression
            $('body').on('submit', 'form.d-inline', function(e) {
                if(!confirm('Voulez-vous vraiment supprimer ce médicament ?')) e.preventDefault();
            });
        });
    </script>
@endsection
