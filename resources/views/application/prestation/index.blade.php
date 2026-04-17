@extends('layouts.app')

@section('titre', '⚙️ Configuration - Prestations Médicales')

@section('content')
    <div class="container mt-4">
        <div class="row">
            <!-- Action Button Area -->
            <div class="d-flex justify-content-end mb-3">
                <a href="{{ route('prestations.create') }}" class="btn btn-sm btn-primary">
                    <i class="fa fa-plus me-1"></i> Ajouter
                </a>
            </div>

            <!-- Sidebar gauche -->
            @include('layouts.partials.configside')

            <!-- Contenu principal -->
            <div class="col-xl-9 col-lg-8">
                <div class="block block-rounded">
                    <div class="block-header block-header-default">
                        <h3 class="block-title">Liste des prestations</h3>
                    </div>
                    <div class="block-content block-content-full">
                        <div class="table-responsive">
                            <table id="prestations-table" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Prestation</th>
                                        <th>Service Médical</th>
                                        <th class="text-center col-md">Description</th>
                                        <th class="text-center">Prix</th>
                                        <th class="text-center col-md">Actions</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
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
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json',
                    paginate: {
                        previous: '<i class="fa fa-chevron-left"></i>',
                        next: '<i class="fa fa-chevron-right"></i>'
                    }
                },
                dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                     "<'row'<'col-sm-12'tr>>" +
                     "<'row'<'col-sm-12'i><'col-sm-12'p>>",
                pagingType: 'simple_numbers'
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
