@extends('layouts.app')

@section('titre', '⚙️ Configuration - Prestations Médicales')

@section('content')
    <div class="container mt-4">
        <div class="row">

            <!-- Sidebar gauche -->
            @include('layouts.partials.configside')

            <!-- Contenu principal -->
            <div class="col-xl-9 col-lg-8">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fa fa-check-circle me-1"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                <div class="block block-rounded">
                    <div class="block-header block-header-default d-flex justify-content-between align-items-center">
                        <h3 class="block-title">Liste des prestations</h3>
                        <div class="d-flex gap-2 align-items-center">
                            <a href="{{ route('export.model', 'prestations') }}" class="btn btn-sm btn-outline-primary" title="Exporter en Excel">
                                <i class="fa fa-file-excel me-1"></i> Exporter
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-primary btn-open-import-modal" data-module="prestations" data-label="Prestations">
                                <i class="fa fa-file-import me-1"></i> Importer
                            </button>
                            <a href="{{ route('prestations.create') }}" class="btn btn-sm btn-primary">
                                <i class="fa fa-plus me-1"></i> Ajouter
                            </a>
                        </div>
                    </div>
                    <div class="block-content block-content-full">
                        <div class="table-responsive">
                            <table id="prestations-table" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Prestation</th>
                                        <th>Service Médical</th>
                                        <th class="text-center col-md">Description</th>
                                        <th class="text-center">Quantifiable</th>
                                        <th class="text-center">Prix</th>
                                        <th class="text-center col-md">Actions</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @include('layouts.partials.import_modal')
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
                    { 
                        data: 'quantifiable', 
                        name: 'quantifiable',
                        className: 'text-center',
                        render: function(data) {
                            if (data == 1 || data === true) {
                                return '<span class="badge bg-success">Oui</span>';
                            }
                            return '<span class="badge bg-secondary">Non</span>';
                        }
                    },
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
