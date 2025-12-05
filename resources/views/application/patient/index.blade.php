@extends('layouts.app')

@section('titre')
    ⚙️ Configuration - Prestations Médicales
@endsection

@section('content')
    <div class="content">
        <div class="row">
            <!-- Sidebar gauche -->
            @include('layouts.partials.configside')

            <!-- Contenu principal -->
            <div class="col-xl-9 col-lg-8">
                <div class="block block-rounded">
                    <div class="block-header block-header-default d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 text-primary fw-bold">📰 Liste des prestations</h5>
                        <a href="{{ route('prestations.create') }}" class="btn btn-success btn-sm rounded-pill shadow-sm">
                            + Ajouter une prestation
                        </a>
                    </div>

                    <div class="block-content">
                        <table id="prestations-table" class="table table-bordered table-striped">
                            <thead>
                            <tr>
                                <th>Prestation</th>
                                <th>Service Médical</th>
                                <th>Description</th>
                                <th>Prix</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        $(document).ready(function() {
            $('#prestations-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('prestations.index') }}", // doit appeler la même route, controller gère $request->ajax()
                columns: [
                    { data: 'nom', name: 'nom' },
                    { data: 'service_medical', name: 'service_medical' },
                    { data: 'description', name: 'description' },
                    { data: 'prix', name: 'prix' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ],
                order: [[1, 'asc']],
            });

            // Supprimer avec confirmation
            $('#prestations-table').on('click', '.delete-btn', function(){
                var url = $(this).data('url');
                if(confirm('Voulez-vous vraiment supprimer cette prestation ?')){
                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: {
                            _method: 'DELETE',
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response){
                            $('#prestations-table').DataTable().ajax.reload();
                            alert('Prestation supprimée avec succès.');
                        },
                        error: function(xhr){
                            alert('Erreur lors de la suppression.');
                        }
                    });
                }
            });
        });
    </script>
@endsection
