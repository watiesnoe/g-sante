@extends('layouts.app')

@section('titre', 'Sécurité Sociale (Assurances)')

@section('content')
    <div class="container mt-4">
        <div class="row">
            <!-- Action Button Area -->
            <div class="d-flex justify-content-end mb-3">
                <a href="{{ route('assurances.create') }}" class="btn btn-sm btn-primary shadow-sm rounded-pill">
                    <i class="fa fa-plus me-1"></i> Ajouter une assurance
                </a>
            </div>

            <!-- Sidebar gauche -->
            @include('layouts.partials.configside')

            <!-- Contenu principal -->
            <div class="col-xl-9 col-lg-8">
                <div class="block block-rounded">
                    <div class="block-header block-header-default">
                        <h3 class="block-title">Liste des Assurances</h3>
                    </div>
                    <div class="block-content block-content-full">
                        <div class="table-responsive">
                            <table id="assuranceTable" class="table table-bordered table-striped table-vcenter">
                                <thead>
                                    <tr>
                                        <th>Nom</th>
                                        <th>Contact</th>
                                        <th>Adresse</th>
                                        <th>Taux (Prise en charge)</th>
                                        <th width="150" class="text-center">Actions</th>
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
                const table = $('#assuranceTable').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: "{{ route('assurances.index') }}",
                    columns: [
                        { data: 'nom', name: 'nom' },
                        { data: 'telephone', name: 'telephone' },
                        { data: 'adresse', name: 'adresse' },
                        { 
                            data: 'taux', 
                            name: 'taux',
                            render: function(data) {
                                return '<span class="badge bg-success">' + data + '%</span>';
                            }
                        },
                        { 
                            data: 'actions', 
                            name: 'actions', 
                            orderable: false, 
                            searchable: false,
                            className: 'text-center'
                        }
                    ],
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

                // Handle Delete via Ajax for better UX
                $('#assuranceTable').on('click', '.delete', function() {
                    const id = $(this).data('id');
                    Swal.fire({
                        title: 'Êtes-vous sûr ?',
                        text: "Cette action est irréversible !",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        confirmButtonText: 'Oui, supprimer !',
                        cancelButtonText: 'Annuler'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: '/assurances/' + id,
                                type: 'DELETE',
                                data: { _token: '{{ csrf_token() }}' },
                                success: function(res) {
                                    Swal.fire('Supprimé !', res.message, 'success');
                                    table.ajax.reload();
                                }
                            });
                        }
                    });
                });
            });
        </script>
    @endsection

