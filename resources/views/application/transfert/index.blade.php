@extends('layouts.app')

@section('titre', 'Suivi des Transferts')

@section('content')
    <div class="container mt-4">
        <div class="block block-rounded">
            <div class="block-header block-header-default">
                <h3 class="block-title">
                    <i class="fa fa-exchange-alt me-1"></i> Historique des Transferts Patients
                </h3>
            </div>
            <div class="block-content block-content-full">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-vcenter w-100" id="transferts-table">
                        <thead>
                            <tr>
                                <th>Patient</th>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Source</th>
                                <th>Destination</th>
                                <th>Motif</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            const table = $('#transferts-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('transferts.index') }}",
                columns: [
                    { data: 'patient', name: 'patient' },
                    { data: 'date', name: 'date_transfert' },
                    { data: 'type_label', name: 'type', className: 'text-center' },
                    { data: 'source', name: 'source' },
                    { data: 'destination', name: 'destination' },
                    { data: 'motif', name: 'motif' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-center' },
                ],
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json',
                    paginate: {
                        previous: '<i class="fa fa-chevron-left"></i>',
                        next: '<i class="fa fa-chevron-right"></i>'
                    }
                },
                order: [[1, 'desc']],
                dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                     "<'row'<'col-sm-12'tr>>" +
                     "<'row'<'col-sm-12 text-center'i><'col-sm-12 text-center'p>>",
                pagingType: 'simple_numbers'
            });

            window.deleteTransfert = function(id) {
                Swal.fire({
                    title: 'Êtes-vous sûr ?',
                    text: "Cette action est irréversible !",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Oui, supprimer !',
                    cancelButtonText: 'Annuler'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ url('transferts') }}/" + id,
                            type: 'DELETE',
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                if (response.success) {
                                    Dashmix.helpers('jq-notify', {type: 'success', icon: 'fa fa-check me-1', message: response.message});
                                    table.ajax.reload();
                                } else {
                                    Dashmix.helpers('jq-notify', {type: 'danger', icon: 'fa fa-times me-1', message: response.message});
                                }
                            },
                            error: function(xhr) {
                                Dashmix.helpers('jq-notify', {type: 'danger', icon: 'fa fa-times me-1', message: 'Erreur lors de la suppression.'});
                            }
                        });
                    }
                });
            };
        });
    </script>
@endsection
